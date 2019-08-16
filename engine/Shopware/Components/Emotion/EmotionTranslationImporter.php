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

namespace Shopware\Components\Emotion;

use Doctrine\DBAL\Connection;
use Shopware\Components\Emotion\Exception\MappingRequiredException;
use Shopware_Components_Translation;

class EmotionTranslationImporter
{
    /**
     * @var Connection
     */
    private $connection;

    /**
     * @var Shopware_Components_Translation
     */
    private $translator;

    public function __construct(Connection $connection, Shopware_Components_Translation $translationComponent = null)
    {
        $this->connection = $connection;
        $this->translator = $translationComponent ?: Shopware()->Container()->get('translation');
    }

    /**
     * @param int  $emotionId
     * @param bool $autoMapping
     */
    public function importTranslations($emotionId, array $translations, $autoMapping = true)
    {
        $translations = $this->prepareDataForImport($emotionId, $translations, $autoMapping);

        foreach ($translations as $translation) {
            $this->translator->write($translation['objectlanguage'], $translation['objecttype'], $translation['objectkey'], $translation['objectdata']);
        }
    }

    /**
     * @return array
     */
    public function getLocaleMapping()
    {
        $shops = $this->connection->createQueryBuilder()
            ->select('locale.locale, shop.id, shop.name')
            ->from('s_core_shops', 'shop')
            ->leftJoin('shop', 's_core_locales', 'locale', 'locale.id = shop.locale_id')
            ->execute()
            ->fetchAll(\PDO::FETCH_GROUP);

        foreach ($shops as $key => $shopData) {
            $shops[$key] = array_combine(array_column($shopData, 'name'), array_column($shopData, 'id'));
        }

        return $shops;
    }

    /**
     * @param int  $emotionId
     * @param bool $autoMapping
     *
     * @return array
     */
    private function prepareDataForImport($emotionId, array $translations, $autoMapping)
    {
        $elements = $this->connection->createQueryBuilder()
            ->select('e.id')
            ->from('s_emotion_element', 'e')
            ->where('e.emotionID = :id')
            ->setParameter('id', $emotionId)
            ->execute()
            ->fetchAll(\PDO::FETCH_COLUMN);

        foreach ($translations as &$translation) {
            $translation['objectdata'] = unserialize($translation['objectdata'], ['allowed_classes' => false]);
            switch ($translation['objecttype']) {
                case 'emotion':
                    $translation['objectkey'] = $emotionId;
                    break;
                case 'emotionElement':
                    $elementIndex = explode('-', $translation['objectkey']);
                    $key = $elements[$elementIndex[1]];

                    if (!isset($key)) {
                        continue 2;
                    }
                    $translation['objectkey'] = $key;
                    break;
            }
        }
        unset($translation);

        if ($autoMapping) {
            $translations = $this->processAutoMapping($translations);
        }

        return $translations;
    }

    /**
     * @throws MappingRequiredException
     *
     * @return array
     */
    private function processAutoMapping(array $translations)
    {
        $shops = $this->getLocaleMapping();

        foreach ($translations as $key => &$translation) {
            if (!isset($shops[$translation['locale']])) {
                unset($translations[$key]);
                continue;
            }
            if (!isset($shops[$translation['locale']][$translation['shop']])) {
                throw new MappingRequiredException('Language mapping is required for translation import.');
            }
            $translation['objectlanguage'] = $shops[$translation['locale']][$translation['shop']];
        }

        return array_values($translations);
    }
}
