<?php

namespace GSpataro\FileSystem;

final class Storage
{
    /**
     * Initialize storage object
     *
     * @param string $rootDir
     */

    public function __construct(
        private string $rootDir
    ) {
        if (!file_exists($rootDir)) {
            throw new Exception\DirectoryNotFoundException(
                "Root directory not found: '{$rootDir}'."
            );
        }
    }

    /**
     * Open a directory
     *
     * @param string $path
     * @return Directory
     */

    public function openDir(string $path): Directory
    {
        return new Directory("{$this->rootDir}/{$path}");
    }

    /**
     * Open a file
     *
     * @param string $path
     * @return Directory
     */

    public function openFile(string $path): File
    {
        return new File("{$this->rootDir}/{$path}");
    }
}
