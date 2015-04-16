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

/**
 * Shopware Translation Component
 */
class Shopware_Components_Translation
{
    /**
     * Filter translation text method
     *
     * @param string $text
     * @return string
     */
    protected function filterText($text)
    {
        $text = html_entity_decode($text);
        $text = preg_replace('!<[^>]*?>!', ' ', $text);
        $text = str_replace(chr(0xa0), ' ', $text);
        $text = preg_replace('/\s\s+/', ' ', $text);
        $text = htmlspecialchars($text);
        $text = trim($text);
        return $text;
    }

    /**
     * Returns mapping for a translation type
     *
     * @param $type
     * @return array|bool
     */
    protected function getMapping($type)
    {
        switch ($type) {
            case 'article':
                return array(
                    'txtArtikel'          => 'name',
                    'txtshortdescription' => 'description',
                    'txtlangbeschreibung' => 'descriptionLong',
                    'txtzusatztxt'        => 'additionalText',
                    'txtkeywords'         => 'keywords',
                    'txtpackunit'         => 'packUnit'
                );
            case 'variant':
                return array(
                    'txtzusatztxt' => 'additionalText',
                    'txtpackunit'  => 'packUnit'
                );
            case 'link':
                return array(
                    'linkname' => 'description'
                );
            case 'download':
                return array(
                    'downloadname' => 'description'
                );
            case 'config_countries':
                return array(
                    'countryname' => 'name',
                    'notice' => 'description'
                );
            case 'config_units':
                return array(
                    'description' => 'name'
                );
            case 'config_dispatch':
                return array(
                    'dispatch_name' => 'name',
                    'dispatch_description' => 'description',
                    'dispatch_status_link' => 'statusLink'
                );
            default:
                return false;
        }
    }

    /**
     * Filter translation data for saving.
     *
     * @param   $type
     * @param   $data
     * @param   null $key
     * @return  array
     */
    public function filterData($type, $data, $key = null)
    {
        $map = $this->getMapping($type);
        $tmp = isset($key) ? $data[$key] : $data;
        if ($map !== false) {
            foreach (array_flip($map) as $from => $to) {
                if (isset($tmp[$from])) {
                    $tmp[$to] = $tmp[$from];
                    unset($tmp[$from]);
                }
            }
        }
        foreach ($tmp as $tmpKey => $value) {
            if (!is_string($value)) {
                continue;
            }
            if (strlen(trim($value)) == 0) {
                unset($tmp[$tmpKey]);
            }
        }
        if (isset($key)) {
            $data[$key] = $tmp;
        } else {
            $data = $tmp;
        }
        $data = serialize($data);
        return $data;
    }

    /**
     * Un filter translation data for output.
     *
     * @param   $type
     * @param   $data
     * @param   null $key
     * @return  array
     */
    public function unFilterData($type, $data, $key = null)
    {
        $tmp = unserialize($data);
        if ($tmp === false) {
            $tmp = unserialize(utf8_decode($data));
        }
        if ($tmp === false) {
            return array();
        }
        if ($key !== null) {
            $tmp = $tmp[$key];
        }
        $map = $this->getMapping($type);
        if ($map === false) {
            return $tmp;
        }
        foreach ($map as $from => $to) {
            if (isset($tmp[$from])) {
                $tmp[$to] = $tmp[$from];
                unset($tmp[$from]);
            }
        }
        return $tmp;
    }

    /**
     * Reads a single translation data from the storage.
     *
     * @param   $language
     * @param   $type
     * @param   int $key
     * @param   bool $merge
     * @return  array
     */
    public function read($language, $type, $key = 1, $merge = false)
    {
        if ($type == 'variantMain') {
            $type = 'article';
        }

        $sql  = '
            SELECT `objectdata`
            FROM `s_core_translations`
            WHERE `objecttype` = ?
            AND `objectkey` = ?
            AND `objectlanguage` = ?
        ';
        $data = Shopware()->Db()->fetchOne($sql, array(
            $type,
            $merge ? 1 : $key,
            $language
        ));

        return $this->unFilterData($type, $data, $merge ? $key : null);
    }

