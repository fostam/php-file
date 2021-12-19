<?php

namespace Fostam\File\Exception;

use Exception;
use Throwable;

class FileException extends Exception {
    private string $filename;

    public function __construct(string $filename, string $message, string $error = '', Throwable $previous = null) {
        $this->filename = $filename;
        if ($error) {
            $message .= ': ' . $error;
        }
        parent::__construct($message, 0, $previous);
    }

    public function getFilename(): string {
        return $this->filename;
    }
}
