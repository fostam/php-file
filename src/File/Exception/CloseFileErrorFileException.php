<?php

namespace Fostam\File\Exception;

use Throwable;

class CloseFileErrorFileException extends FileException {
    public function __construct(string $filename, string $error = '', Throwable $previous = null) {
        parent::__construct($filename, "can't close file {$filename}", $error, $previous);
    }
}