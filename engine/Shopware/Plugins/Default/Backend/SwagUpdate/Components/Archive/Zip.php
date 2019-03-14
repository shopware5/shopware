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

namespace ShopwarePlugins\SwagUpdate\Components\Archive;

use ZipArchive;

class Zip extends Adapter
{
    /**
     * @var ZipArchive
     */
    protected $stream;

    /**
     * @param string $fileName
     *
     * @throws \Exception
     */
    public function __construct($fileName = null, $flags = null)
    {
        if (!extension_loaded('zip')) {
            throw new \Exception('The PHP extension "zip" is not loaded.');
        }

        $this->stream = new ZipArchive();

        if ($fileName != null) {
            if (($retval = $this->stream->open($fileName, $flags)) !== true) {
                throw new \RuntimeException($this->getErrorMessage($retval, $fileName), $retval);
            }
            $this->position = 0;
            $this->count = $this->stream->numFiles;
        }
    }

    /**
     * @return Entry\Zip
     */
    public function current()
    {
        return new Entry\Zip($this->stream, $this->position);
    }

    /**
     * @param string $name
     *
     * @return resource
     */
    public function getStream($name)
    {
        return $this->stream->getStream($name);
    }

    /**
     * @param string $name
     */
    public function getContents($name)
    {
        return $this->stream->getFromName($name);
    }

    /**
     * @param int $position
     */
    public function getEntry($position)
    {
        return $this->stream->statIndex($position);
    }

    /**
     * @return bool
     */
    public function close()
    {
        return $this->stream->close();
    }

    /**
     * Give a meaningful error message to the user.
     *
     * @param int    $retval
     * @param string $file
     *
     * @return string
     */
    protected function getErrorMessage($retval, $file)
    {
        switch ($retval) {
            case ZipArchive::ER_EXISTS:
                return sprintf("File '%s' already exists.", $file);
            case ZipArchive::ER_INCONS:
                return sprintf("Zip archive '%s' is inconsistent.", $file);
            case ZipArchive::ER_INVAL:
                return sprintf('Invalid argument (%s)', $file);
            case ZipArchive::ER_MEMORY:
                return sprintf('Malloc failure (%s)', $file);
            case ZipArchive::ER_NOENT:
                return sprintf("No such zip file: '%s'", $file);
            case ZipArchive::ER_NOZIP:
                return sprintf("'%s' is not a zip archive.", $file);
            case ZipArchive::ER_OPEN:
                return sprintf("Can't open zip file: %s", $file);
            case ZipArchive::ER_READ:
                return sprintf('Zip read error (%s)', $file);
            case ZipArchive::ER_SEEK:
                return sprintf('Zip seek error (%s)', $file);
            default:
                return sprintf("'%s' is not a valid zip archive, got error code: %s", $file, $retval);
        }
    }
}
