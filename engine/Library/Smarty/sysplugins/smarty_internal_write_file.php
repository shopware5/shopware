<?php
/**
 * Shopware 5
 * Copyright (c) shopware AG
 *
 * According to our dual licensing model, this program can be used either
 * under the terms of the GNU Affero General Public License, version 3,
 * or under a proprietary license.
 *
 * The texts of the GNU Affero General Public License with an additional
 * permission and of our proprietary license can be found at and
 * in the LICENSE file you have received along with this program.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU Affero General Public License for more details.
 *
 * "Shopware" is a registered trademark of shopware AG.
 * The licensing of the program under the AGPLv3 does not imply a
 * trademark license. Therefore any rights, title and interest in
 * our trademarks remain entirely with us.
 */

/**
 * Smarty Internal Write File Class
 */
class Smarty_Internal_Write_File
{
    /**
     * Writes file in a safe way to disk
     *
     * @param string $_filepath complete filepath
     * @param string $_contents file content
     * @param Smarty $smarty    smarty instance
     *
     * @return bool true
     */
    public static function writeFile($_filepath, $_contents, Smarty $smarty)
    {
        if (empty($_contents)) {
            return false;
        }
        $_error_reporting = error_reporting();
        error_reporting($_error_reporting & ~E_NOTICE & ~E_WARNING);
        if ($smarty->_file_perms !== null) {
            $old_umask = umask(0);
        }

        $_dirpath = dirname($_filepath);
        // if subdirs, create dir structure
        if ($_dirpath !== '.' && !self::ensureDirectoryExists($_dirpath, $smarty->_dir_perms)) {
            error_reporting($_error_reporting);
            throw new SmartyException("unable to create directory {$_dirpath}");
        }

        // write to tmp file, then move to overt file lock race condition
        $_tmp_file = $_dirpath . DS . uniqid('wrt', true);
        if (file_put_contents($_tmp_file, $_contents) === false) {
            error_reporting($_error_reporting);
            umask($old_umask);
            throw new SmartyException("unable to write file {$_tmp_file}");
        }

        if ($smarty->_file_perms !== null) {
            // set file permissions
            chmod($_tmp_file, $smarty->_file_perms);
            umask($old_umask);
        }

        /*
         * Windows' rename() fails if the destination exists,
         * Linux' rename() properly handles the overwrite.
         * Simply unlink()ing a file might cause other processes
         * currently reading that file to fail, but linux' rename()
         * seems to be smart enough to handle that for us.
         */
        if (Smarty::$_IS_WINDOWS) {
            // remove original file
            @unlink($_filepath);
            // rename tmp file
            $success = @rename($_tmp_file, $_filepath);
        } else {
            // rename tmp file
            $success = @rename($_tmp_file, $_filepath);
            if (!$success) {
                // remove original file
                @unlink($_filepath);
                // rename tmp file
                $success = @rename($_tmp_file, $_filepath);
            }
        }
        if (!$success) {
            @unlink($_tmp_file);
            trigger_error("unable to write file {$_filepath}");
        }

        error_reporting($_error_reporting);

        return $success;
    }

    /**
     * Recursively creates the missing parts of a directory path in a manner that is concurrency-safe.
     *
     * @see https://bugs.php.net/bug.php?id=35326
     *
     * @param string $pathname a (nested) directory path to create
     * @param int    $mode     the permission to use
     *
     * @return bool true iff the directory path was successfully created
     */
    private static function ensureDirectoryExists($pathname, $mode)
    {
        $path_segments = explode(DIRECTORY_SEPARATOR, $pathname);

        $current_pathname = '';
        foreach ($path_segments as $path_segment) {
            $current_pathname = $current_pathname . $path_segment . DIRECTORY_SEPARATOR;
            @mkdir($current_pathname, $mode);
        }

        return is_dir($pathname);
    }
}
