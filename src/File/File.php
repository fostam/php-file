<?php

namespace Fostam\File;

use Exception;
use Fostam\File\Exception\{
    FileException,
    ValueErrorFileException,
    FlushErrorFileException,
    OpenFileErrorFileException,
    CloseFileErrorFileException,
    LockWouldBlockException,
    ReadErrorFileException,
    WriteErrorFileException,
    TruncateErrorFileException,
    SetPositionErrorFileException,
    GetPositionErrorFileException
};


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
     * @throws CloseFileErrorFileException
     */
    public function __destruct() {
        $this->close();
    }

    /**
     * @throws OpenFileErrorFileException
     */
    public function open() {
        if ($this->fh) {
            return;
        }

        error_clear_last();
        try {
            $this->fh = @fopen($this->filename, $this->mode);
        }
        catch (Exception $e) {
            throw new OpenFileErrorFileException($this->filename, $this->getLastErrorMessage(), $e);
        }

        if ($this->fh === false) {
            $this->fh = null;
            throw new OpenFileErrorFileException($this->filename, $this->getLastErrorMessage());
        }
    }

    /**
     * @throws CloseFileErrorFileException
     */
    public function close() {
        if (is_null($this->fh)) {
            return;
        }

        error_clear_last();
        try {
            $result = @fclose($this->fh);
        }
        catch (Exception $e) {
            throw new CloseFileErrorFileException($this->filename, $this->getLastErrorMessage(), $e);
        }

        if ($result !== true) {
            throw new CloseFileErrorFileException($this->filename);
        }

        $this->fh = null;
    }

    /**
     * @throws OpenFileErrorFileException
     * @throws SetPositionErrorFileException
     * @throws GetPositionErrorFileException
     */
    public function setPos(?int $pos): int {
        $this->open();
        $this->seek($pos);
        return $this->tell();
    }

    /**
     * @throws OpenFileErrorFileException
     * @throws GetPositionErrorFileException
     */
    public function getPos(): int {
        $this->open();
        return $this->tell();
    }

    /**
     * @throws OpenFileErrorFileException
     * @throws SetPositionErrorFileException
     * @throws GetPositionErrorFileException
     * @throws ReadErrorFileException
     * @throws FileException
     */
    public function readLine(?int $maxBytes = null, int $pos = null): ?string {
        $this->open();
        $this->seek($pos);
        $pos = $this->tell();

        error_clear_last();
        try {
            if (is_null($maxBytes)) {
                $line = @fgets($this->fh);
            }
            else {
                $line = @fgets($this->fh, $maxBytes + 1);
            }
        }
        catch (Exception $e) {
            throw new ReadErrorFileException($this->filename, $pos, $this->getLastErrorMessage(), $e);
        }

        if ($line === false) {
            if ($this->isAtEOF()) {
                return null;
            }
            else {
                throw new ReadErrorFileException($this->filename, $pos, $this->getLastErrorMessage());
            }
        }

        return $line;
    }

    /**
     * @throws OpenFileErrorFileException
     * @throws SetPositionErrorFileException
     * @throws GetPositionErrorFileException
     * @throws ReadErrorFileException
     * @throws FileException
     */
    public function readBytes(int $length, int $pos = null): ?string {
        $this->open();
        $this->seek($pos);
        $pos = $this->tell();

        error_clear_last();
        try {
            $data = @fread($this->fh, $length);
        }
        catch (Exception $e) {
            throw new ReadErrorFileException($this->filename, $pos, $this->getLastErrorMessage(), $e);
        }

        if ($data === false) {
            if (feof($this->fh)) {
                return null;
            }
            else {
                throw new ReadErrorFileException($this->filename, $pos, $this->getLastErrorMessage());
            }
        }

        return $data;
    }

    /**
     * @throws FileException
     * @throws OpenFileErrorFileException
     * @throws SetPositionErrorFileException
     * @throws GetPositionErrorFileException
     * @throws WriteErrorFileException
     */
    public function write(string $data, int $maxBytes = null, int $pos = null): int {
        if (!is_null($pos) && ($this->mode === self::MODE_WRITE_APPEND_CREATE || $this->mode === self::MODE_READWRITE_APPEND_CREATE)) {
            throw new FileException($this->filename, "position can't be set in append mode");
        }

        $this->open();
        $this->seek($pos);
        $pos = $this->tell();

        error_clear_last();
        try {
            if (is_null($maxBytes)) {
                $bytesWritten = @fwrite($this->fh, $data);
            }
            else {
                $bytesWritten = @fwrite($this->fh, $data, $maxBytes);
            }
        }
        catch (Exception $e) {
            throw new WriteErrorFileException($this->filename, $pos, $this->getLastErrorMessage(), $e);
        }

        if ($bytesWritten === false) {
            throw new WriteErrorFileException($this->filename, $pos, $this->getLastErrorMessage());
        }

        return $bytesWritten;
    }

    /**
     * @throws ValueErrorFileException
     * @throws OpenFileErrorFileException
     * @throws TruncateErrorFileException
     */
    public function truncate(int $size = 0): void {
        if ($size < 0) {
            throw new ValueErrorFileException($this->filename, "truncate size can't be lower than 0");
        }

        $this->open();

        error_clear_last();
        try {
            $result = @ftruncate($this->fh, $size);
        }
        catch (Exception $e) {
            throw new TruncateErrorFileException($this->filename, $this->getLastErrorMessage(), $e);
        }

        if ($result !== true) {
            throw new TruncateErrorFileException($this->filename, $this->getLastErrorMessage());
        }
    }

    /**
     * @throws OpenFileErrorFileException
     * @throws FlushErrorFileException
     */
    public function flush(): void {
        $this->open();

        error_clear_last();
        try {
            $result = @fflush($this->fh);
        }
        catch (Exception $e) {
            throw new FlushErrorFileException($this->filename, $this->getLastErrorMessage(), $e);
        }

        if ($result !== true) {
            throw new FlushErrorFileException($this->filename, $this->getLastErrorMessage());
        }
    }

    /**
     * @throws FileException
     * @throws OpenFileErrorFileException
     */
    public function lockShared(bool $nonBlocking = false) {
        $this->lock(LOCK_SH, $nonBlocking);
    }

    /**
     * @throws FileException
     * @throws OpenFileErrorFileException
     */
    public function lockExclusive(bool $nonBlocking = false) {
        $this->lock(LOCK_EX, $nonBlocking);
    }

    /**
     * @throws FileException
     * @throws OpenFileErrorFileException
     */
    public function unlock(bool $nonBlocking = false) {
        $this->lock(LOCK_UN, $nonBlocking);
    }

    /**
     * @throws OpenFileErrorFileException
     * @throws FileException
     */
    public function stat(): array {
        $this->open();

        error_clear_last();
        try {
            $result = @fstat($this->fh);
        }
        catch (Exception $e) {
            throw new FileException($this->filename, "couldn't stat file '{$this->filename}'", $this->getLastErrorMessage(), $e);
        }

        if ($result === false) {
            throw new FileException($this->filename, "couldn't stat file '{$this->filename}'", $this->getLastErrorMessage());
        }
        return $result;
    }

    /**
     * @return bool
     * @throws FileException
     */
    public function isAtEOF(): bool {
        try {
            $result = @feof($this->fh);
        }
        catch (Exception $e) {
            throw new FileException($this->filename, '', $this->getLastErrorMessage(), $e);
        }

        if ($result === null) {
            throw new FileException($this->filename, '', $this->getLastErrorMessage());
        }

        return $result;
    }

    /**
     * @return resource
     */
    public function getFileHandle() {
        return $this->fh;
    }

    /**
     * @throws OpenFileErrorFileException
     * @throws FileException
     */
    private function lock(int $operation, bool $nonBlocking = false) {
        if ($nonBlocking) {
            $operation |= LOCK_NB;
        }

        $this->open();

        error_clear_last();
        try {
            $result = @flock($this->fh, $operation, $wouldBlock);
        }
        catch (Exception $e) {
            throw new FileException($this->filename, "couldn't lock file '{$this->filename}'", $this->getLastErrorMessage(), $e);
        }

        if ($wouldBlock === 1) {
            throw new LockWouldBlockException($this->filename);
        }

        if ($result !== true) {
            throw new FileException($this->filename, "couldn't lock file '{$this->filename}'", $this->getLastErrorMessage());
        }
    }

    /**
     * @throws SetPositionErrorFileException
     */
    private function seek(?int $pos): void {
        if (is_null($pos)) {
            return;
        }

        error_clear_last();
        try {
            $result = @fseek($this->fh, $pos);
        }
        catch (Exception $e) {
            throw new SetPositionErrorFileException($this->filename, $pos, $this->getLastErrorMessage(), $e);
        }

        if ($result !== 0) {
            throw new SetPositionErrorFileException($this->filename, $pos, $this->getLastErrorMessage());
        }
    }

    /**
     * @throws GetPositionErrorFileException
     */
    private function tell(): int {
        error_clear_last();
        try {
            $pos = @ftell($this->fh);
        }
        catch (Exception $e) {
                throw new GetPositionErrorFileException($this->filename, $this->getLastErrorMessage(), $e);
        }

        if ($pos === false) {
            throw new GetPositionErrorFileException($this->filename, $this->getLastErrorMessage());
        }

        return $pos;
    }

    private function getLastErrorMessage(): string {
        $lastError = error_get_last();
        if (!$lastError) {
            return '';
        }

        return $lastError['message'];
    }
}