<?php

namespace GSpataro\FileSystem;

use SplFileInfo;
use DirectoryIterator;

class Directory extends SplFileInfo
{
    /**
     * Cache the read content of the directory
     *
     * @var array
     */

    private array $content = [];

    /**
     * Initialize directory object
     *
     * @param string $path
     */

    public function __construct(string $path)
    {
        // If the resource exists and is not a directory throw an exception
        // If the resource doesn't exists the class can handle the creation process

        if (file_exists($path) && !is_dir($path)) {
            throw new Exception\PathIsNotDirectoryException(
                "The resource located at '{$path}' that you are trying to open is not a directory."
            );
        }

        parent::__construct($path);
    }

    /**
     * Verify if the directory exists
     *
     * @return bool
     */

    public function exists(): bool
    {
        return $this->isDir();
    }

    /**
     * Verify if the directory exists and, if not, throw an exception
     *
     * @throws Exception\DirectoryNotFoundException
     * @return void
     */

    public function existsOrDie(): void
    {
        if (!$this->exists()) {
            throw new Exception\DirectoryNotFoundException(
                "Directory located at '{$this->getPathname()}' not found."
            );
        }
    }

    /**
     * Write the directory to the filesystem
     *
     * @param bool $recursive
     * @param int $permissions
     * @return void
     */

    public function write(bool $recursive = false, int $permissions = 0777): void
    {
        if ($this->exists()) {
            return;
        }

        mkdir($this->getPathname(), $permissions, $recursive);
    }

    /**
     * Read the directory from the filesystem
     *
     * @return array
     */

    public function read(): array
    {
        $this->existsOrDie();

        if (!$this->isReadable()) {
            throw new Exception\DirectoryPermissionsException(
                "Cannot read directory located at '{$this->getPathname()}'. Check directory permissions."
            );
        }

        if (empty($this->content)) {
            $iterator = new DirectoryIterator($this->getPathname());
            $content = [];

            foreach ($iterator as $item) {
                if ($item->isDot() || $item->isLink()) {
                    continue;
                } elseif ($item->isDir()) {
                    $itemPath = str_replace("\\", "/", $item->getPathname());
                    $content[$itemPath] = new Directory($itemPath);
                } elseif ($item->isFile()) {
                    $itemPath = str_replace("\\", "/", $item->getPathname());
                    $content[$itemPath] = new File($itemPath);
                }
            }

            $this->content = $content;
        }

        return $this->content;
    }

    /**
     * Verify if the directory is empty
     *
     * @return bool
     */

    public function empty(): bool
    {
        return empty($this->read());
    }

    /**
     * Delete the directory and its content (if recursive is enabled) from the filesystem
     *
     * @param bool $recursive
     * @return void
     */

    public function delete(bool $recursive = false): void
    {
        if (!$this->empty() && !$recursive) {
            throw new Exception\DirectoryIsNotEmptyException(
                "Cannot delete directory located at '{$this->getPathname()}' because is not empty.
                Set Argument #2 to true to delete it recursively."
            );
        }

        if ($this->exists()) {
            if ($recursive) {
                foreach ($this->read() as $item) {
                    $item->delete();
                }
            }

            rmdir($this->getPathname());
        }
    }

    /**
     * Move the directory to another location and return an object for the new directory
     *
     * @param string $newPath
     * @param bool $overwrite
     * @return Directory
     */

    public function move(string $newPath, bool $overwrite = false): Directory
    {
        $newDir = new Directory($newPath);

        if (!$overwrite && $newDir->exists()) {
            throw new Exception\DirectoryFoundException(
                "Cannot move '{$this->getPathname()}' to '{$newPath}'. Set Argument #2 to true to overwrite it."
            );
        } elseif ($overwrite) {
            $newDir->delete();
        }

        rename($this->getPathname(), $newPath);

        return $newDir;
    }

    /**
     * Copy the directory to another location and return an object for the new directory
     *
     * @param string $newPath
     * @param bool $overwrite
     * @return Directory
     */

    public function copy(string $newPath, bool $overwrite = false): Directory
    {
        $newDir = new Directory($newPath);

        if (!$overwrite && $newDir->exists()) {
            throw new Exception\DirectoryFoundException(
                "Cannot copy '{$this->getPathname()}' to '{$newPath}'. Set Argument #2 to true to overwrite it."
            );
        } elseif ($overwrite) {
            $newDir->delete();
        }

        $newDir->write(true);

        foreach ($this->read() as $item) {
            $separator = str_ends_with($newPath, "/") ? null : "/";
            $item->copy("{$newPath}{$separator}{$item->getBasename()}", $overwrite);
        }

        return $newDir;
    }
}
