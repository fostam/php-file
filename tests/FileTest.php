<?php

namespace Fostam\File\Tests;

use Exception;
use Fostam\File\Exception\OpenFileErrorFileException;
use PHPUnit\Framework\TestCase;
use Fostam\File\File;
use Fostam\File\Exception\SetPositionErrorFileException;


final class FileTest extends TestCase {
    private string $testFileName;
    private string $testFileContents = "a1234567890\nb12345678901234567890\nc1234567890\nd12345678901234567890\n";

    protected function setUp(): void {
        $this->testFileName = sys_get_temp_dir() . '/php-reader-test.txt';
        file_put_contents($this->testFileName, $this->testFileContents);
    }

    protected function tearDown(): void {
        unlink($this->testFileName);
    }

    public function testOpenFile(): void {
        $file = new File($this->testFileName . strval(time()), File::MODE_READ);

        $this->expectException(OpenFileErrorFileException::class);
        $file->open();
    }

    /**
     * @throws Exception
     */
    public function testReadLines(): void {
        $file = new File($this->testFileName, File::MODE_READ);

        $line = $file->readLine();
        $this->assertEquals("a1234567890\n", $line);

        $line = $file->readLine();
        $this->assertEquals("b12345678901234567890\n", $line);

        $line = $file->readLine();
        $this->assertEquals("c1234567890\n", $line);

        $line = $file->readLine();
        $this->assertEquals("d12345678901234567890\n", $line);

        $line = $file->readLine();
        $this->assertEquals(null, $line);

        $file->close();
    }

    /**
     * @throws Exception
     */
    public function testReadLinesWithPosition(): void {
        $file = new File($this->testFileName, File::MODE_READ);

        $line = $file->readLine(null, 0);
        $this->assertEquals("a1234567890\n", $line);
        $this->assertEquals(12, $file->getPos());

        $line = $file->readLine(null, 0);
        $this->assertEquals("a1234567890\n", $line);
        $this->assertEquals(12, $file->getPos());

        $line = $file->readLine(null, 34);
        $this->assertEquals("c1234567890\n", $line);
        $this->assertEquals(46, $file->getPos());

        $line = $file->readLine(null, 67);
        $this->assertEquals("\n", $line);
        $this->assertEquals(strlen($this->testFileContents), $file->getPos());

        $line = $file->readLine(null, 999);
        $this->assertEquals(null, $line);
        $this->assertEquals(999, $file->getPos());

        $this->expectException(SetPositionErrorFileException::class);
        $file->readLine(null, -1);

        $file->close();
    }

    /**
     * @throws Exception
     */
    public function testReadLinesWithLimit(): void {
        $file = new File($this->testFileName, File::MODE_READ);

        $line = $file->readLine(4);
        $this->assertEquals("a123", $line);

        $line = $file->readLine(6);
        $this->assertEquals("456789", $line);

        $line = $file->readLine(999);
        $this->assertEquals("0\n", $line);

        $pos = 0;
        $line = $file->readLine(999, $pos);
        $this->assertEquals("a1234567890\n", $line);

        $file->close();
    }

    /**
     * @throws Exception
     */
    public function testReadBytes(): void {
        $file = new File($this->testFileName, File::MODE_READ);

        $data = $file->readBytes(11);
        $this->assertEquals("a1234567890", $data);

        $data = $file->readBytes(11);
        $this->assertEquals("\nb123456789", $data);

        $data = $file->readBytes(999);
        $this->assertEquals("01234567890\nc1234567890\nd12345678901234567890\n", $data);

        $data = $file->readBytes(1);
        $this->assertEquals(null, $data);

        $file->close();
    }

    /**
     * @throws Exception
     */
    public function testReadBytesWithPosition(): void {
        $file = new File($this->testFileName, File::MODE_READ);

        $data = $file->readBytes(strlen($this->testFileContents), 0);
        $this->assertEquals($this->testFileContents, $data);
        $this->assertEquals(strlen($this->testFileContents), $file->getPos());

        $data = $file->readBytes(11, 33);
        $this->assertEquals("\nc123456789", $data);
        $this->assertEquals(44, $file->getPos());

        $data = $file->readBytes(999, $file->getPos());
        $this->assertEquals("0\nd12345678901234567890\n", $data);
        $this->assertEquals(strlen($this->testFileContents), $file->getPos());

        $data = $file->readBytes(1);
        $this->assertEquals(null, $data);
        $this->assertEquals(strlen($this->testFileContents), $file->getPos());

        $this->expectException(SetPositionErrorFileException::class);
        $file->readBytes(999, -1);

        $file->close();
    }

    public function testWriteBytes(): void {
        $file = new File($this->testFileName, File::MODE_READWRITE);

        $testStr = 'ABCDEFGHIJ';
        $file->write($testStr);
        $data = $file->readBytes(5);
        $this->assertEquals("0\nb12", $data);
        $data = $file->readBytes(15, 0);
        $this->assertEquals("ABCDEFGHIJ0\nb12", $data);

        $file->close();
    }

    public function testTruncate(): void {
        $file = new File($this->testFileName, File::MODE_READWRITE);

        $file->truncate(15);
        $data = $file->readBytes(999);
        $this->assertEquals("a1234567890\nb12", $data);

        $file->truncate();
        $data = $file->readBytes(999);
        $this->assertEquals("", $data);

        $this->expectException(Exception::class);
        $file->truncate(-1);

        $file->close();
    }

    public function testPositioning(): void {
        $file = new File($this->testFileName, File::MODE_READWRITE);

        $this->assertEquals(0, $file->getPos());

        $file->setPos(20);
        $this->assertEquals(20, $file->getPos());

        $file->setPos(999);
        $this->assertEquals(999, $file->getPos());

        $this->expectException(SetPositionErrorFileException::class);
        $file->setPos(-1);

        $file->close();
    }

    public function testLock(): void {
        $file = new File($this->testFileName, File::MODE_READWRITE);

        $file->lockExclusive();
        $file->lockShared();
        $file->unlock();

        $file->close();

        $this->assertTrue(true);
    }

    public function testStat(): void {
        $file = new File($this->testFileName, File::MODE_READ);

        $stat = $file->stat();
        $this->assertEquals(68, $stat['size']);

        $file->close();
    }
}