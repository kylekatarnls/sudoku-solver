<?php

namespace SudokuSolver;

use RuntimeException;

class Board
{
    /**
     * @var string[][]
     */
    protected $grid;

    /**
     * @var string[][]
     */
    protected $columns;

    /**
     * @var string[][]
     */
    protected $lines;

    /**
     * @var string[][]
     */
    protected $squares;

    /**
     * @var int
     */
    protected $missing;

    /**
     * @var array|null
     */
    protected $numbersToTry;

    /**
     * @var array|null
     */
    protected $positionsToTry;

    /**
     * @var bool[]
     */
    protected $excluded = [];

    /**
     * Board constructor.
     *
     * @param string[][] $grid
     */
    public function __construct(array $grid)
    {
        $this->grid = $grid;
        $this->missing = 81;
        $this->columns = array_pad([], 9, []);
        $this->lines = array_pad([], 9, []);
        $this->squares = array_pad([], 9, []);

        foreach ($this->getFilledCells() as $cell) {
            $x = $cell->getX();
            $y = $cell->getY();
            $square = $cell->getSquare();
            $number = $cell->getNumber();

            if (in_array($number, $this->columns[$x])) {
                throw new RuntimeException("Duplicate $number in the column $x.", 1);
            }

            if (in_array($number, $this->lines[$y])) {
                throw new RuntimeException("Duplicate $number in the line $y.", 2);
            }

            if (in_array($number, $this->squares)) {
                throw new RuntimeException("Duplicate $number in the square $square.", 3);
            }

            $this->missing--;

            $this->columns[$x][] = $number;
            $this->lines[$y][] = $number;
            $this->squares[$square][] = $number;
        }
    }

    /**
     * @return bool[]
     */
    public function getExcluded(): array
    {
        return $this->excluded;
    }

    /**
     * @param int    $x
     * @param int    $y
     * @param string $number
     */
    public function exclude(int $x, int $y, string $number)
    {
        $this->excluded["$x-$y-$number"] = true;
    }

    /**
     * @param int    $x
     * @param int    $y
     * @param string $number
     *
     * @return bool
     */
    public function isExcluded(int $x, int $y, string $number): bool
    {
        return $this->excluded["$x-$y-$number"] ?? false;
    }

    /**
     * @return string[][]
     */
    public function getGrid(): array
    {
        return $this->grid;
    }

    /**
     * @param self $board
     */
    public function replace(self $board): void
    {
        $this->grid = $board->getGrid();
        $this->columns = $board->getColumns();
        $this->lines = $board->getLines();
        $this->squares = $board->getSquares();
        $this->missing = $board->getMissing();
        $this->excluded = $board->getExcluded();
    }

    /**
     * @return string[][]
     */
    public function getColumns(): array
    {
        return $this->columns;
    }

    /**
     * @return string[][]
     */
    public function getLines(): array
    {
        return $this->lines;
    }

    /**
     * @return int
     */
    public function getMissing(): int
    {
        return $this->missing;
    }

    /**
     * @return string[][]
     */
    public function getSquares(): array
    {
        return $this->squares;
    }

    /**
     * @param int    $x
     * @param int    $y
     * @param string $number
     */
    public function setNumber(int $x, int $y, string $number)
    {
        if ($this->grid[$y][$x] === '.') {
            $this->columns[$x][] = $number;
            $this->lines[$y][] = $number;
            $this->squares[(new Position($x, $y))->getSquare()][] = $number;
            $this->grid[$y][$x] = $number;
            $this->missing--;
        }
    }

    /**
     * @param int    $x
     * @param int    $y
     * @param string $number
     *
     * @return bool
     */
    public function isPossibleValue(int $x, int $y, string $number)
    {
        return !$this->isExcluded($x, $y, $number) &&
            !in_array($number, $this->columns[$x]) &&
            !in_array($number, $this->lines[$y]) &&
            !in_array($number, $this->squares[(new Position($x, $y))->getSquare()]);
    }

    /**
     * @return Cell[]
     */
    public function getCells()
    {
        foreach ($this->grid as $y => $line) {
            foreach ($line as $x => $number) {
                yield new Cell($x, $y, $number);
            }
        }
    }

    /**
     * @return Cell[]
     */
    public function getEmptyCells()
    {
        foreach ($this->getCells() as $cell) {
            if ($cell->isEmpty()) {
                yield $cell;
            }
        }
    }

