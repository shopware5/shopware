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
use Shopware\Bundle\AttributeBundle\Service\TypeMapping;
use Shopware\Bundle\MediaBundle\Struct\MediaPosition;

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
     * @var MediaServiceInterface
     */
    private $mediaService;

    public function __construct(\Enlight_Event_EventManager $events, Connection $connection, MediaServiceInterface $mediaService)
    {
        $this->connection = $connection;
        $this->events = $events;
        $this->mediaService = $mediaService;
    }

    /**
     * @throws \Enlight_Event_Exception
     *
     * @return GarbageCollector
     */
    public function factory()
    {
        $mediaPositions = $this->getMediaPositions();

        return new GarbageCollector($mediaPositions, $this->connection, $this->mediaService);
    }

    /**
     * Return default media-positions
     *
     * @return MediaPosition[]
     */
    private function getDefaultMediaPositions()
    {
        return [
            new MediaPosition('s_articles_img', 'media_id'),
            new MediaPosition('s_categories', 'mediaID'),
            new MediaPosition('s_emarketing_banners', 'img', 'path'),
            new MediaPosition('s_blog_media', 'media_id'),
            new MediaPosition('s_core_config_mails_attachments', 'mediaID'),
            new MediaPosition('s_filter_values', 'media_id'),
            new MediaPosition('s_emotion_element_value', 'value', 'path'),
            new MediaPosition('s_emotion_element_value', 'value', 'path', MediaPosition::PARSE_JSON),
            new MediaPosition('s_emotion_element_value', 'value', 'path', MediaPosition::PARSE_HTML),
            new MediaPosition('s_articles_downloads', 'filename', 'path'),
            new MediaPosition('s_articles_supplier', 'img', 'path'),
            new MediaPosition('s_core_templates_config_values', 'value', 'path', MediaPosition::PARSE_SERIALIZE),
            new MediaPosition('s_core_documents_box', 'value', 'path', MediaPosition::PARSE_HTML),
            new MediaPosition('s_articles', 'description_long', 'path', MediaPosition::PARSE_HTML),
            new MediaPosition('s_billing_template', 'value', 'path', MediaPosition::PARSE_HTML),
            new MediaPosition('s_campaigns_html', 'html', 'path', MediaPosition::PARSE_HTML),
            new MediaPosition('s_cms_static', 'html', 'path', MediaPosition::PARSE_HTML),
            new MediaPosition('s_cms_support', 'text', 'path', MediaPosition::PARSE_HTML),
            new MediaPosition('s_cms_support', 'text2', 'path', MediaPosition::PARSE_HTML),
            new MediaPosition('s_core_config_mails', 'contentHTML', 'path', MediaPosition::PARSE_HTML),
            new MediaPosition('s_core_config_values', 'value', 'path', MediaPosition::PARSE_SERIALIZE),
        ];
    }

    /**
     * @throws \Enlight_Event_Exception
     *
     * @return MediaPosition[]
     */
    private function getMediaPositions()
    {
        $mediaPositions = new ArrayCollection(
            array_merge(
                $this->getDefaultMediaPositions(),
                $this->getAttributeMediaPositions()
            )
        );

        $mediaPositions = $this->events->collect(
            'Shopware_Collect_MediaPositions',
            $mediaPositions
        );

        return $mediaPositions->toArray();
    }

    /**
     * @return MediaPosition[]
     */
    private function getAttributeMediaPositions()
    {
        $mediaPositions = [];

        // value is just the media ID
        $singleSelectionColumns = $this->connection->createQueryBuilder()
            ->select(['table_name', 'column_name'])
            ->from('s_attribute_configuration')
            ->andWhere('entity = :entityName')
            ->andWhere('column_type = :columnType')
            ->setParameters([
                'entityName' => \Shopware\Models\Media\Media::class,
                'columnType' => TypeMapping::TYPE_SINGLE_SELECTION,
            ])
            ->execute()
            ->fetchAll();

        foreach ($singleSelectionColumns as $attribute) {
            $mediaPositions[] = new MediaPosition($attribute['table_name'], $attribute['column_name']);
        }

        // values are separated by pipes '|'
        $multiSelectionColumns = $this->connection->createQueryBuilder()
            ->select(['table_name', 'column_name'])
            ->from('s_attribute_configuration')
            ->andWhere('entity = :entityName')
            ->andWhere('column_type = :columnType')
            ->setParameters([
                'entityName' => \Shopware\Models\Media\Media::class,
                'columnType' => TypeMapping::TYPE_MULTI_SELECTION,
            ])
            ->execute()
            ->fetchAll();

        foreach ($multiSelectionColumns as $attribute) {
            $mediaPositions[] = new MediaPosition($attribute['table_name'], $attribute['column_name'], 'id', MediaPosition::PARSE_PIPES);
        }

        // values as path in html/smarty code
        $htmlColumns = $this->connection->createQueryBuilder()
            ->select(['table_name', 'column_name'])
            ->from('s_attribute_configuration')
            ->andWhere('column_type = :columnType')
            ->setParameters([
                'columnType' => TypeMapping::TYPE_HTML,
            ])
            ->execute()
            ->fetchAll();

        foreach ($htmlColumns as $attribute) {
            $mediaPositions[] = new MediaPosition($attribute['table_name'], $attribute['column_name'], 'path', MediaPosition::PARSE_HTML);
        }

        return $mediaPositions;
    }
}
