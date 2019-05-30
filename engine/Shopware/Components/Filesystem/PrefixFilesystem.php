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

namespace Shopware\Components\Filesystem;

use League\Flysystem\FilesystemInterface;
use League\Flysystem\Handler;
use League\Flysystem\PluginInterface;

class PrefixFilesystem implements FilesystemInterface
{
    /**
     * @var FilesystemInterface
     */
    private $filesystem;

    /**
     * @var string
     */
    private $prefix;

    /**
     * @param string $prefix
     */
    public function __construct(FilesystemInterface $filesystem, $prefix)
    {
        if (empty($prefix)) {
            throw new \InvalidArgumentException('The prefix must not be empty.');
        }

        $this->filesystem = $filesystem;
        $this->prefix = $this->normalizePrefix($prefix);
    }

    /**
     * {@inheritdoc}
     */
    public function has($path)
    {
        $this->checkPath($path);

        return $this->filesystem->has($this->prefix . $path);
    }

    /**
     * {@inheritdoc}
     */
    public function read($path)
    {
        $this->checkPath($path);

        return $this->filesystem->read($this->prefix . $path);
    }

    /**
     * {@inheritdoc}
     */
    public function readStream($path)
    {
        $this->checkPath($path);

        return $this->filesystem->readStream($this->prefix . $path);
    }

    /**
     * {@inheritdoc}
     */
    public function listContents($directory = '', $recursive = false)
    {
        $this->checkPath($directory);

        return array_map(
            function ($info) {
                $info['dirname'] = $this->stripPrefix($info['dirname']);
                $info['path'] = $this->stripPrefix($info['path']);

                return $info;
            },
            $this->filesystem->listContents($this->prefix . $directory, $recursive)
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getMetadata($path)
    {
        $this->checkPath($path);

        $meta = $this->filesystem->getMetadata($this->prefix . $path);
        $meta['path'] = $this->stripPrefix($meta['path']);
        $meta['dirname'] = $this->stripPrefix($meta['dirname']);

        return $meta;
    }

    /**
     * {@inheritdoc}
     */
    public function getSize($path)
    {
        $this->checkPath($path);

        return $this->filesystem->getSize($this->prefix . $path);
    }

    /**
     * {@inheritdoc}
     */
    public function getMimetype($path)
    {
        $this->checkPath($path);

        return $this->filesystem->getMimetype($this->prefix . $path);
    }

    /**
     * {@inheritdoc}
     */
    public function getTimestamp($path)
    {
        $this->checkPath($path);

        return $this->filesystem->getTimestamp($this->prefix . $path);
    }

    /**
     * {@inheritdoc}
     */
    public function getVisibility($path)
    {
        $this->checkPath($path);

        return $this->filesystem->getVisibility($this->prefix . $path);
    }

    /**
     * {@inheritdoc}
     */
    public function write($path, $contents, array $config = [])
    {
        $this->checkPath($path);

        return $this->filesystem->write($this->prefix . $path, $contents, $config);
    }

    /**
     * {@inheritdoc}
     */
    public function writeStream($path, $resource, array $config = [])
    {
        $this->checkPath($path);

        return $this->filesystem->writeStream($this->prefix . $path, $resource, $config);
    }

    /**
     * {@inheritdoc}
     */
    public function update($path, $contents, array $config = [])
    {
        $this->checkPath($path);

        return $this->filesystem->update($this->prefix . $path, $contents, $config);
    }

    /**
     * {@inheritdoc}
     */
    public function updateStream($path, $resource, array $config = [])
    {
        $this->checkPath($path);

        return $this->filesystem->updateStream($this->prefix . $path, $resource, $config);
    }

    /**
     * {@inheritdoc}
     */
    public function rename($path, $newpath)
    {
        $this->checkPath($path);
        $this->checkPath($newpath);

        return $this->filesystem->rename($this->prefix . $path, $this->prefix . $newpath);
    }

    /**
     * {@inheritdoc}
     */
    public function copy($path, $newpath)
    {
        $this->checkPath($path);
        $this->checkPath($newpath);

        return $this->filesystem->copy($this->prefix . $path, $this->prefix . $newpath);
    }

    /**
     * {@inheritdoc}
     */
    public function delete($path)
    {
        $this->checkPath($path);

        return $this->filesystem->delete($this->prefix . $path);
    }

    /**
     * {@inheritdoc}
     */
    public function deleteDir($dirname)
    {
        $this->checkPath($dirname);

        return $this->filesystem->deleteDir($this->prefix . $dirname);
    }

    /**
     * {@inheritdoc}
     */
    public function createDir($dirname, array $config = [])
    {
        $this->checkPath($dirname);

        return $this->filesystem->createDir($this->prefix . $dirname, $config);
    }

    /**
     * {@inheritdoc}
     */
    public function setVisibility($path, $visibility)
    {
        $this->checkPath($path);

        return $this->filesystem->setVisibility($this->prefix . $path, $visibility);
    }

    /**
     * {@inheritdoc}
     */
    public function put($path, $contents, array $config = [])
    {
        $this->checkPath($path);

        return $this->filesystem->put($this->prefix . $path, $contents, $config);
    }

    /**
     * {@inheritdoc}
     */
    public function putStream($path, $resource, array $config = [])
    {
        $this->checkPath($path);

        return $this->filesystem->putStream($this->prefix . $path, $resource, $config);
    }

    /**
     * {@inheritdoc}
     */
    public function readAndDelete($path)
    {
        $this->checkPath($path);

        return $this->filesystem->readAndDelete($this->prefix . $path);
    }

    /**
     * {@inheritdoc}
     */
    public function get($path, Handler $handler = null)
    {
        $this->checkPath($path);

        return $this->filesystem->get($this->prefix . $path, $handler);
    }

    /**
     * {@inheritdoc}
     */
    public function addPlugin(PluginInterface $plugin)
    {
        throw new \RuntimeException('Filesystem plugins are not allowed in prefixed filesystems.');
    }

    /**
     * @param string $path
     *
     * @return string
     */
    public function stripPrefix($path)
    {
        $prefix = rtrim($this->prefix, '/');
        $path = preg_replace('#^' . $prefix . '#', '', $path);
        $path = ltrim($path, '/');

        return $path;
    }

    /**
     * @param string $prefix
     *
     * @return string
     */
    private function normalizePrefix($prefix)
    {
        return trim($prefix, '/') . '/';
    }

    /**
     * @param string $path
     *
     * @throws \RuntimeException
     */
    private function checkPath($path)
    {
        if (!preg_match('#\.\./|\\0#msi', $path)) {
            return;
        }

        throw new \RuntimeException('Path traversal is not allowed.');
    }
}
