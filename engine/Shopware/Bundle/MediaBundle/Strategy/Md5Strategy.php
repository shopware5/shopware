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

namespace Shopware\Bundle\MediaBundle\Strategy;

class Md5Strategy implements StrategyInterface
{
    /**
     * @var array
     */
    private $blacklist = [
        '/ad/' => '/g0/',
    ];

    /**
     * {@inheritdoc}
     */
    public function normalize($path)
    {
        // remove filesystem directories
        $path = str_replace('//', '/', $path);

        // remove everything before /media/...
        preg_match("/.*((media\/(?:archive|image|model|music|pdf|temp|unknown|video|vector)(?:\/thumbnail)?).*\/((.+)\.(.+)))/", $path, $matches);

        if (!empty($matches)) {
            return $matches[2] . '/' . $matches[3];
        }

        return $path;
    }

    /**
     * {@inheritdoc}
     */
    public function encode($path)
    {
        if (!$path || $this->isEncoded($path)) {
            return $this->substringPath($path);
        }

        $path = $this->normalize($path);

        $path = ltrim($path, '/');
        $pathElements = explode('/', $path);
        $pathInfo = pathinfo($path);
        $md5hash = md5($path);

        if (empty($pathInfo['extension'])) {
            return '';
        }

        $realPath = array_slice(str_split($md5hash, 2), 0, 3);
        $realPath = $pathElements[0] . '/' . $pathElements[1] . '/' . implode('/', $realPath) . '/' . $pathInfo['basename'];

        if (!$this->hasBlacklistParts($realPath)) {
            return $realPath;
        }

        foreach ($this->blacklist as $key => $value) {
            // must be called 2 times, because the second level won't be matched in the first call
            $realPath = str_replace($key, $value, $realPath);
            $realPath = str_replace($key, $value, $realPath);
        }

        return $realPath;
    }

    /**
     * {@inheritdoc}
     */
    public function isEncoded($path)
    {
        if ($this->hasBlacklistParts($path)) {
            return false;
        }

        return (bool) preg_match("/.*(media\/(?:archive|image|model|music|pdf|temp|unknown|video|vector)(?:\/thumbnail)?\/(?:([0-9a-g]{2}\/[0-9a-g]{2}\/[0-9a-g]{2}\/))((.+)\.(.+)))/", $path);
    }

    /**
     * @param string $path
     *
     * @return bool
     */
    private function hasBlacklistParts($path)
    {
        foreach ($this->blacklist as $key => $value) {
            if (strpos($path, $key) !== false) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param string $path
     *
     * @return string|null
     */
    private function substringPath($path)
    {
        preg_match("/(media\/(?:archive|image|model|music|pdf|temp|unknown|video|vector)(?:\/thumbnail)?\/.*)/", $path, $matches);

        return empty($matches) ? null : $matches[0];
    }
}
