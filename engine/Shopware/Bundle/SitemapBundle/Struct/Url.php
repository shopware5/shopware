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

class Url
{
    /**
     * The Url
     *
     * @var string
     */
    private $loc;

    /**
     * Date and time of last modification
     *
     * @var \DateTimeInterface
     */
    private $lastmod;

    /**
     * Frequency of changing
     *
     * @var string
     */
    private $changefreq;

    /**
     * Relative priority for this URL
     *
     * @var float
     */
    private $priority;

    /**
     * @var string
     */
    private $resource;

    /**
     * @var int
     */
    private $identifier;

    /**
     * @param string             $loc
     * @param \DateTimeInterface $lastmod
     * @param string             $changefreq
     * @param string             $resource
     * @param int|null           $identifier
     * @param float              $priority
     */
    public function __construct($loc, $lastmod, $changefreq, $resource, $identifier, $priority = 0.5)
    {
        $this->loc = $loc;
        $this->lastmod = $lastmod;
        $this->changefreq = $changefreq;
        $this->priority = $priority;
        $this->resource = $resource;
        $this->identifier = (int) $identifier;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return \sprintf('<url><loc>%s</loc><lastmod>%s</lastmod><changefreq>%s</changefreq><priority>%.1f</priority></url>',
            $this->getLoc(),
            $this->getLastmod()->format('Y-m-d'),
            $this->getChangefreq(),
            $this->getPriority());
    }

    /**
     * @return string
     */
    public function getLoc()
    {
        return $this->loc;
    }

    /**
     * @param string $loc
     */
    public function setLoc($loc)
    {
        $this->loc = $loc;
    }

    /**
     * @return \DateTimeInterface
     */
    public function getLastmod()
    {
        return $this->lastmod;
    }

    /**
     * @param \DateTimeInterface $lastmod
     */
    public function setLastmod($lastmod)
    {
        $this->lastmod = $lastmod;
    }

    /**
     * @return string
     */
    public function getChangefreq()
    {
        return $this->changefreq;
    }

    /**
     * @param string $changefreq
     */
    public function setChangefreq($changefreq)
    {
        $this->changefreq = $changefreq;
    }

    /**
     * @return float
     */
    public function getPriority()
    {
        return $this->priority;
    }

    /**
     * @param float $priority
     */
    public function setPriority($priority)
    {
        $this->priority = $priority;
    }

    /**
     * @param string $resource
     */
    public function setResource($resource)
    {
        $this->resource = $resource;
    }

    /**
     * @return string
     */
    public function getResource()
    {
        return $this->resource;
    }

    /**
     * @param int $identifier
     */
    public function setIdentifier($identifier)
    {
        $this->identifier = $identifier;
    }

    /**
     * @return int
     */
    public function getIdentifier()
    {
        return $this->identifier;
    }
}
