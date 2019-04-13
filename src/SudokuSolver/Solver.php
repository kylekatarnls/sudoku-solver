<?php

namespace SudokuSolver;

use RuntimeException;

class Solver
{
    public function solveSudoku(&$grid)
    {
        $board = new Board($grid);
        $grid = $board->getGrid();

        if (!$board->solve()) {
            throw new RuntimeException('Unable to solve the sudoku grid.', 4);
        }

        $grid = $board->getGrid();
    }

    public function __invoke(&$board)
    {
        $this->solveSudoku($board);
    }
}
