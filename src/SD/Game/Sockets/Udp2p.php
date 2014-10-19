<?php
/**
 * Copyright (c) Scott Driscoll
 */

namespace SD\Game\Sockets;

use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use JMS\DiExtraBundle\Annotation as DI;
use Symfony\Component\Stopwatch\Stopwatch;
use SD\Game\Sockets\Message\AbstractMessage;
use SD\Game\Sockets\Message\AcknowledgeMessage;
use SD\Game\Sockets\Message\ConnectionMessage;
use SD\Game\Sockets\Message\CriticalMessage;
use SD\Game\Sockets\Message\GameOverMessage;
use SD\Game\Sockets\Message\BoardUpdateMessage;
use SD\TetrisBundle\Event\MultiplayerBoardUpdateEvent;
use SD\TetrisBundle\Events;
use SD\TetrisBundle\Event\HeartbeatEvent;
use SD\TetrisBundle\Event\GameOverEvent;
use SD\TetrisBundle\Event\PlayerConnectedEvent;

/**
 * Class to implement simple peer to peer communication over UDP
 *
 * @DI\Service("game.udp2p")
 *
 * @author Scott Driscoll <scott.driscoll@opensoftdev.com>
 */
class Udp2p
{
    const PORT = 11019;

    const MICROSECONDS_PER_SECOND = 1000000;

    /**
     * Critical message retry frequency in milliseconds
     *
     * If a critical message is not acknowledged, we must retry it
     */
    const MESSAGE_RESEND_FREQUENCY = 2000;

    /**
     * How long to keep critical messages we have received before we discard them.
     *
     * This is needed in case the other peer did not receive our ACK. We don't want to double process.
     */
    const CRITICAL_RECEIVE_AGE = 10000;

    /**
     * @var EventDispatcherInterface
     */
    private $eventDispatcher;

    /**
     * The "connected" socket, or null if communication has not been established
     *
     * @var resource
     */
    private $socket;

    /**
     * @var array
     */
    private $criticalSend = [];

    /**
     * @var array
     */
    private $criticalReceive = [];

    /**
     * @var string
     */
    private $ip;

    /**
     * @var string
     */
    private $name;

    /**
     * @DI\InjectParams({
     *     "eventDispatcher" = @DI\Inject("event_dispatcher")
     * })
     *
     * @param EventDispatcherInterface $eventDispatcher
     */
    public function __construct(EventDispatcherInterface $eventDispatcher)
    {
        $this->eventDispatcher = $eventDispatcher;
    }

    /**
     * Employ UDP hole punching to communicate with the other peer
     *
     * @param string $ip
     * @param int $timeout timeout in milliseconds
     * @param string $name
     *
     * @return bool
     */
    public function establishCommunication($ip, $timeout, $name)
    {
        $this->name = $name;
        $this->ip = $ip;
        $stopwatch = new Stopwatch();
        $receiveMessage = null;
        $message = new ConnectionMessage($name);

        $this->socket = socket_create(AF_INET, SOCK_DGRAM, SOL_UDP);
        socket_bind($this->socket, '0.0.0.0', self::PORT);

        $stopwatch->start('connect');

        while (null === $receiveMessage) {
            $this->sendMessage($message);
            $receiveMessage = $this->readMessage();
            if (null === $receiveMessage || !($receiveMessage instanceof ConnectionMessage)) {
                $timer = $stopwatch->lap('connect');
                if ($timer->getDuration() >= $timeout) {
                    $this->socket = null;

                    return false;
                }

                usleep(self::MICROSECONDS_PER_SECOND / 2);
            }
        }

        $this->fireEvent($receiveMessage);

        return true;
    }

    /**
     * @DI\Observe(Events::HEARTBEAT, priority = 10)
     *
     * @param HeartbeatEvent $event
     */
    public function readIncomingMessage(HeartbeatEvent $event)
    {
        if (null === $this->socket) {
            return;
        }

        $message = $this->readMessage();
        if (null === $message) {
            return;
        }

        if ($message->isCritical()) {
            $messageId = $message->getObjectId();
            $this->sendAck($messageId);
            $this->storeCriticalReceive($messageId);
        }

        $this->fireEvent($message);
    }

