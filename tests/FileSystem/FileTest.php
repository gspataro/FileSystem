<?php

namespace GSpataro\Test\FileSystem;

use GSpataro\FileSystem;
use PHPUnit\Framework\TestCase;
use org\bovigo\vfs\vfsStream;

final class FileTest extends TestCase
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
                "document.txt" => "lorem ipsum...",
                "script.php" => "<?php return true;"
            ]
        );
    }

    /**
     * @testdox Test file class initialization
     * @return void
     */

    public function testClassInitialization(): void
    {
        $this->expectException(FileSystem\Exception\PathIsNotFileException::class);
        $vfs = $this->getVfsStream();
        new FileSystem\File(vfsStream::url("root/directory"));
    }

    /**
     * @testdox Test File::exists() method
     * @covers File::exists
     * @return void
     */

    public function testExists(): void
    {
        $vfs = $this->getVfsStream();
        $file = new FileSystem\File(vfsStream::url("root/document.txt"));

        $this->assertTrue($vfs->hasChild("document.txt"));
        $this->assertTrue($file->exists());
    }

    /**
     * @testdox Test File::existsOrDie() method
     * @covers File::existsOrDie
     * @return void
     */

    public function testExistsOrDie(): void
    {
        $this->expectException(FileSystem\Exception\FileNotFoundException::class);

        $vfs = $this->getVfsStream();
        $file = new FileSystem\File(vfsStream::url("root/nonexisting.txt"));
        $file->existsOrDie();
    }

    /**
     * @testdox Test File::write() method
     * @covers File::write
     * @return array
     */

    public function testWrite(): array
    {
        $vfs = $this->getVfsStream();
        $file = new FileSystem\File(vfsStream::url("root/newfile.txt"));
        $fileContent = "test";

        $this->assertFalse($vfs->hasChild("newfile.txt"));

        $file->write($fileContent);

        $this->assertTrue($vfs->hasChild("newfile.txt"));
        $this->assertEquals(file_get_contents(vfsStream::url("root/newfile.txt")), $fileContent);

        return [
            "vfs" => $vfs,
            "file" => $file,
            "fileContent" => $fileContent
        ];
    }

    /**
     * @testdox Test File::write() method with append option
     * @covers File::write
     * @depends testWrite
     * @return void
     */

    public function testWriteAppend(array $params): void
    {
        $vfs = $params['vfs'];
        $file = $params['file'];
        $fileContent = $params['fileContent'];

        $this->assertTrue($vfs->hasChild("newfile.txt"));

        $file->write($fileContent, false);

        $this->assertEquals(file_get_contents(vfsStream::url("root/newfile.txt")), $fileContent . $fileContent);
    }

    /**
     * @testdox Test File::write() method with wrong permissions
     * @covers File::write
     * @depends testWrite
     * @return void
     */

    public function testWritePermissionsError(array $params): void
    {
        $this->expectException(FileSystem\Exception\FilePermissionsException::class);

        $vfs = $params['vfs'];
        $file = $params['file'];

        $this->assertTrue($vfs->hasChild("newfile.txt"));
        chmod(vfsStream::url("root/newfile.txt"), 0577);

        $file->write("test");
    }

    /**
     * @testdox Test File::read() method
     * @covers File::read
     * @return array
     */

    public function testRead(): array
    {
        $vfs = $this->getVfsStream();
        $file = new FileSystem\File(vfsStream::url("root/document.txt"));

        $this->assertTrue($vfs->hasChild("document.txt"));
        $this->assertEquals($file->read(), file_get_contents(vfsStream::url("root/document.txt")));

        return [
            "vfs" => $vfs,
            "file" => $file
        ];
    }

    /**
     * @testdox Test File::read() method with wrong permissions
     * @covers File::read
     * @depends testRead
     * @return void
     */

    public function testReadPermissionsError(array $params): void
    {
        $this->expectException(FileSystem\Exception\FilePermissionsException::class);

        $vfs = $params['vfs'];
        $file = $params['file'];

        $this->assertTrue($vfs->hasChild("document.txt"));
        chmod(vfsStream::url("root/document.txt"), 0277);

        $file->read();
    }

    /**
     * @testdox Test File::matchExtensions() method
     * @covers File::matchExtensions
     * @return void
     */

    public function testMatchExtensions(): void
    {
        $vfs = $this->getVfsStream();
        $file = new FileSystem\File(vfsStream::url("root/document.txt"));

        $this->assertTrue($file->matchExtensions("txt"));
        $this->assertTrue($file->matchExtensions(["php", "html", "txt"]));
        $this->assertFalse($file->matchExtensions("php"));
        $this->assertFalse($file->matchExtensions(["php", "html", "jpg"]));
    }

    /**
     * @testdox Test File::matchExtensionsOrDie() method
     * @covers File::matchExtensionsOrDie
     * @return void
     */

    public function testMatchExtensionsOrDie(): void
    {
        $this->expectException(FileSystem\Exception\FileExtensionNotAllowedException::class);

        $vfs = $this->getVfsStream();
        $file = new FileSystem\File(vfsStream::url("root/document.txt"));

        $file->matchExtensionsOrDie("php");
    }

    /**
     * @testdox Test File::import() method
     * @covers File::import
     * @return void
     */

    public function testImport(): void
    {
        $vfs = $this->getVfsStream();
        $file = new FileSystem\File(vfsStream::url("root/script.php"));

        $this->assertTrue($vfs->hasChild("script.php"));
        $this->assertTrue($file->import());
    }

    /**
     * @testdox Test File::delete() method
     * @covers File::delete
     * @return void
     */

    public function testDelete(): void
    {
        $vfs = $this->getVfsStream();
        $file = new FileSystem\File(vfsStream::url("root/document.txt"));

        $this->assertTrue($vfs->hasChild("document.txt"));
        $file->delete();
        $this->assertFalse($vfs->hasChild("document.txt"));
    }

    /**
     * @testdox Test File::move() method
     * @covers File::move
     * @return void
     */

    public function testMove(): void
    {
        $vfs = $this->getVfsStream();
        $file = new FileSystem\File(vfsStream::url("root/document.txt"));

        $this->assertTrue($vfs->hasChild("document.txt"));
        $newFile = $file->move(vfsStream::url("root/moved.txt"));
        $this->assertFalse($vfs->hasChild("document.txt"));
        $this->assertTrue($vfs->hasChild("moved.txt"));
        $this->assertTrue($newFile->exists());
        $this->assertEquals($newFile->getPathname(), vfsStream::url("root/moved.txt"));
    }

    /**
     * @testdox Test File::move() method with overwrite error
     * @covers File::move
     * @return void
     */

    public function testMoveOverwriteError(): void
    {
        $this->expectException(FileSystem\Exception\FileFoundException::class);

        $vfs = $this->getVfsStream();
        $file = new FileSystem\File(vfsStream::url("root/script.php"));

        $this->assertTrue($vfs->hasChild("script.php"));
        $this->assertTrue($vfs->hasChild("document.txt"));

        $file->move(vfsStream::url("root/document.txt"));
    }

    /**
     * @testdox Test File::copy() method
     * @covers File::copy
     * @return void
     */

    public function testCopy(): void
    {
        $vfs = $this->getVfsStream();
        $file = new FileSystem\File(vfsStream::url("root/document.txt"));

        $this->assertTrue($vfs->hasChild("document.txt"));
        $this->assertFalse($vfs->hasChild("document_copy.txt"));

        $newFile = $file->copy(vfsStream::url("root/document_copy.txt"));

        $this->assertTrue($vfs->hasChild("document.txt"));
        $this->assertTrue($vfs->hasChild("document_copy.txt"));
        $this->assertTrue($newFile->exists());
        $this->assertEquals($newFile->getPAthname(), vfsStream::url("root/document_copy.txt"));
    }

    /**
     * @testdox Test File::copy() method with overwrite error
     * @covers File::copy
     * @return void
     */

    public function testCopyOverwriteError(): void
    {
        $this->expectException(FileSystem\Exception\FileFoundException::class);

        $vfs = $this->getVfsStream();
        $file = new FileSystem\File(vfsStream::url("root/document.txt"));

        $this->assertTrue($vfs->hasChild("document.txt"));
        $this->assertTrue($vfs->hasChild("script.php"));

        $file->copy(vfsStream::url("root/script.php"));
    }
}
