<?php
/**
 * Enlight
 *
 * LICENSE
 *
 * This source file is subject to the new BSD license that is bundled
 * with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://enlight.de/license
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@shopware.de so we can send you a copy immediately.
 *
 * @category   Enlight
 * @copyright  Copyright (c) 2011, shopware AG (http://www.shopware.de)
 * @license    http://enlight.de/license     New BSD License
 */

/**
 * Enlight auto loader class.
 *
 * The Enlight_Loader is responsible for the automatic class loading. It supports different types of
 * directory structures and is used for example for the enlight vendor library.
 *
 * @category   Enlight
 * @package    Enlight_Loader
 * @copyright  Copyright (c) 2011, shopware AG (http://www.shopware.de)
 * @license    http://enlight.de/license     New BSD License
 */
class Enlight_Loader
{
    /**
     * Constant for the default file separator.
     */
    const DEFAULT_SEPARATOR = '_\\';

    /**
     * Constant for the default file extension
     */
    const DEFAULT_EXTENSION = '.php';

    /**
     * Constant for the append parameter. Used when a namespace will be registered.
     */
    const POSITION_APPEND = 'append';

    /**
     * Constant for the prepend parameter. Used when a namespace will be registered.
     */
    const POSITION_PREPEND = 'prepend';

    /**
     * Constant for the remove parameter. Used when a namespace will be registered.
     */
    const POSITION_REMOVE = 'remove';

    /**
     * @var array Contains all registered namespaces
     */
    protected $namespaces = array();

    /**
     * Init loader method
     */
    public function __construct()
    {
        spl_autoload_register(array($this, 'autoload'), true, false);
    }

    /**
     * Loads a class by name. If the class is not already loaded the class will load
     * by the method loadFile.
     *
     * @param   string|array $class
     * @param   string $path
     * @throws Enlight_Exception
     * @return  bool
     */
    public function loadClass($class, $path = null)
    {
        if (is_array($class)) {
            return min(array_map(array($this, __METHOD__), $class));
        }
        if (!is_string($class)) {
            throw new Enlight_Exception('Class name must be a string');
        }
        $class = ltrim($class, self::DEFAULT_SEPARATOR);
        if (!$this->isLoaded($class)) {
            if ($path !== null) {
                $this->loadFile($path);
                return true;
            }
            $path = $this->getClassPath($class);
            if ($path === null) {
                return false;
            }
            $this->loadFile($path, false);
        }
        return true;
    }

    /**
     * Loads file method. If the path is not readable, the output buffering can't be started or the
     * path didn't pass the security check, the function throws an exception.
     * Otherwise the path is included and the result is returned.
     *
     * @param   string $path
     * @param   bool $check
     * @throws Enlight_Exception
     * @return  mixed
     */
    public static function loadFile($path, $check = true)
    {
        if ($check && !self::checkFile($path)) {
            throw new Enlight_Exception('Security check: Illegal character in filename');
        }
        if ($check && !self::isReadable($path)) {
            throw new Enlight_Exception('File "' . $path . '" not exists failure');
        }
        if (!ob_start()) {
            throw new Enlight_Exception('Output buffering could not be started');
        }

        $result = include $path;
        ob_end_clean();

        return $result;
    }

    /**
     * Checks if the file is readable. If the is_readable class fails the function will use
     * the stream_resolve_include_path function. In case the stream_resolve_include_path
     * doesn't exist the path is exploded if the path is given as stream schema.
     *
     * @param   string $path
     * @return  string|bool
     */
    public static function isReadable($path)
    {
        if (is_readable($path)) {
            return $path;
        }

        return stream_resolve_include_path($path);
    }

    /**
     * Realpath implementation that is 100% compatible but symlink aware
     *
     * @param string $path
     * @return string
     */
    public static function realpath($path)
    {
        // symlink are not supported on windows, fallback to inbuilt realpath
        if (DIRECTORY_SEPARATOR !== '/') {
            return realpath($path);
        }

        // No symlink
        if (realpath($path) === $path) {
            return $path;
        }

        if (!isset($path['0'])) {
            return getcwd();
        }

        // Make path absolute
        if ($path[0] !== DIRECTORY_SEPARATOR) {
            $path = getcwd().DIRECTORY_SEPARATOR.$path;
        }

        // Remove . and ..
        // See http://php.net/manual/en/function.realpath.php#84012
        $path = str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $path);
        $parts = array_filter(explode(DIRECTORY_SEPARATOR, $path), 'strlen');
        $absolutes = [];
        foreach ($parts as $part) {
            if ('.' === $part) {
                continue;
            }
            if ('..' === $part) {
                array_pop($absolutes);
            } else {
                $absolutes[] = $part;
            }
        }
        $newPath = DIRECTORY_SEPARATOR.implode(DIRECTORY_SEPARATOR, $absolutes);

