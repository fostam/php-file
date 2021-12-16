<?php

namespace Fostam\File;

use Exception;

class File {
    const MODE_READ = 'r';
    const MODE_READWRITE = 'r+';
    const MODE_WRITE_CREATE_OR_TRUNCATE = 'w';
    const MODE_READWRITE_CREATE_OR_TRUNCATE = 'w+';
    const MODE_WRITE_APPEND_CREATE = 'a';
    const MODE_READWRITE_APPEND_CREATE = 'a+';
    const MODE_WRITE_CREATE_NEW = 'x';
    const MODE_READWRITE_CREATE_NEW = 'x+';
    const MODE_WRITE_CREATE = 'c';
    const MODE_READWRITE_CREATE = 'c+';

    private string $filename;
    private string $mode;
    /** @var resource */
    private $fh;

    public function __construct(string $filename, string $mode) {
        $this->filename = $filename;
        $this->mode = $mode;
    }

    /**
     * @throws Exception
     */
    public function __destruct() {
        $this->close();
    }

    /**
     * @throws Exception
     */
    public function open() {
        if ($this->fh) {
            return;
        }

        $this->fh = fopen($this->filename, $this->mode);
        if ($this->fh === false) {
            $this->fh = null;
            throw new Exception("can't open file {$this->filename}");
        }
    }

    /**
     * @throws Exception
     */
    public function close() {
        if (!is_null($this->fh)) {
            if (!fclose($this->fh)) {
                throw new Exception("can't close file {$this->filename}");
            }
            $this->fh = null;
        }
    }

    /**
     * @throws Exception
     */
    public function setPos(int $pos): void {
        $this->open();
        $this->seek($pos);
    }

    /**
     * @throws Exception
     */
    public function getPos(): int {
        $this->open();
        $pos = ftell($this->fh);
        if ($pos === false) {
            throw new Exception("can't get current position in {$this->filename}");
        }
        return $pos;
    }

    /**
     * @throws Exception
     */
    public function readLine(?int $maxBytes = null, int $pos = null): ?string {
        $this->open();
        $this->seek($pos);

        if (is_null($maxBytes)) {
            $line = fgets($this->fh);
        }
        else {
            $line = fgets($this->fh, $maxBytes + 1);
        }

        if ($line === false) {
            if (feof($this->fh)) {
                return null;
            }
            else {
                throw new Exception("can't read line from {$this->filename} on position {$pos}");
            }
        }

        return $line;
    }

    /**
     * @throws Exception
     */
    public function readBytes(int $length, int $pos = null): ?string {
        $this->open();
        $this->seek($pos);

        $data = fread($this->fh, $length);

        if ($data === false) {
            if (feof($this->fh)) {
                return null;
            }
            else {
                throw new Exception("can't read from {$this->filename} on position {$pos}");
            }
        }

        return $data;
    }

    /**
     * @throws Exception
     */
    public function write(string $data, int $maxBytes = null, int $pos = null): int {
        if (!is_null($pos) && ($this->mode === self::MODE_WRITE_APPEND_CREATE || $this->mode === self::MODE_READWRITE_APPEND_CREATE)) {
            throw new Exception("position can't be set in append mode");
        }

        $this->open();
        $this->seek($pos);

        if (is_null($maxBytes)) {
            $bytesWritten = fwrite($this->fh, $data);
        }
        else {
            $bytesWritten = fwrite($this->fh, $data, $maxBytes);
        }

        if ($bytesWritten === false) {
            throw new Exception("error writing to {$this->filename} on position {$pos}");
        }

        return $bytesWritten;
    }

    /**
     * @throws Exception
     */
    public function truncate(int $size = 0): void {
        if ($size < 0) {
            throw new Exception("truncate size can't be lower than 0");
        }

        $this->open();
        if (!ftruncate($this->fh, $size)) {
            throw new Exception("couldn't truncate file '{$this->filename}'");
        }
    }

    /**
     * @throws Exception
     */
    public function flush(): void {
        $this->open();
        if (!fflush($this->fh)) {
            throw new Exception("couldn't flush file '{$this->filename}'");
        }
    }

    /**
     * @throws Exception
     */
    public function lock(int $operation, int &$would_block = null) {
        $this->open();
        if (!flock($this->fh, $operation, $would_block)) {
            throw new Exception("couldn't lock file '{$this->filename}'");
        }
    }

    /**
     * @throws Exception
     */
    public function stat(): array {
        $this->open();
        $result = fstat($this->fh);
        if ($result === false) {
            throw new Exception("couldn't stat file '{$this->filename}'");
        }
        return $result;
    }

    /**
     * @throws Exception
     */
    private function seek(?int $pos): void {
        if (is_null($pos)) {
            return;
        }

        if (fseek($this->fh, $pos) !== 0) {
            throw new Exception("seek to position {$pos} in {$this->filename} failed");
        }
    }
}