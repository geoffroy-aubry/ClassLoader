<?php

/**
 * Just a simple autoloader implementation that implements the technical interoperability
 * standards for PHP 5.3 namespaces and class names (PSR-0).
 *
 * Based on https://gist.github.com/221634.
 *
 * Usage:
 *     ClassLoader::register(<namespace>|'', <include-path>, [<file-extension>]);
 *
 * Example:
 *     Suppose you have Project\Core and Project\Apps namespaces based on /myproject/core and /myproject/apps respectively,
 *     and external lib's classes without namespaces in /myproject/lib. Then:
 *
 *     ClassLoader::register('Project\Core', '/myproject/core');
 *     ClassLoader::register('Project\Apps', '/myproject/apps');
 *     ClassLoader::register('', '/myproject/lib');
 *
 * @author Geoffroy Aubry <geoffroy.aubry@free.fr>
 * @see https://gist.github.com/221634
 * @see https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-0.md
 */
class ClassLoader
{
    private $_sFileExtension;
    private $_sNamespace;
    private $_sIncludePath;

    /**
     * Create a class loader where the given namespace's base directory maps to on the file system. 
     * Set $sNamespace to '' to register libraries without namespaces and using '_' as directory separator.
     * 
     * @param string $sNamespace namespace's base directory, or empty string for libraries with pseudo-namespaces
     * @param string $sIncludePath location of the namespace
     * @param string $sFileExtension optional file extension, default '.php'
     */
    public function __construct($sNamespace, $sIncludePath, $sFileExtension='.php')
    {
        $this->_sNamespace = $sNamespace;
        $this->_sIncludePath = $sIncludePath;
        $this->_sFileExtension = $sFileExtension;
    }

    /**
     * Add a class loader on the SPL autoload stack where the given namespace's base directory maps to 
     * on the file system. 
     * Set $sNamespace to '' to register libraries without namespaces and using '_' as directory separator.
     * 
     * @param string $sNamespace namespace's base directory, or empty string for libraries with pseudo-namespaces
     * @param string $sIncludePath location of the namespace
     * @param string $sFileExtension optional file extension, default '.php'
     */
    public static function register($sNamespace, $sIncludePath, $sFileExtension='.php')
    {
        spl_autoload_register(array(new self($sNamespace, $sIncludePath, $sFileExtension), 'loadClass'));
    }

    /**
     * Loads the given class or interface.
     *
     * @param string $sClassName The name of the class to load.
     * @return bool true on success
     */
    public function loadClass($sClassName)
    {
        if (
                $this->_sNamespace === ''
                || $this->_sNamespace.'\\' === substr($sClassName, 0, strlen($this->_sNamespace.'\\'))
        ) {

            if ($this->_sNamespace !== '' && $this->_sIncludePath !== '') {
                $sClassName = substr($sClassName, strlen($this->_sNamespace.'\\'));
            }

            $fileName = '';
            $namespace = '';
            $lastNsPos = strripos($sClassName, '\\');
            if ($lastNsPos !== false) {
                $namespace = substr($sClassName, 0, $lastNsPos);
                $sClassName = substr($sClassName, $lastNsPos + 1);
                $fileName .= str_replace('\\', DIRECTORY_SEPARATOR, $namespace) . DIRECTORY_SEPARATOR;
            }
            $fileName .= str_replace('_', DIRECTORY_SEPARATOR, $sClassName) . $this->_sFileExtension;
            if ($this->_sIncludePath !== '') {
                $fileName = $this->_sIncludePath . DIRECTORY_SEPARATOR . $fileName;
            }

            // PHP 5.3:
            if (function_exists('stream_resolve_include_path')) {
                $filePath = stream_resolve_include_path($fileName);
                if ($filePath !== false) {
                    require $filePath;
                }
                return ($filePath !== false);

            // PHP <5.3:
            } else {
                foreach (explode(PATH_SEPARATOR, get_include_path()) as $path) {
                    if ($path == '.') {
                        if (is_readable($fileName)) {
                            require $fileName;
                            return true;
                        }
                        continue;
                    }
                    $file = $path . '/' . $fileName;
                    if (is_readable($file)) {
                        require $fileName;
                        return true;
                    }
                }
                return false;
            }

        } else {
            return false;
        }
    }
}

