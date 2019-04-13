<?php

namespace SudokuSolver;

class Cell extends Position
{
    /**
     * @var string
     */
    protected $number;

    public function __construct(int $x, int $y, string $number)
    {
        parent::__construct($x, $y);

        $this->number = $number;
    }

    /**
     * @return string
     */
    public function getNumber(): string
    {
        return $this->number;
    }

    /**
     * @return bool
     */
    public function isFilled(): bool
    {
        return $this->number !== '.';
    }

    /**
     * @return bool
     */
    public function isEmpty(): bool
    {
        return $this->number === '.';
    }
}
