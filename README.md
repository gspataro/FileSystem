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

```
