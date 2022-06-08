<?php

namespace GSpataro\Test\FileSystem;

use GSpataro\FileSystem;
use org\bovigo\vfs\vfsStream;
use PHPUnit\Framework\TestCase;

final class DirectoryTest extends TestCase
{
    /**
     * Return an instance of vfsStream
     *
     * @return object
     */

    public function getVfsStream(): object
    {
        return vfsStream::setup(
            rootDirName: "root",
            structure: [
                "directory" => [],
                "full_directory" => [
                    "sub_directory" => [],
                    "sub_document.txt" => ""
                ],
                "document.txt" => ""
            ]
        );
    }

    /**
     * @testdox Test Directory class initialization
     * @return void
     */

    public function testClassInitialization(): void
    {
        $this->expectException(FileSystem\Exception\PathIsNotDirectoryException::class);
        $vfs = $this->getVfsStream();
        new FileSystem\Directory(vfsStream::url("root/document.txt"));
    }

    /**
     * @testdox Test Directory::exists() method
     * @covers Directory::exists
     * @return void
     */

    public function testExists(): void
    {
        $vfs = $this->getVfsStream();
        $dir = new FileSystem\Directory(vfsStream::url("root/directory"));

        $this->assertTrue($vfs->hasChild("directory"));
        $this->assertTrue($dir->exists());
    }

    /**
     * @testdox Test Directory::existsOrDie() method
     * @covers Directory::existsOrDie
     * @return void
     */

    public function testExistsOrDie(): void
    {
        $this->expectException(FileSystem\Exception\DirectoryNotFoundException::class);

        $vfs = $this->getVfsStream();
        $dir = new FileSystem\Directory(vfsStream::url("root/nonexisting"));

        $this->assertFalse($vfs->hasChild("nonexisting"));
        $dir->existsOrDie();
    }

    /**
     * @testdox Test Directory::write method
     * @covers Directory::write
     * @return void
     */

    public function testWrite(): void
    {
        $vfs = $this->getVfsStream();
        $dir = new FileSystem\Directory(vfsStream::url("root/newdir"));

        $this->assertFalse($vfs->hasChild("newdir"));
        $dir->write();
        $this->assertTrue($vfs->hasChild("newdir"));
    }

    /**
     * @testdox Test Directory::read() method
     * @covers Directory::read
     * @return void
     */

    public function testRead(): void
    {
        $vfs = $this->getVfsStream();
        $dir = new FileSystem\Directory(vfsStream::url("root/full_directory"));

        $controlContent = [];
        $controlDir = vfsStream::url("root/full_directory");
        $controlDirContent = scandir($controlDir);

        foreach ($controlDirContent as $item) {
            $itemPath = "{$controlDir}/{$item}";

            if ($item == "." || $item == ".." || is_link($itemPath)) {
                continue;
            }

            if (is_file($itemPath)) {
                $controlContent[$itemPath] = new FileSystem\File($itemPath);
            } elseif (is_dir($itemPath)) {
                $controlContent[$itemPath] = new FileSystem\Directory($itemPath);
            }
        }

        $this->assertEquals($dir->read(), $controlContent);
    }

    /**
     * @testdox Test Directory::empty() method
     * @covers Directory::empty
     * @return void
     */

    public function testEmpty(): void
    {
        $vfs = $this->getVfsStream();
        $emptyDir = new FileSystem\Directory(vfsStream::url("root/directory"));
        $fullDir = new FileSystem\Directory(vfsStream::url("root/full_directory"));

        $this->assertTrue($emptyDir->empty());
        $this->assertFalse($fullDir->empty());
    }

    /**
     * @testdox Test Directory::delete() method
     * @covers Directory::delete
     * @return void
     */

    public function testDelete(): void
    {
        $vfs = $this->getVfsStream();
        $emptyDir = new FileSystem\Directory(vfsStream::url("root/directory"));
        $fullDir = new FileSystem\Directory(vfsStream::url("root/full_directory"));

        $this->assertTrue($vfs->hasChild("directory"));
        $this->assertTrue($vfs->hasChild("full_directory"));

        $emptyDir->delete();
        $fullDir->delete(true);

        $this->assertFalse($vfs->hasChild("directory"));
        $this->assertFalse($vfs->hasChild("full_directory"));
    }

    /**
     * @testdox Test Directory::delete() method with recursive error
     * @covers Directory::delete
     * @return void
     */

    public function testDeleteWithRecursiveError(): void
    {
        $this->expectException(FileSystem\Exception\DirectoryIsNotEmptyException::class);

        $vfs = $this->getVfsStream();
        $dir = new FileSystem\Directory(vfsStream::url("root/full_directory"));

        $this->assertTrue($vfs->hasChild("full_directory"));
        $dir->delete();
    }

    /**
     * @testdox Test Directory::move() method
     * @covers Directory::move
     * @return void
     */

    public function testMove(): void
    {
        $vfs = $this->getVfsStream();
        $dir = new FileSystem\Directory(vfsStream::url("root/full_directory"));

        $this->assertTrue($vfs->hasChild("full_directory"));
        $this->assertFalse($vfs->hasChild("full_directory_new"));

        $newDir = $dir->move(vfsStream::url("root/full_directory_new"));

        $this->assertFalse($vfs->hasChild("full_directory"));
        $this->assertTrue($vfs->hasChild("full_directory_new"));
        $this->assertTrue($vfs->hasChild("full_directory_new/sub_directory"));
        $this->assertTrue($vfs->hasChild("full_directory_new/sub_document.txt"));
        $this->assertInstanceOf(FileSystem\Directory::class, $newDir);
    }

    /**
     * @testdox Test Directory::move() method with overwrite error
     * @covers Directory::move
     * @return void
     */

    public function testMoveWithOverwriteError(): void
    {
        $this->expectException(FileSystem\Exception\DirectoryFoundException::class);

        $vfs = $this->getVfsStream();
        $dir = new FileSystem\Directory(vfsStream::url("root/full_directory"));

        $this->assertTrue($vfs->hasChild("full_directory"));
        $this->assertTrue($vfs->hasChild("directory"));

        $dir->move(vfsStream::url("root/directory"));
    }

    /**
     * @testdox Test Directory::copy() method
     * @covers Directory::copy
     * @return void
     */

    public function testCopy(): void
    {
        $vfs = $this->getVfsStream();
        $dir = new FileSystem\Directory(vfsStream::url("root/full_directory"));

        $this->assertTrue($vfs->hasChild("full_directory"));
        $this->assertFalse($vfs->hasChild("full_directory_copy"));

        $newDir = $dir->copy(vfsStream::url("root/full_directory_copy"));

        $this->assertTrue($vfs->hasChild("full_directory"));
        $this->assertTrue($vfs->hasChild("full_directory_copy"));

        $this->assertEquals(
            scandir(vfsStream::url("root/full_directory")),
            scandir(vfsStream::url("root/full_directory_copy"))
        );

        $this->assertInstanceOf(FileSystem\Directory::class, $newDir);
    }

    /**
     * @testdox Test Directory::copy() method with overwrite error
     * @covers Directory::copy
     * @return void
     */

    public function testCopyWithOverwriteError(): void
    {
        $this->expectException(FileSystem\Exception\DirectoryFoundException::class);

        $vfs = $this->getVfsStream();
        $dir = new FileSystem\Directory(vfsStream::url("root/full_directory"));

        $this->assertTrue($vfs->hasChild("full_directory"));
        $this->assertTrue($vfs->hasChild("directory"));

        $dir->copy(vfsStream::url("root/directory"));
    }
}
