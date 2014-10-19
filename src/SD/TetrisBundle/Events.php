<?php
/**
 * Copyright (c) Scott Driscoll
 */

namespace SD\TetrisBundle;

/**
 * @author Scott Driscoll <scott.driscoll@opensoftdev.com>
 */
final class Events
{
    const HEARTBEAT = 'sd.heartbeat';
    const REDRAW = 'sd.redraw';
    const KEYBOARD_LEFT = 'sd.keyboard.left';
    const KEYBOARD_RIGHT = 'sd.keyboard.right';
    const KEYBOARD_DOWN = 'sd.keyboard.down';
    const KEYBOARD_ROTATE = 'sd.keyboard.rotate';
    const GAME_OVER = 'sd.game.over';
    const BLOCK_REACHED_BOTTOM = 'sd.block_bottom';
    const BLOCK_MOVED = 'sd.block_moved';
    const NEXT_BLOCK_READY = 'sd.next_block_ready';
    const LINES_CLEARED = 'sd.lines_cleared';
    const STAGE_CLEARED = 'sd.stage_cleared';
    const MESSAGE_PLAYER_CONNECTED = 'sd.message.player_connected';
    const MESSAGE_BOARD_UPDATE = 'sd.message.board_update';
    const MESSAGE_PEER_LOSE = 'sd.message.peer_lose';
}