    /**
     * Reads a single translation data from the storage.
     * Also loads fallback (has less priority)
     *
     * @param   $language
     * @param   $fallback
     * @param   $type
     * @param   int $key
     * @param   bool $merge
     * @return  array
     */
    public function readWithFallback($language, $fallback, $type, $key = 1, $merge = false)
    {
        $translation = $this->read($language, $type, $key, $merge);
        if ($fallback) {
            $translationFallback = $this->read($fallback, $type, $key, $merge);
        } else {
            $translationFallback = array();
        }

        return $translation + $translationFallback;
    }

    /**
     * Reads multiple translation data from storage.
     *
     * @param   $language
     * @param   $type
     * @param   int $key
     * @param   bool $merge
     * @return  array
     */
    public function readBatch($language, $type, $key = 1, $merge = false)
    {
        if ($type == 'variantMain') {
            $type = 'article';
        }

        $queryBuilder = Shopware()->Models()->getDBALQueryBuilder()
            ->select('objectdata, objectlanguage, objecttype, objectkey')
            ->from('s_core_translations', 't');

        if ($language) {
            $queryBuilder
                ->andWhere('t.objectlanguage = :objectLanguage')
                ->setParameter('objectLanguage', $language);
        }
        if ($type) {
            $queryBuilder
                ->andWhere('t.objecttype = :objectType')
                ->setParameter('objectType', $type);
        }
        if ($key) {
            $queryBuilder
                ->andWhere('t.objectkey = :objectKey')
                ->setParameter('objectKey', $merge ? 1 : $key);
        }

        $data = $queryBuilder->execute()->fetchAll();

        foreach ($data as &$translation) {
            $translation['objectdata'] = $this->unFilterData(
                $translation['objecttype'],
                $translation['objectdata'],
                null
            );

            if ($merge) {
                return $translation['objectdata'];
            }
        }
        return $data;
    }

    /**
     * Reads multiple translations including their fallbacks
     * Merges the two (fallback has less priority) and returns the results
     *
     * @param int $language
     * @param int $fallback
     * @param $type
     * @return array|mixed
     */
    public function readBatchWithFallback($language, $fallback, $type)
    {
        $translationData = $this->readBatch($language, $type, 1, true);

        // Look for a fallback and correspondent translations
        if (!empty($fallback)) {
            $translationFallback = $this->readBatch($fallback, $type, 1, true);

            if (!empty($translationFallback)) {
                // We need something like array_merge_recursive, but that also
                // recursively merges elements with int keys.
                foreach ($translationFallback as $key => $data) {
                    if (array_key_exists($key, $translationData)) {
                        $translationData[$key] += $data;
                    } else {
                        $translationData[$key] = $data;
                    }
                }
            }
        }

        return $translationData;
    }

    /**
     * Deletes translations from storage.
     *
     * @param   $language
     * @param   $type
     * @param   int $key
     * @return  array
     */
    public function delete($language, $type, $key = 1)
    {
        $queryBuilder = Shopware()->Models()->getDBALQueryBuilder()
            ->delete('s_core_translations');

        if ($language) {
            $queryBuilder
                ->andWhere('objectlanguage = :objectLanguage')
                ->setParameter('objectLanguage', $language);
        }
        if ($type) {
            $queryBuilder
                ->andWhere('objecttype = :objectType')
                ->setParameter('objectType', $type);
        }
        if ($key) {
            $queryBuilder
                ->andWhere('objectkey = :objectKey')
                ->setParameter('objectKey', $key);
        }

        $queryBuilder->execute();
    }

    /**
     * Writes multiple translation data to storage.
     *
     * @param mixed $data
     * @param bool $merge
     */
    public function writeBatch($data, $merge = false)
    {
        $requiredKeys = array('objectdata', 'objectlanguage', 'objecttype', 'objectkey');

        foreach ($data as $translation) {
            if (count(array_intersect_key(array_flip($requiredKeys), $translation)) !== count($requiredKeys)) {
                continue;
            }

            $this->write(
                $translation['objectlanguage'],
                $translation['objecttype'],
                $translation['objectkey'] ? : 1,
                $translation['objectdata'],
                $merge
            );
        }
    }

