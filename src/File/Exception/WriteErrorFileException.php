<?php

namespace Fostam\File\Exception;

use Throwable;

class WriteErrorFileException extends FileException {
    private int $pos;

    public function __construct(string $filename, int $pos, string $error = '', Throwable $previous = null) {
        $this->pos = $pos;
        parent::__construct($filename, "can't write to {$filename} on position {$pos}", $error, $previous);
    }

    public function getPos(): int {
        return $this->pos;
    }
}