<?php

namespace GSpataro\FileSystem;

final class Storage
{
    /**
     * Store aliases
     *
     * @var array
     */

    private array $aliases = [];

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

        if (!str_ends_with($this->rootDir, '/')) {
            $this->rootDir .= '/';
        }
    }

    /**
     * Add a directory alias
     *
     * @param string $alias
     * @param string $path
     * @return void
     */

    public function addAlias(string $alias, string $path): void
    {
        $prefix = !str_starts_with($path, $this->rootDir) ? $this->rootDir : '';
        $suffix = !str_ends_with($path, '/') ? '/' : '';

        $this->aliases['{' . $alias . '}'] = $prefix . $path . $suffix;
    }

    /**
     * Prepare path
     *
     * @param string $path
     * @return string
     */

    private function preparePath(string $path): string
    {
        $path = strtr($path, $this->aliases);
        $prefix = !str_starts_with($path, $this->rootDir) ? $this->rootDir : '';

        return $prefix . $path;
    }

    /**
     * Open a directory
     *
     * @param string $path
     * @return Directory
     */

    public function openDir(string $path): Directory
    {
        return new Directory($this->preparePath($path));
    }

    /**
     * Open a file
     *
     * @param string $path
     * @return File
     */

    public function openFile(string $path): File
    {
        return new File($this->preparePath($path));
    }
}
