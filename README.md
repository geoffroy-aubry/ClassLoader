# PHP Class Loader

## Description
Just a simple autoloader implementation that implements the technical interoperability standards for PHP 5.3 namespaces and class names ([PSR-0](https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-0.md)).
So `ClassLoader` loads your project classes automatically if they follow some standard PHP conventions, with or without namespaces.
Based on https://gist.github.com/221634.

## Requirements
This class needs PHP 5 or later.

## Usage
```php
ClassLoader::register(<namespace>|'', <include-path>, [<file-extension>]);
```

### Example
Suppose you have `Project\Core` and `Project\Apps` namespaces based on `/myproject/core` and `/myproject/apps` respectively, and external lib's classes without namespaces in `/myproject/lib`. Then:
```php
require_once '/path/to/ClassLoader.php'
ClassLoader::register('Project\Core', '/myproject/core');
ClassLoader::register('Project\Apps', '/myproject/apps');
ClassLoader::register('', '/myproject/lib');
```

## Note
For a more sophisticated autoloader, see [Symfony ClassLoader Component](https://github.com/symfony/ClassLoader).
