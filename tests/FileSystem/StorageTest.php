<?php

namespace GSpataro\Test\FileSystem;

use GSpataro\FileSystem;
use org\bovigo\vfs\vfsStream;
use PHPUnit\Framework\TestCase;

final class StorageTest extends TestCase
{
    /**
     * Get an instance of VfsStream
     *
     * @return object
     */

    public function getVfsStream(): object
    {
        return vfsStream::setup(
            rootDirName: "root",
            structure: [
                "directory" => [],
                "document.txt" => ""
            ]
        );
    }

    /**
     * @testdox Test class initialization
     * @return void
     */

    public function testClassInitialization(): void
    {
        $this->expectException(FileSystem\Exception\DirectoryNotFoundException::class);

        $vfs = $this->getVfsStream();
        $this->assertFalse($vfs->hasChild("root/nonexisting"));

        new FileSystem\Storage(vfsStream::url("root/nonexisting"));
    }

    /**
     * @testdox Test Storage::openDir() method
     * @covers Storage::openDir
     * @return void
     */

    public function testOpenDir(): void
    {
        $vfs = $this->getVfsStream();
        $storage = new FileSystem\Storage(vfsStream::url("root"));
        $dir = $storage->openDir("directory");

        $this->assertInstanceOf(FileSystem\Directory::class, $dir);
        $this->assertTrue($vfs->hasChild("directory"));
        $this->assertTrue($dir->exists());
    }

    /**
     * @testdox Test Storage::openFile() method
     * @covers Storage::openFile
     * @return void
     */

    public function testOpenFile(): void
    {
        $vfs = $this->getVfsStream();
        $storage = new FileSystem\Storage(vfsStream::url("root"));
        $file = $storage->openFile("document.txt");

        $this->assertInstanceOf(FileSystem\File::class, $file);
        $this->assertTrue($vfs->hasChild("document.txt"));
        $this->assertTrue($file->exists());
    }
}
