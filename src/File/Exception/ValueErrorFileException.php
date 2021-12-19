<?php

namespace Fostam\File\Exception;


class ValueErrorFileException extends FileException {
    public function __construct($filename, string $message) {
        parent::__construct($filename, $message);
    }
}