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

namespace Shopware\Bundle\MediaBundle;

use League\Flysystem\FilesystemInterface;

class MediaBackendFlysystemAdapter implements MediaBackendInterface
{
    /**
     * @var FilesystemInterface
     */
    private $filesystem;

    /**
     * @var array
     */
    private $config;

    /**
     * @param FilesystemInterface $filesystem
     * @param array $cdnConfig
     */
    public function __construct(FilesystemInterface $filesystem, $cdnConfig)
    {
        $this->filesystem = $filesystem;
        $this->config = $cdnConfig;
    }

    /**
     * @inheritdoc
     */
    public function getAdapterType()
    {
        return $this->config['type'];
    }

    /**
     * @inheritdoc
     */
    public function toUrlPath($path)
    {
        return $this->getRealPath($path);
    }

    /**
     * Builds the path on the filesystem
     *
     * @param string $path
     * @param bool $skipMigration
     * @return string
     */
    private function getRealPath($path, $skipMigration = false)
    {
        if ($this->isRealPathFormat($path)) {
            return $path;
        } else {
            if (!$skipMigration) {
                if ($this->filesystem->has($path)) {
                    $realPath = $this->getRealPath($path, true);
                    if (!$this->filesystem->has($realPath)) {
                        $this->filesystem->rename($path, $realPath);
                    }
                }
            }
        }

        $path = ltrim($path, "/");
        $pathElements = explode("/", $path);
        $pathInfo = pathinfo($path);
        $md5hash = md5($path);

        $realPath = array_slice(str_split($md5hash, 2), 0, 3);
        $realPath = $pathElements[0] . "/" . $pathElements[1] . "/" . join("/", $realPath) . "/" . $pathInfo['basename'];

        return $realPath;
    }

    /**
     * @inheritdoc
     */
    public function isRealPathFormat($path)
    {
        return preg_match("/.*(media\/(?:archive|image|music|pdf|temp|unknown|video)(?:\/thumbnail)?\/(?:([0-9a-f]{2}\/[0-9a-f]{2}\/[0-9a-f]{2}\/)).*)/", $path);
    }

    /**
     * @inheritdoc
     */
    public function has($path)
    {
        $path = $this->getRealPath($path);

        return $this->filesystem->has($path);
    }

    /**
     * @inheritdoc
     */
    public function read($path)
    {
        $path = $this->getRealPath($path);

        return $this->filesystem->read($path);
    }

    /**
     * @inheritdoc
     */
    public function listContents($directory = '', $recursive = false)
    {
        return $this->filesystem->listContents($directory, $recursive);
    }

    /**
     * @inheritdoc
     */
    public function write($path, $contents)
    {
        $path = $this->getRealPath($path);
        if ($this->filesystem->has($path)) {
            $this->filesystem->delete($path);
        }

        return $this->filesystem->put($path, $contents);
    }

    /**
     * @inheritdoc
     */
    public function rename($path, $newpath)
    {
        $path = $this->getRealPath($path);
        $newpath = $this->getRealPath($newpath);

        if ($this->filesystem->has($path)) {
            return $this->filesystem->rename($path, $newpath);
        }

        return false;
    }

    /**
     * @inheritdoc
     */
    public function copy($path, $newpath)
    {
        $path = $this->getRealPath($path);

        return $this->filesystem->copy($path, $newpath);
    }

    /**
     * @inheritdoc
     */
    public function delete($path)
    {
        $path = $this->getRealPath($path);

        return $this->filesystem->delete($path);
    }

    /**
     * @inheritdoc
     */
    public function getMediaUrl()
    {
        return $this->config['mediaUrl'];
    }

    /**
     * @inheritdoc
     */
    public function createDir($dirname)
    {
        return $this->filesystem->createDir($dirname);
    }

    /**
     * @inheritdoc
     */
    public function getSize($path)
    {
        $path = $this->getRealPath($path);

        return $this->filesystem->getSize($path);
    }
}
