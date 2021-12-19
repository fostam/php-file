<?php

namespace Fostam\File\Exception;

use Throwable;

class FlushErrorFileException extends FileException {
    public function __construct(string $filename, string $error = '', Throwable $previous = null) {
        parent::__construct($filename, "can't truncate file {$filename}", $error, $previous);
    }
}