<?php

namespace Fostam\File\Exception;

use Throwable;

class OpenFileErrorFileException extends FileException {
    public function __construct(string $filename, string $error = '', Throwable $previous = null) {
        parent::__construct($filename, "can't open file {$filename}", $error, $previous);
    }
}