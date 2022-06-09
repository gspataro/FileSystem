<?php

namespace GSpataro\FileSystem;

use SplFileInfo;

class File extends SplFileInfo
{
    /**
     * Initialize file object
     *
     * @param string $path
     */

    public function __construct(string $path)
    {
        // If the resource exists and is not a file throw an exception
        // If the resource doesn't exists the class can handle the creation process

        if (file_exists($path) && !is_file($path)) {
            throw new Exception\PathIsNotFileException(
                "The resource located at '{$path}' that you are trying to open is not a file."
            );
        }

        parent::__construct($path);
    }

    /**
     * Verify if the file exists
     *
     * @return bool
     */

    public function exists(): bool
    {
        return $this->isFile();
    }

    /**
     * Verify if the file exists and, if not, throw an exception
     *
     * @throws Exception\FileNotFoundException
     * @return void
     */

    public function existsOrDie(): void
    {
        if (!$this->exists()) {
            throw new Exception\FileNotFoundException(
                "File located at '{$this->getPathname()}' not found."
            );
        }
    }

    /**
     * Write the file to the filesystem
     *
     * @param mixed $content
     * @param bool $overwrite
     * @return int|false
     */

    public function write(mixed $content, bool $overwrite = true): int|false
    {
        if ($this->exists() && !$this->isWritable()) {
            throw new Exception\FilePermissionsException(
                "Cannot write to file located at '{$this->getPathname()}'. Check file permissions."
            );
        }

        return file_put_contents($this->getPathname(), $content, $overwrite ?: FILE_APPEND);
    }

    /**
     * Read the file from the filesystem
     *
     * @return string|false
     */

    public function read(): string|false
    {
        $this->existsOrDie();

        if (!$this->isReadable()) {
            throw new Exception\FilePermissionsException(
                "Cannot read file located at '{$this->getPathname()}'. Check file permissions."
            );
        }

        return file_get_contents($this->getPathname());
    }

    /**
     * Verify if a file matches one of given extensions
     *
     * @param string|array $extensions
     * @return bool
     */

    public function matchExtensions(string|array $extensions): bool
    {
        return is_array($extensions)
            ? in_array($this->getExtension(), $extensions)
            : $this->getExtension() == $extensions;
    }

    /**
     * Verify if a file matches one of given extensions or throw an exception
     *
     * @param string|array $extensions
     * @throws Exception\FileExtensionNotAllowedException
     * @return void
     */

    public function matchExtensionsOrDie(string|array $extensions): void
    {
        if (!$this->matchExtensions($extensions)) {
            $extensionsList = is_array($extensions) ? implode(", ", $extensions) : $extensions;

            throw new Exception\FileExtensionNotAllowedException(
                "Extension not allowed for file located at '{$this->getPathname()}'. Only '{$extensionsList}' accepted."
            );
        }
    }

    /**
     * Import a PHP script
     *
     * @return mixed
     */

    public function import(): mixed
    {
        $this->existsOrDie();
        $this->matchExtensionsOrDie("php");
        return require_once($this->getPathname());
    }

    /**
     * Delete a file from the filesystem
     *
     * @return void
     */

    public function delete(): void
    {
        if ($this->exists()) {
            unlink($this->getPathname());
        }
    }

    /**
     * Move a file to another location and returns an object for the new file
     *
     * @param string $newPath
     * @param bool $overwrite
     * @return File
     */

    public function move(string $newPath, bool $overwrite = false): File
    {
        $this->existsOrDie();
        $newFile = new File($newPath);

        if (!$overwrite && $newFile->exists()) {
            throw new Exception\FileFoundException(
                "Cannot move '{$this->getPathname()}' to '{$newPath}'. Set Argument #2 to true to overwrite it."
            );
        } elseif ($overwrite) {
            $newFile->delete();
        }

        rename($this->getPathname(), $newPath);

        return $newFile;
    }

    /**
     * Copy a file and returns an object for the new file
     *
     * @param string $newPath
     * @param bool $overwrite
     * @return File
     */

    public function copy(string $newPath, bool $overwrite = false): File
    {
        $this->existsOrDie();
        $newFile = new File($newPath);

        if (!$overwrite && $newFile->exists()) {
            throw new Exception\FileFoundException(
                "Cannot copy '{$this->getPathname()}' to '{$newPath}'. Set Argument #2 to true to overwrite it."
            );
        } elseif ($overwrite) {
            $newFile->delete();
        }

        copy($this->getPathname(), $newPath);

        return $newFile;
    }
}