    /**
     * Saves translation data to the storage.
     *
     * @param   $language
     * @param   $type
     * @param   int $key
     * @param   mixed $data
     * @param   bool $merge
     * @return  int|bool
     */
    public function write($language, $type, $key = 1, $data = null, $merge = false)
    {
        if ($type == 'variantMain') {
            $type = 'article';
            $data = array_merge(
                $this->read($language, $type, $key),
                $data
            );
        }

        if ($merge) {
            $tmp = $this->read($language, $type, 1);
            $tmp[$key] = $data;
            $data = $tmp;
        }

        $data = $this->filterData($type, $data, $merge ? $key : null);

        if (!empty($data)) {
            $sql = '
                INSERT INTO `s_core_translations` (
                  `objecttype`, `objectdata`, `objectkey`, `objectlanguage`, `dirty`
                ) VALUES (
                  ?, ?, ?, ?, 1
                ) ON DUPLICATE KEY UPDATE `objectdata`=VALUES(`objectdata`), `dirty` = 1;
            ';
            Shopware()->Db()->query($sql, array(
                $type, $data, $merge ? 1 : $key, $language
            ));
        } else {
            $sql = '
                DELETE FROM `s_core_translations`
                WHERE `objecttype`=?
                AND `objectkey`=?
                AND `objectlanguage`=?
            ';
            Shopware()->Db()->query($sql, array(
                $type, $merge ? 1 : $key, $language
            ));
        }
        if ($type == 'article') {
            $this->fixArticleTranslation($language, $key, $data);
        }
    }

    /**
     * Fix article translation table data.
     *
     * @param $languageId
     * @param $articleId
     * @param $data
     */
    protected function fixArticleTranslation($languageId, $articleId, $data)
    {
        $sql = "SELECT id FROM s_core_shops WHERE fallback_id = ?";
        $ids = Shopware()->Db()->fetchCol($sql, array($languageId));

        $existStmt = Shopware()->Container()->get('dbal_connection')->prepare(
            "SELECT id
             FROM s_core_translations
             WHERE objectlanguage = :language"
        );

        $insertStmt = Shopware()->Container()->get('dbal_connection')->prepare("
          INSERT INTO `s_articles_translations` (articleID, languageID, name, keywords, description, description_long)
          VALUE (:articleId, :languageId, :name, :keywords, :description, :descriptionLong)
          ON DUPLICATE KEY UPDATE
              name = VALUES(name),
              keywords = VALUES(keywords),
              description = VALUES(description),
              description_long = VALUES(description_long);
        ");

        // prepare data
        $data = unserialize($data);
        if (!empty($data['txtlangbeschreibung']) && strlen($data['txtlangbeschreibung']) > 1000) {
            $data['txtlangbeschreibung'] = substr(strip_tags($data['txtlangbeschreibung']), 0, 1000);
        }
        $data['txtArtikel'] = isset($data['txtArtikel']) ? (string) $data['txtArtikel'] : '';
        $data['txtkeywords'] = ($data['txtkeywords']) ? (string) $data['txtkeywords'] : '';
        $data['txtshortdescription'] = isset($data['txtshortdescription']) ? (string) $data['txtshortdescription'] : '';
        $data['txtlangbeschreibung'] = isset($data['txtlangbeschreibung']) ? (string) $data['txtlangbeschreibung'] : '';

        // Insert s_articles_translations entry for current locale
        $insertStmt->execute(array(
            ':articleId' => $articleId,
            ':languageId' => $languageId,
            ':name' => $data['txtArtikel'],
            ':keywords' => $data['txtkeywords'],
            ':description' => $data['txtshortdescription'],
            ':descriptionLong' => $data['txtlangbeschreibung']
        ));

        // Insert s_articles_translations entry for fallbacks
        foreach ($ids as $id) {
            $existStmt->execute(array(':language' => $id));
            $exist = $existStmt->fetch(PDO::FETCH_COLUMN);

            if ($exist) {
                continue;
            }

            $insertStmt->execute(array(
                ':articleId' => $articleId,
                ':languageId' => $id,
                ':name' => $data['txtArtikel'],
                ':keywords' => $data['txtkeywords'],
                ':description' => $data['txtshortdescription'],
                ':descriptionLong' => $data['txtlangbeschreibung']
            ));
        }
    }
}
