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

namespace Shopware\Bundle\StoreFrontBundle\Struct;

/**
 * @category  Shopware
 * @package   Shopware\Bundle\StoreFrontBundle\Struct
 * @copyright Copyright (c) shopware AG (http://www.shopware.de)
 */
class Thumbnail extends Extendable implements \JsonSerializable
{
    /**
     * @var string
     */
    protected $source;

    /**
     * @var string|null
     */
    protected $retinaSource;

    /**
     * @var int
     */
    protected $maxWidth;

    /**
     * @var int
     */
    protected $maxHeight;

    /**
     * @param string $source
     * @param string|null $retinaSource
     * @param $maxWidth
     * @param $maxHeight
     */
    public function __construct($source, $retinaSource, $maxWidth, $maxHeight)
    {
        $this->source = $source;
        $this->retinaSource = $retinaSource;
        $this->maxWidth = $maxWidth;
        $this->maxHeight = $maxHeight;
    }

    /**
     * @return string
     */
    public function getSource()
    {
        return $this->source;
    }

    /**
     * @return bool
     */
    public function hasRetinaSource()
    {
        return ($this->retinaSource != null);
    }

    /**
     * @return null|string
     */
    public function getRetinaSource()
    {
        return $this->retinaSource;
    }

    /**
     * @deprecated deprecated since version 5.1, please build the sourceSet in a hydrator or view
     *
     * @param string $imageDir
     * @return string
     */
    public function getSourceSet($imageDir)
    {
        if ($this->retinaSource !== null) {
            return sprintf('%s%s, %s%s 2x', $imageDir, $this->source, $imageDir, $this->retinaSource);
        } else {
            return $imageDir . $this->source;
        }
    }

    /**
     * @return int
     */
    public function getMaxWidth()
    {
        return $this->maxWidth;
    }

    /**
     * @return int
     */
    public function getMaxHeight()
    {
        return $this->maxHeight;
    }
}