    /**
     * @return Cell[]
     */
    public function getFilledCells()
    {
        foreach ($this->getCells() as $cell) {
            if ($cell->isFilled()) {
                yield $cell;
            }
        }
    }

    /**
     * @return bool
     */
    function fillGrid()
    {
        $oldMissing = $this->missing;

        while ($this->missing) {
            $this->numbersToTry = null;
            $this->positionsToTry = null;

            foreach ($this->getEmptyCells() as $cell) {
                $possibleNumbers = [];

                for ($i = 1; $i <= 9; $i++) {
                    if ($this->isPossibleValue($cell->getX(), $cell->getY(), $i)) {
                        $possibleNumbers[] = $i;
                    }
                }

                switch (count($possibleNumbers)) {
                    case 1:
                        $this->setNumber($cell->getX(), $cell->getY(), $possibleNumbers[0]);

                        break;
                    case 2:
                        $this->numbersToTry = [$cell->getX(), $cell->getY(), $possibleNumbers];

                        break;
                }
            }

            for ($i = 1; $i <= 9; $i++) {
                for ($x = 0; $x < 9; $x++) {
                    $possiblePositions = [];

                    for ($y = 0; $y < 9; $y++) {
                        if ($this->isPossibleValue($x, $y, $i)) {
                            $possiblePositions[] = $y;
                        }
                    }

                    switch (count($possiblePositions)) {
                        case 1:
                            $y = $possiblePositions[0];
                            $this->setNumber($x, $y, $i);

                            break;
                        case 2:
                            $this->positionsToTry = [$i, [
                                [$x, $possiblePositions[0]],
                                [$x, $possiblePositions[1]],
                            ]];

                            break;
                    }
                }

                for ($y = 0; $y < 9; $y++) {
                    $possiblePositions = [];

                    for ($x = 0; $x < 9; $x++) {
                        if ($this->isPossibleValue($x, $y, $i)) {
                            $possiblePositions[] = $x;
                        }
                    }

                    switch (count($possiblePositions)) {
                        case 1:
                            $x = $possiblePositions[0];
                            $this->setNumber($x, $y, $i);

                            break;
                        case 2:
                            $this->positionsToTry = [$i, [
                                [$possiblePositions[0], $y],
                                [$possiblePositions[1], $y],
                            ]];

                            break;
                    }
                }

                for ($s = 0; $s < 9; $s++) {
                    $possiblePositions = [];

                    for ($d = 0; $d < 9; $d++) {
                        $x = $s * 3 % 9 + $d % 3;
                        $y = floor($s / 3) * 3 + floor($d / 3);

                        if ($this->isPossibleValue($x, $y, $i)) {
                            $possiblePositions[] = [$x, $y];
                        }
                    }

                    switch (count($possiblePositions)) {
                        case 1:
                            [$x, $y] = $possiblePositions[0];
                            $this->setNumber($x, $y, $i);

                            break;
                        case 2:
                            $this->positionsToTry = [$i, $possiblePositions];

                            break;
                    }
                }
            }

            if ($oldMissing === $this->missing) {
                return false;
            }

            $oldMissing = $this->missing;
        }

        return true;
    }

    /**
     * @param int $depth
     *
     * @return bool
     */
    public function solve(int $depth = 0): bool
    {
        if ($depth > 10) {
            return false;
        }

        if (!$this->fillGrid()) {
            if ($this->numbersToTry) {
                [$x, $y, $possibleNumbers] = $this->numbersToTry;

                foreach ($possibleNumbers as $index => $number) {
                    $boardSandBox = clone $this;
                    $boardSandBox->exclude($x, $y, $possibleNumbers[1 - $index]);

                    if ($boardSandBox->solve($depth + 1)) {
                        $this->replace($boardSandBox);

                        return true;
                    }
                }
            } elseif ($this->positionsToTry) {
                [$number, $possiblePositions] = $this->positionsToTry;

                foreach ($possiblePositions as $index => [$x, $y]) {
                    $boardSandBox = clone $this;
                    $boardSandBox->exclude($possiblePositions[1 - $index][0], $possiblePositions[1 - $index][1], $number);

                    if ($boardSandBox->solve($depth + 1)) {
                        $this->replace($boardSandBox);

                        return true;
                    }
                }
            }

            return false;
        }

        return true;
    }
}
