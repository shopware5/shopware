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

class PlainStrategy implements StrategyInterface
{
    /**
     * {@inheritdoc}
     */
    public function normalize($path)
    {
        // remove filesystem directories
        $path = str_replace('//', '/', $path);

        // remove everything before /media/...
        preg_match('/.*((media\/(?:archive|image|music|pdf|temp|unknown|video|vector)(?:\/thumbnail)?).*\/((.+)\.(.+)))/', $path, $matches);

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
        $path = $this->normalize($path);
        $path = ltrim($path, '/');
        $pathInfo = pathinfo($path);

        if (empty($pathInfo['extension'])) {
            return '';
        }

        preg_match('/.*((media\/(?:archive|image|model|music|pdf|temp|unknown|video|vector)(?:\/thumbnail)?).*\/((.+)\.(.+)))/', $path, $matches);

        if (!empty($matches)) {
            $path = $matches[2] . '/' . $matches[3];
            if (preg_match('/.*(_[\d]+x[\d]+(@2x)?).(?:.*)$/', $path) && strpos($matches[2], '/thumbnail') === false) {
                $path = $matches[2] . '/thumbnail/' . $matches[3];
            }

            return $path;
        }

        return $path;
    }

    /**
     * {@inheritdoc}
     */
    public function isEncoded($path)
    {
        return (bool) preg_match('/.*((media\/(?:archive|image|model|music|pdf|temp|unknown|video|vector)(?:\/thumbnail)?).*\/((.+)\.(.+)))/', $path);
    }
}