        // Check if path/file exists
        if (!file_exists($newPath)) {
            return false;
        }

        return $newPath;
    }

    /**
     * Explodes the given path by the PATH_SEPARATOR constant
     *
     * @param   string $path
     * @return  array
     */
    public static function explodeIncludePath($path = null)
    {
        if (null === $path) {
            $path = get_include_path();
        }

        if (PATH_SEPARATOR == ':') {
            // On *nix systems, include_paths which include paths with a stream
            // schema cannot be safely explode'd, so we have to be a bit more
            // intelligent in the approach.
            $paths = preg_split('#:(?!//)#', $path);
        } else {
            $paths = explode(PATH_SEPARATOR, $path);
        }
        return $paths;
    }

    /**
     * Returns the path of the given class name. Iterates all namespaces
     * and checks if the namespace contains the class name.
     * After the namespace is founded the namespace is split by the separator
     * and concated with the DIRECTORY_SEPARATOR constant.
     * Last is to consider whether the path is readable.
     *
     * @param   string $class
     * @return  string|void
     */
    public function getClassPath($class)
    {
        foreach ($this->namespaces as $namespace) {
            if (strpos($class, $namespace['namespace']) !== 0) {
                continue;
            }
            $path = substr($class, strlen($namespace['namespace']) + 1);
            $path = str_replace(str_split($namespace['separator']), DIRECTORY_SEPARATOR, $path);
            $path = $namespace['path'] . $path . $namespace['extension'];
            $path = self::isReadable($path);
            if ($path !== false) {
                return $path;
            }
        }
        return null;
    }

    /**
     * Checks if the given class is loaded.
     *
     * @param   string $class
     * @return  bool
     */
    public static function isLoaded($class)
    {
        return class_exists($class, false) || interface_exists($class, false);
    }

    /**
     * This function registers a namespace. The position specifies
     * at what point you want to add the namespace in the array.
     *
     * @param   string $namespace
     * @param   string $path
     * @param   string $separator
     * @param   string $extension
     * @param   string $position
     * @return  Enlight_Loader
     */
    public function registerNamespace($namespace, $path,
                                        $separator = self::DEFAULT_SEPARATOR,
                                        $extension = self::DEFAULT_EXTENSION,
                                        $position = self::POSITION_APPEND)
    {
        $namespace = array(
            'namespace' => $namespace,
            'path' => $path,
            'separator' => $separator,
            'extension' => $extension
        );

        switch ($position) {
            case self::POSITION_APPEND:
                array_push($this->namespaces, $namespace);
                break;
            case self::POSITION_PREPEND:
                array_unshift($this->namespaces, $namespace);
                break;
            default:
                break;
        }

        return $this;
    }

    /**
     * Adds a path to the include paths array and returns the old include paths.
     * The position specifies at what point you want to add the include path in the array.
     *
     * @param   string $path
     * @param   string $position
     * @throws  Enlight_Exception
     * @return  string
     */
    public static function addIncludePath($path, $position = self::POSITION_APPEND)
    {
        if (is_array($path)) {
            return (bool) array_map(__METHOD__, $path);
        }
        if (!is_string($path) || !file_exists($path) || !is_dir($path)) {
            throw new Enlight_Exception('Path "' . $path . '" is not a dir failure');
        }

        $paths = self::explodeIncludePath();

        if (($key = array_search($path, $paths)) !== false) {
            unset($paths[$key]);
        }

        switch ($position) {
            case self::POSITION_APPEND:
                array_push($paths, $path);
                break;
            case self::POSITION_PREPEND:
                array_unshift($paths, $path);
                break;
            default:
                break;
        }

        return self::setIncludePath($paths);
    }

    /**
     * Sets the include path. If the given parameter is an array, the array elements will be imploded
     * by the PATH_SEPARATOR constant.
     *
     * @param   string|array $path
     * @throws  Enlight_Exception
     * @return  string
     */
    public static function setIncludePath($path)
    {
        if (is_array($path)) {
            $path = implode(PATH_SEPARATOR, $path);
        }

        $old = set_include_path($path);
        if ($old !== $path && (!$old || $old == get_include_path())) {
            throw new Enlight_Exception('Include path "' . $path . '" could not be set failure');
        }

        return $old;
    }

    /**
     * Callback for auto loading of classes.
     *
     * @param   string $class
     */
    public function autoload($class)
    {
        try {
            $this->loadClass($class);
        } catch (Exception $e) {
        }
    }

    /**
     * Security check file
     *
     * @param   string $path
     * @return  bool
     */
    public static function checkFile($path)
    {
        return !preg_match('/[^a-z0-9\\/\\\\_. :-]/i', $path);
    }
}