    /**
     * @DI\Observe(Events::HEARTBEAT, priority = 9)
     *
     * @param HeartbeatEvent $event
     */
    public function resendCriticalMessages(HeartbeatEvent $event)
    {
        /** @var CriticalMessage $message */
        foreach ($this->criticalSend as $messageId => $message) {
            if ($message->getElapsedTime() >= self::MESSAGE_RESEND_FREQUENCY) {
                $this->sendMessage($message->getMessage());
            }
        }
    }

    /**
     * @DI\Observe(Events::HEARTBEAT, priority = 8)
     *
     * @param HeartbeatEvent $event
     */
    public function removeDeadAcks(HeartbeatEvent $event)
    {
        /** @var CriticalMessage $message */
        foreach ($this->criticalReceive as $messageId => $message) {
            if ($message->getElapsedTime() >= self::CRITICAL_RECEIVE_AGE) {
                unset($this->criticalReceive[$messageId]);
            }
        }
    }

    /**
     * Format of the data being sent over the socket is always:
     *
     * 4 bytes = length of following message
     * <serialized object extending AbstractMessage>
     *
     * @param AbstractMessage $message
     */
    public function sendMessage(AbstractMessage $message)
    {
        if (null === $this->socket) {
            return;
        }

        $messageId = spl_object_hash($message);
        $message->setObjectId($messageId);
        $serializedMessage = serialize($message);

        if ($message->isCritical()) {
            $this->storeCriticalMessage($message, $messageId);
        }

        $messageLength = pack("L", strlen($serializedMessage));
        socket_sendto($this->socket, $messageLength, 4, 0, $this->ip, self::PORT);
        socket_sendto($this->socket, $serializedMessage, strlen($serializedMessage), 0, $this->ip, self::PORT);
    }

    /**
     * @return bool
     */
    public function isConnected()
    {
        return !empty($this->socket);
    }

    /**
     * @param AbstractMessage $message
     * @param string $messageId
     */
    private function storeCriticalMessage(AbstractMessage $message, $messageId)
    {
        if (!isset($this->criticalSend[$messageId])) {
            $this->criticalSend[$messageId] = new CriticalMessage($message);
        }
    }

    /**
     * @param string $messageId
     */
    private function storeCriticalReceive($messageId)
    {
        if (!isset($this->criticalSend[$messageId])) {
            $this->criticalReceive[$messageId] = new CriticalMessage();
        }
    }

    /**
     * @return AbstractMessage|null
     */
    private function readMessage()
    {
        $messageLength = 0;
        $serializedMessage = '';
        $port = self::PORT;
        if (@socket_recvfrom($this->socket, $messageLength, 4, MSG_DONTWAIT, $this->ip, $port) == 4) {
            $messageLength = unpack('L', $messageLength);
            $messageLength = $messageLength[1];

            if (@socket_recvfrom($this->socket, $serializedMessage, $messageLength, MSG_DONTWAIT, $this->ip, $port) == $messageLength) {
                $message = unserialize($serializedMessage);

                if (!empty($message) && is_object($message) && $message instanceof AbstractMessage) {
                    return $message;
                }
            }
        }

        return null;
    }

    /**
     * @param string $messageId
     */
    private function sendAck($messageId)
    {
        $this->sendMessage(new AcknowledgeMessage($messageId));
    }

    /**
     * @param AbstractMessage $message
     */
    private function fireEvent(AbstractMessage $message)
    {
        if ($message instanceof BoardUpdateMessage) {
            $this->eventDispatcher->dispatch(Events::MESSAGE_BOARD_UPDATE, new MultiplayerBoardUpdateEvent($message));
        } elseif ($message instanceof GameOverMessage) {
            $this->eventDispatcher->dispatch(Events::GAME_OVER, new GameOverEvent(true));
        } elseif ($message instanceof ConnectionMessage) {
            $this->eventDispatcher->dispatch(Events::MESSAGE_PLAYER_CONNECTED, new PlayerConnectedEvent($this->name, $message->getName()));
        }
    }
}
