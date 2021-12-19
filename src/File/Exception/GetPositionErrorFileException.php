<?php

namespace Fostam\File\Exception;

use Throwable;

class GetPositionErrorFileException extends FileException {
    public function __construct(string $filename, string $error = '', Throwable $previous = null) {
        parent::__construct($filename, "can't get current position for file {$filename}", $error, $previous);
    }
}