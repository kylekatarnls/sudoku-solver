<?php

namespace SudokuSolver;

class Position
{
    /**
     * @var int|null
     */
    protected $x = null;

    /**
     * @var int|null
     */
    protected $y = null;

    /**
     * @var int[]
     */
    protected static $squares = [];

    public function __construct(int $x, int $y)
    {
        $this->x = $x;
        $this->y = $y;
    }

    /**
     * @return int
     */
    public function getSquare()
    {
        $index = $this->y * 9 + $this->x;

        if (!isset(self::$squares[$index])) {
            self::$squares[$index] = floor($this->y / 3) * 3 + floor($this->x / 3);
        }

        return self::$squares[$index];
    }

    /**
     * @return int
     */
    public function getX()
    {
        return $this->x;
    }

    /**
     * @return int
     */
    public function getY()
    {
        return $this->y;
    }
}
