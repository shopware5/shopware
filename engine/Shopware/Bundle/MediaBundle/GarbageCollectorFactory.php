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

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\DBAL\Connection;
use Shopware\Bundle\MediaBundle\Struct\MediaPosition;

/**
 * Class GarbageCollectorFactory
 * @package Shopware\Bundle\MediaBundle
 */
class GarbageCollectorFactory
{
    /**
     * @var \Enlight_Event_EventManager
     */
    private $events;

    /**
     * @var Connection
     */
    private $connection;

    /**
     * @param \Enlight_Event_EventManager $events
     * @param Connection $connection
     */
    public function __construct(\Enlight_Event_EventManager $events, Connection $connection)
    {
        $this->connection = $connection;
        $this->events = $events;
    }

    /**
     * @return GarbageCollector
     */
    public function factory()
    {
        $mediaPositions = $this->getMediaPositions();

        return new GarbageCollector($mediaPositions, $this->connection);
    }

    /**
     * Return default media-positions
     *
     * @return ArrayCollection
     */
    private function getDefaultMediaPositions()
    {
        return new ArrayCollection([
            new MediaPosition('s_articles_img', 'media_id', 'id'),
            new MediaPosition('s_categories', 'mediaID', 'id'),
            new MediaPosition('s_emarketing_banners', 'img', 'path'),
            new MediaPosition('s_blog_media', 'media_id', 'id'),
            new MediaPosition('s_core_config_mails_attachments', 'mediaID', 'id'),
            new MediaPosition('s_filter_values', 'media_id', 'id'),
            new MediaPosition('s_emotion_element_value', 'value', 'path'),
            new MediaPosition('s_emotion', 'landingpage_teaser', 'path'),
            new MediaPosition('s_articles_downloads', 'filename', 'path'),
            new MediaPosition('s_articles_supplier', 'img', 'path')
        ]);
    }

    /**
     * @return MediaPosition[]
     */
    private function getMediaPositions()
    {
        $mediaPositions = $this->getDefaultMediaPositions();

        $mediaPositions = $this->events->collect(
            'Shopware_Collect_MediaPositions',
            $mediaPositions
        );

        return $mediaPositions->toArray();
    }
}
