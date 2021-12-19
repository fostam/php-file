<?php

namespace Fostam\File\Exception;

use Throwable;

class LockWouldBlockException extends FileException {
    public function __construct(string $filename, Throwable $previous = null) {
        parent::__construct($filename, "lock on {$filename} would block", '', $previous);
    }
}