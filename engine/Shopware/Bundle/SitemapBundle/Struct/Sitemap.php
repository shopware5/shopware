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

namespace Shopware\Bundle\SitemapBundle\Struct;

class Sitemap
{
    /**
     * @var string
     */
    private $filename;

    /**
     * @var \DateTimeInterface
     */
    private $created;

    /**
     * @var int
     */
    private $urlCount;

    /**
     * @param string $filename
     * @param int    $urlCount
     */
    public function __construct($filename, $urlCount, \DateTimeInterface $created = null)
    {
        $this->filename = $filename;
        $this->created = $created ?: new \DateTime('NOW', new \DateTimeZone('UTC'));
        $this->urlCount = $urlCount;
    }

    /**
     * @return string
     */
    public function getFilename()
    {
        return $this->filename;
    }

    /**
     * @param string $filename
     */
    public function setFilename($filename)
    {
        $this->filename = $filename;
    }

    /**
     * @return int
     */
    public function getUrlCount()
    {
        return $this->urlCount;
    }

    /**
     * @param int $urlCount
     */
    public function setUrlCount($urlCount)
    {
        $this->urlCount = $urlCount;
    }

    /**
     * @return \DateTimeInterface
     */
    public function getCreated()
    {
        return $this->created;
    }

    /**
     * @param \DateTimeInterface $created
     */
    public function setCreated($created)
    {
        $this->created = $created;
    }
}
