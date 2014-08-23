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
    const KEYBOARD_LEFT = 'sd.keyboard.left';
    const KEYBOARD_RIGHT = 'sd.keyboard.right';
    const KEYBOARD_DOWN = 'sd.keyboard.down';
    const KEYBOARD_ROTATE = 'sd.keyboard.rotate';
    const BOARD_REDRAW = 'sd.board.redraw';
    const GAME_OVER = 'sd.game.over';
}
