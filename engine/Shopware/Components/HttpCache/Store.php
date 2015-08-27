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

namespace Shopware\Components\HttpCache;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\HttpCache\Store as BaseStore;

/**
 * <code>
 * $httpCacheStore = new Shopware\Components\HttpCache\Store($root);
 * $httpCacheStore->purgeByHeader($name);
 * </code>
 *
 * @category  Shopware
 * @package   Shopware\Components\HttpCache
 * @copyright Copyright (c) shopware 11AG (http://www.shopware.de)
 */
class Store extends BaseStore
{
    /**
     * @var string[]
     */
    private $cacheCookies;

    /**
     * @param string $root
     * @param string[] $cacheCookies
     */
    public function __construct($root, array $cacheCookies)
    {
        $this->cacheCookies = $cacheCookies;

        parent::__construct($root);
    }

    /**
     * Generate custom cache key including
     * additional state from cookie and headers.
     *
     * {@inheritdoc}
     */
    protected function generateCacheKey(Request $request)
    {
        $uri = $request->getUri();

        foreach ($this->cacheCookies as $cookieName) {
            if ($request->cookies->has($cookieName)) {
                $uri .= '&__'. $cookieName . '=' . $request->cookies->get($cookieName);
            }
        }

        return 'md'.hash('sha256', $uri);
    }

    /**
     * Purges data for the given Header.
     *
     * @return bool
     */
    public function purgeAll()
    {
        if (!file_exists($this->root)) {
            return false;
        }

        $result = false;

        /** @var $file \SplFileInfo */
        foreach ($this->createRecursiveFileIterator($this->root) as $file) {
            if (!$file->isFile()) {
                continue;
            }

            // skip .gitkeep
            if ($file->getFilename() === '.gitkeep') {
                continue;
            }

            unlink($file->getPathname());
            $result = true;
        }

        return $result;
    }

    /**
     * Purges data for the given Header.
     *
     * @param  string $name
     * @param  string|null $value
     * @return bool
     */
    public function purgeByHeader($name, $value = null)
    {
        $headerDir = $this->root . DIRECTORY_SEPARATOR . 'md';

        if (!file_exists($headerDir)) {
            return false;
        }

        if (isset($value)) {
            $value = ';' . $value . ';';
        }

        $result = false;

        /** @var $headerFile \SplFileInfo */
        foreach ($this->createRecursiveFileIterator($headerDir) as $headerFile) {
            if (!$headerFile->isFile()) {
                continue;
            }

            $headerData = file_get_contents($headerFile->getPathname());
            $headerData = unserialize($headerData);

            $changed = false;
            foreach ($headerData as $headerIndex => $header) {
                if (!isset($header[1][$name])) {
                    continue;
                }

                $headerValue = implode(';', $header[1][$name]);

                if (isset($value) && strpos($headerValue, $value) === false) {
                    continue;
                }
                $cacheKey = $header[1]['x-content-digest'][0];
                if (file_exists($path = $this->getPath($cacheKey))) {
                    unlink($path);
                }
                $changed = true;
                unset($headerData[$headerIndex]);
            }

            if ($changed) {
                $result = true;
                if (empty($headerData)) {
                    unlink($headerFile->getPathname());
                } else {
                    $headerData = serialize($headerData);
                    file_put_contents($headerFile->getPathname(), $headerData);
                }
            }
        }

        return $result;
    }

    /**
     * @param string $path The path of the directory to be iterated over.
     * @return \RecursiveIteratorIterator
     */
    private function createRecursiveFileIterator($path)
    {
        $directoryIterator = new \RecursiveDirectoryIterator(
            $path,
            \RecursiveDirectoryIterator::SKIP_DOTS
        );

        return new \RecursiveIteratorIterator(
            $directoryIterator,
            \RecursiveIteratorIterator::LEAVES_ONLY
        );
    }
}
