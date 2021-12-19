<?php

namespace Fostam\File\Exception;

use Throwable;

class SetPositionErrorFileException extends FileException {
    private int $pos;

    public function __construct(string $filename, int $pos, string $error = '', Throwable $previous = null) {
        $this->pos = $pos;
        parent::__construct($filename, "seek to position {$pos} in {$filename} failed", $error, $previous);
    }

    public function getPos(): int {
        return $this->pos;
    }
}