# fostam/file

__File__ is a simple convenience wrapper for PHP functions like `fopen()`, `fgets()`. It offers object
oriented usage of file functions, and handles errors with exceptions instead of return results
and PHP warnings/errors.

## Install
The easiest way to install __File__ is by using [composer](https://getcomposer.org/):

```
$> composer require fostam/file
```

## Usage

__Example:__ print the contents of a file, line by line
```php
<?php

use Fostam\File\File;

$reader = new File($filename, File::MODE_READ);

while ($line = $reader->readLine()) {
    print $line;
}

$reader->close();
```

__Example:__ write into a file
```php
$writer = new File($filename, File::MODE_WRITE_CREATE_OR_TRUNCATE);
$writer->write('test');
$writer->close();
```

__Example:__ resumt reading a file from a known position
```php
$reader = new File($filename, File::MODE_READ);

// (read $pos from previous run)

while ($line = $reader->readLine(null, $pos)) {
    // (process $line)
    // (save $pos to resume if interrupted)
}

$reader->close();
```

## Errors
All errors are caught and passed on by throwing an Exception. All thrown Exceptions
derive from the `Fostam\File\Exception\FileException` class.

## Reference
### Methods

| Method                                                       | Return value | Description                                                                     | Equivalent                                                                 |
|--------------------------------------------------------------|--------------|---------------------------------------------------------------------------------|----------------------------------------------------------------------------|
| `__construct(string $filename, string $mode)`                | *void*       | Constructor                                                                     | -                                                                          |                                                                   |
| `open()`                                                     | *void*       | Open the file                                                                   | [fopen()](https://www.php.net/manual/en/function.fopen.php)                |                                                              |
| `close(bool $silent = false)`                                | *void*       | Close the file                                                                  | [fclose()](https://www.php.net/manual/en/function.fclose.php)              |
| `setPos(int $pos)`                                           | *void*       | Set the file pointer to position *$pos*                                         | [fseek()](https://www.php.net/manual/en/function.fseek.php)                |                                                            |
| `getPos()`                                                   | `int`        | Get the current file position                                                   | [ftell()](https://www.php.net/manual/en/function.ftell.php)                |                                                            |
| `readLine(?int $maxBytes = null, int $pos = null)`           | `?string`    | Get the line (up to *$maxBytes* bytes) at the current position, or *$pos*       | [fgets()](https://www.php.net/manual/en/function.fgets.php)                |
| `readBytes(int $length, int $pos = null)`                    | `?string`    | Get *$length* bytes from the current position, or *$pos*                        | [fread()](https://www.php.net/manual/en/function.fread.php)                |
| `write(string $data, int $maxBytes = null, int $pos = null)` | `int`        | Write (up to *$maxBytes* bytes from) *$data* to the current position, or *$pos* | [fputs()](https://www.php.net/manual/en/function.fputs.php)                |
| `truncate(int $size = 0)`                                    | *void*       | Truncate the file to *$size* bytes                                              | [ftruncate()](https://www.php.net/manual/en/function.ftruncate.php)        |
| `flush()`                                                    | *void*       | Write all data to disk                                                          | [fflush()](https://www.php.net/manual/en/function.fflush.php)              |
| `lockShared(bool $nonBlocking = false)`                      | *void*       | Lock the file in shared mode                                                    | [flock()](https://www.php.net/manual/en/function.flock.php)                |               
| `lockExclusive(bool $nonBlocking = false)`                   | *void*       | Lock the file in exclusive mode                                                 | [flock()](https://www.php.net/manual/en/function.flock.php)                |                 
| `unlock(bool $nonBlocking = false)`                          | *void*       | Unlock the file                                                                 | [flock()](https://www.php.net/manual/en/function.flock.php)                |                      
| `stat()`                                                     | *void*       | Get file information                                                            | [fstat()](https://www.php.net/manual/en/function.fstat.php)                |                        
| `isAtEOF()`                                                  | `bool`       | Returns *true* if the file pointer is at the end of the file                    | [feof()](https://www.php.net/manual/en/function.feof.php)                  |
| `getFileHandle(): resource`                                  | *void*       | Get the file handle (e.g. to be used with other file functions)                 | [fopen()](https://www.php.net/manual/en/function.fopen.php) (return value) |


### Open File Modes

See the [fopen() documentation](https://www.php.net/manual/de/function.fopen.php) for a description of modes.

| Constant                          | Mode | Description                                         |
|-----------------------------------|------|-----------------------------------------------------|
| MODE_READ                         | r    | Read-only                                           |
| MODE_READWRITE                    | r+   | Read/Write                                          |
| MODE_WRITE_CREATE_OR_TRUNCATE     | w    | Write-only; create file, truncate if existing       |
| MODE_READWRITE_CREATE_OR_TRUNCATE | w+   | Read/Write; create file, truncate if existing       |
| MODE_WRITE_APPEND_CREATE          | a    | Write-only; append; create file if not existing     |
| MODE_READWRITE_APPEND_CREATE      | a+   | Read/Write; append; create file if not existing     |
| MODE_WRITE_CREATE_NEW             | x    | Write-only; create file, fail if existing           |
| MODE_READWRITE_CREATE_NEW         | x+   | Read/Write; create file, fail if existing           |
| MODE_WRITE_CREATE                 | c    | Write-only; create file, don't truncate if existing |
| MODE_READWRITE_CREATE             | c+   | Read/Write; create file, don't truncate if existing |

### Exceptions
- `FileException`
- `OpenFileErrorFileException`
- `CloseFileErrorFileException`
- `GetPositionErrorFileException`
- `SetPositionErrorFileException`
- `ReadErrorFileException`
- `WriteErrorFileException`
- `TruncateErrorFileException`
- `FlushErrorFileException`
- `ValueErrorFileException`
- `LockWouldBlockFileException`
