<?php
/**
 * Smarty read include path plugin
 *
 * @package Smarty
 * @subpackage PluginsInternal
 * @author Monte Ohrt
 */

/**
 * Smarty Internal Read Include Path Class
 *
 * @package Smarty
 * @subpackage PluginsInternal
 */
class Smarty_Internal_Get_Include_Path {

    /**
     * Return full file path from PHP include_path
     *
     * @param string $filePath filepath
     * @return string|boolean full filepath or false
     */
    public static function getIncludePath($filePath)
    {
        static $_include_path = null;

        if (function_exists('stream_resolve_include_path')) {
            // available since PHP 5.3.2
            $filePath = strpos($filePath, '.' . DS) === 0 ? substr($filePath, 2) : $filePath;
            return stream_resolve_include_path($filePath);
        }

        if ($_include_path === null) {
            $_include_path = explode(PATH_SEPARATOR, get_include_path());
        }

        foreach ($_include_path as $_path) {
            if (file_exists($_path . DS . $filePath)) {
                return $_path . DS . $filePath;
            }
        }

        return false;
    }
}
