# fostam/file

__File__ is a simple convenience wrapper for PHP functions like `fopen()`, `fgets()`. It allows object
oriented usage of file functions, and handles errors with exceptions instead of return results.

## Install
The easiest way to install __File__ is by using [composer](https://getcomposer.org/):

```
$> composer require fostam/file
```

## Usage

__Example:__ print the contents of a file, line by line
```php
$reader = new File($filename, File::MODE_READ);

while ($line = $reader->getLine()) {
    print $line;
}

$reader->close();
```

__Example:__ continue reading a file from a known position
```php
$reader = new File($filename, File::MODE_READ);

// (read $pos from previous run)

while ($line = $reader->getLine(null, $pos)) {
    // (process $line)
    // (save $pos to resume if interrupted)
}

$reader->close();
```

## Reference
### Methods

```
__construct(string $filename, string $mode)
open()
close()
setPos(int $pos)
getPos(): int
getLine(?int $maxBytes = null, int $pos = null): ?string
getBytes(int $length, int $pos = null): ?string
write(string $data, int $maxBytes = null, int $pos = null): int
truncate(int $size = 0)
flush()
lock(int $operation, int &$would_block = null)
stat()
```

### Open File Modes

See the [fopen() documentation](https://www.php.net/manual/de/function.fopen.php) for a description of modes.

| Constant                          | Mode |
|-----------------------------------|------|
| MODE_READ                         | r    |
| MODE_READWRITE                    | r+   |
| MODE_WRITE_CREATE_OR_TRUNCATE     | w    |
| MODE_READWRITE_CREATE_OR_TRUNCATE | w+   |
| MODE_WRITE_APPEND_CREATE          | a    |
| MODE_READWRITE_APPEND_CREATE      | a+   |
| MODE_WRITE_CREATE_NEW             | x    |
| MODE_READWRITE_CREATE_NEW         | x+   |
| MODE_WRITE_CREATE                 | c    |
| MODE_READWRITE_CREATE             | c+   |
