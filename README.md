# GSpataro/FileSystem

A component to easily manage the files and directories of your application.

---

## Installation

> **Requires [PHP 8.0+](https://www.php.net/releases/)**

Require the component using [Composer](https://getcomposer.org)

```
composer require gspataro/filesystem
```

---

## Quick start

To start using the FileSystem component you only need to initialize the Storage class. This will give you easy access to the files and directories present on your application's filesystem.

```php
<?php

use GSpataro\FileSystem\Storage;

/**
 * The storage class gives you easy access to files and directories in your root folder
 * 
 * @param string $root The path of your root
 */

$storage = new Storage(__DIR__);

/**
 * Open a file
 * 
 * @param string $path The path to the file (exluding root)
 */

$storage->openFile("document.txt"); // Will open: __DIR__ . '/document.txt'

/**
 * Open a directory
 * 
 * @param string $path The path to the directory (exluding root)
 */

$storage->openDir("directory"); // Will open: __DIR__ . '/directory'
```

---

## File and Directory classes

You can also instanciate File and Directory classes outside of the storage class. This will give you more flexibility in some cases. Both classes have some methods in common:

```php
<?php

use GSpataro\FileSystem\File;
use GSpataro\FileSystem\Directory;

$file = new File(__DIR__ . '/document.txt');
$directory = new Directory(__DIR__ . '/document');

/**
 * Verify if the file/directory exists
 */

$file->exists();
$directory->exists();

// This variant throws an exeption if the file/directory does not exist

$file->existsOrDie();
$directory->existsOrDie();

/**
 * Write or create a file
 * 
 * @param mixed $content          The content to write
 * @param bool  $overwrite = true If true, overwrite the existing content, otherwise add the new content at the end of the file
 */

$file->write("Lorem ipsum");

/**
 * Create a directory
 * 
 * @param bool $recursive = false  If true, create all the non existing directories present in the path
 * @param bool $permissions = 0777 Define the permissions of the directory
 */

$directory->write();

/**
 * Read a file/directory
 * 
 * File:      return the content of the file
 * Directory: return the content of the directory as File/Directory objects contained in an array
 */

$file->read();
$directory->read();

/**
 * Delete a file
 */

$file->delete();

/**
 * Delete a directory
 * 
 * @param bool $recursive = false If true, delete the directory and its content, otherwise delete the directory only if empty
 */

$directory->delete(true);

/**
 * Move/copy a file/directory
 * 
 * Directory: this will also move/copy the content of the directory
 * @param bool $overwrite = false If true, overwrite the new path
 */

$file->move(__DIR__ . '/moved_document.txt');
$file->copy(__DIR__ . '/copied_documen.txt');

$directory->move(__DIR__ . '/moved_directory');
$directory->copy(__DIR__ . '/copied_directory');
```

### Directory only methods

```php
<?php

/**
 * Verify if the directory is empty
 * 
 * @return bool
 */

$directory->empty();
```

### File only methods

```php
<?php

/**
 * Verify if the file matches one or more extensions
 * 
 * @param string|array $extensions
 * @return bool
 */

$file->matchExtensions("txt");

// This variant throws an exception if the file doesn't match the given extension/s

$file->matchExcentionsOrDie(["txt", "md"]);

/**
 * Require PHP file
 * 
 * @return mixed
 */

$file->import();
```
