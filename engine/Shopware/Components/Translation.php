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

use Doctrine\DBAL\Connection;
use Shopware\Bundle\AttributeBundle\Service\CrudService;
use Shopware\Components\DependencyInjection\Container;
use Shopware\Components\Translation\ObjectTranslator;

/**
 * Shopware Translation Component
 */
class Shopware_Components_Translation
{
    /**
     * @var Connection
     */
    private $connection;

    /**
     * @var int
     */
    private $localeId;

    /**
     * @var int
     */
    private $fallbackLocaleId;

    public function __construct(Connection $connection, Container $container)
    {
        $this->connection = $connection;

        // Default language in case no locale exists
        $this->localeId = 1;

        $locale = null;

        // Determine how to query the current language
        if ($container->initialized('auth') && $container->get('auth')->hasIdentity()) {
            $locale = $container->get('auth')->getIdentity()->locale;
        } elseif ($container->has('shop')) {
            $locale = $container->get('shop')->getLocale();
        }

        if ($locale) {
            $this->localeId = $locale->getId();
        }

        // Determine fallback language
        $this->fallbackLocaleId = $this->getFallbackLocaleId($this->localeId);
    }

    /**
     * Filter translation data for saving.
     *
     * @param string   $type
     * @param int|null $key
     *
     * @return string
     */
    public function filterData($type, array $data, $key = null)
    {
        $map = $this->getMapping($type);
        $tmp = $key ? $data[$key] : $data;

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
            if (trim($value) === '') {
                unset($tmp[$tmpKey]);
            }
        }

        if ($key) {
            $data[$key] = $tmp;
        } else {
            $data = $tmp;
        }

        return serialize($data);
    }

    /**
     * Unfilter translation data for output.
     *
     * @param string   $type
     * @param string   $data
     * @param int|null $key
     *
     * @return array
     */
    public function unFilterData($type, $data, $key = null)
    {
        $tmp = unserialize($data, ['allowed_classes' => false]);
        if ($tmp === false) {
            $tmp = unserialize(utf8_decode($data), ['allowed_classes' => false]);
        }
        if ($tmp === false) {
            return [];
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
     * @param string|int $language
     * @param string     $type
     * @param int        $key
     * @param bool       $merge
     *
     * @return array
     */
    public function read($language, $type, $key = 1, $merge = false)
    {
        if ($type === 'variantMain') {
            $type = 'article';
        }

        $query = $this->connection->createQueryBuilder()
            ->select('`objectdata`')
            ->from('`s_core_translations`')
            ->where('`objecttype` = :type')
            ->andWhere('`objectkey` = :key')
            ->andWhere('`objectlanguage` = :language')
            ->setParameter(':type', $type)
            ->setParameter(':key', $merge ? 1 : $key)
            ->setParameter(':language', $language);

        $data = $query->execute()
            ->fetch(\PDO::FETCH_COLUMN);

        return $this->unFilterData($type, $data, $merge ? $key : null);
    }

    /**
     * Reads a single translation data from the storage.
     * Also loads fallback (has less priority)
     *
     * @param string|int $language
     * @param int        $fallback
     * @param string     $type
     * @param int        $key
     * @param bool       $merge
     *
     * @return array
     */
    public function readWithFallback($language, $fallback, $type, $key = 1, $merge = false)
    {
        $translation = $this->read($language, $type, $key, $merge);
        if ($fallback) {
            $translationFallback = $this->read($fallback, $type, $key, $merge);
        } else {
            $translationFallback = [];
        }

        return $translation + $translationFallback;
    }

    /**
     * Reads multiple translation data from storage.
     *
     * @param string|int|null $language
     * @param string          $type
     * @param int|int[]       $key
     * @param bool            $merge
     *
     * @return array
     */
    public function readBatch($language, $type, $key = 1, $merge = false)
    {
        if ($type === 'variantMain') {
            $type = 'article';
        }

        $queryBuilder = $this->connection->createQueryBuilder()
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
            if (is_array($key)) {
                $queryBuilder
                    ->andWhere('t.objectkey IN (:objectKey)')
                    ->setParameter('objectKey', $key, Connection::PARAM_INT_ARRAY);
            } else {
                $queryBuilder
                    ->andWhere('t.objectkey = :objectKey')
                    ->setParameter('objectKey', $merge ? 1 : $key);
            }
        }

        $data = $queryBuilder->execute()->fetchAll();

        foreach ($data as &$translation) {
            $translation['objectdata'] = $this->unFilterData(
                $translation['objecttype'],
                $translation['objectdata']
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
     * @param string|int      $language
     * @param string|int|null $fallback
     * @param string          $type
     * @param int|int[]       $key
     * @param bool            $merge
     *
     * @return array
     */
    public function readBatchWithFallback($language, $fallback, $type, $key = 1, $merge = true)
    {
        $translationData = $this->readBatch($language, $type, $key, $merge);

        // Look for a fallback and correspondent translations
        if (!empty($fallback)) {
            $translationFallback = $this->readBatch($fallback, $type, $key, $merge);

            if (!empty($translationFallback)) {
                // We need something like array_merge_recursive, but that also
                // recursively merges elements with int keys.
                foreach ($translationFallback as $translationKey => $data) {
                    if (array_key_exists($translationKey, $translationData)) {
                        $translationData[$translationKey] += $data;
                    } else {
                        $translationData[$translationKey] = $data;
                    }
                }
            }
        }

        return $translationData;
    }

    /**
     * Deletes translations from storage.
     *
     * @param string|int|null $language
     * @param string          $type
     * @param int             $key
     */
    public function delete($language, $type, $key = 1)
    {
        $queryBuilder = $this->connection->createQueryBuilder()
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
     * @param array $data
     * @param bool  $merge
     */
    public function writeBatch($data, $merge = false)
    {
        $requiredKeys = ['objectdata', 'objectlanguage', 'objecttype', 'objectkey'];

        foreach ($data as $translation) {
            if (count(array_intersect_key(array_flip($requiredKeys), $translation)) !== count($requiredKeys)) {
                continue;
            }

            $this->write(
                $translation['objectlanguage'],
                $translation['objecttype'],
                $translation['objectkey'] ?: 1,
                $translation['objectdata'],
                $merge
            );
        }
    }

    /**
     * Saves translation data to the storage
     *
     * @param string|int $language
     * @param string     $type
     * @param array|null $data
     * @param int        $key
     * @param bool       $merge
     */
    public function write($language, $type, $key = 1, $data = null, $merge = false)
    {
        if ($type === 'variantMain') {
            $type = 'article';
            $data = array_merge(
                $this->read($language, $type, $key),
                $data
            );
        }

        if ($merge) {
            $tmp = $this->read($language, $type);
            $tmp[$key] = $data;
            $data = $tmp;
        }

        $serializedData = $this->filterData($type, $data, $merge ? $key : null);

        if (!empty($data)) {
            $sql = '
                INSERT INTO `s_core_translations` (
                  `objecttype`, `objectdata`, `objectkey`, `objectlanguage`, `dirty`
                ) VALUES (
                  :type, :data, :key, :language, 1
                ) ON DUPLICATE KEY UPDATE `objectdata`=VALUES(`objectdata`), `dirty` = 1;
            ';
            $this->connection->executeQuery(
                $sql,
                [
                    ':type' => $type,
                    ':data' => $serializedData,
                    ':key' => $merge ? 1 : $key,
                    ':language' => $language,
                ]
            );
        } else {
            $sql = '
                DELETE FROM `s_core_translations`
                WHERE `objecttype`= :type
                AND `objectkey`= :key
                AND `objectlanguage`= :language
            ';
            $this->connection->executeQuery(
                $sql,
                [
                    ':type' => $type,
                    ':key' => $merge ? 1 : $key,
                    ':language' => $language,
                ]
            );
        }
        if ($type === 'article') {
            $this->fixArticleTranslation($language, $key, $serializedData);
        }
    }

    /**
     * Translates an order by translating it's document types, payment and dispatch methods.
     */
    public function translateOrders(array $orders, ?int $language = null, ?int $fallback = null): array
    {
        $documentTypes = [];
        $paymentMethods = [];
        $dispatchMethods = [];

        // Extract documents, payment and dispatch methods
        foreach ($orders as $order) {
            if (isset($order['dispatch'])) {
                $dispatchMethods[$order['dispatch']['id']] = $order['dispatch'];
            }
            if (isset($order['payment'])) {
                $paymentMethods[$order['payment']['id']] = $order['payment'];
            }
            if (array_key_exists('documents', $order)) {
                foreach ($order['documents'] as $documentIndex => $document) {
                    $documentTypes[$document['type']['id']] = $document['type'];
                }
            }
        }

        // Translate the objects
        $translatedDocumentTypes = $this->translateDocuments($documentTypes, $language, $fallback);
        $translatedDispatchMethods = $this->translateDispatchMethods($dispatchMethods, $language, $fallback);
        $translatedPaymentMethods = $this->translatePaymentMethods($paymentMethods, $language, $fallback);

        // Save the translated objects
        foreach ($orders as &$order) {
            $orderDocuments = $order['documents'];
            for ($documentCounter = 0, $orderDocumentsCount = count($orderDocuments); $documentCounter < $orderDocumentsCount; ++$documentCounter) {
                $type = $orderDocuments[$documentCounter]['type'];
                $order['documents'][$documentCounter]['type'] = $translatedDocumentTypes[$type['id']];
            }

            if ($translatedDispatchMethods[$order['dispatch']['id']]) {
                $order['dispatch'] = $translatedDispatchMethods[$order['dispatch']['id']];
            }

            if ($translatedPaymentMethods[$order['payment']['id']]) {
                $order['payment'] = $translatedPaymentMethods[$order['payment']['id']];
            }
        }

        return $orders;
    }

    /**
     * Translates dispatch methods.
     *
     * @return array Translated dispatch methods
     */
    public function translateDispatchMethods(array $dispatchMethods, ?int $language = null, ?int $fallback = null): array
    {
        $translator = $this->getObjectTranslator('config_dispatch', $language, $fallback);

        $translatedDispatchMethods = array_map(
            static function ($dispatchMethod) use ($translator) {
                if (!$dispatchMethod) {
                    return [];
                }

                return $translator->translateObjectProperty($dispatchMethod, 'dispatch_name', 'name');
            }, $dispatchMethods);

        return $translatedDispatchMethods;
    }

    /**
     * Translates documents.
     *
     * @return array Translated documents
     */
    public function translateDocuments(array $documents, ?int $language = null, ?int $fallback = null): array
    {
        $translator = $this->getObjectTranslator('documents', $language, $fallback);

        $translatedDocuments = array_map(
            static function ($document) use ($translator) {
                return $translator->translateObjectProperty($document, 'name');
            }, $documents);

        return $translatedDocuments;
    }

    /**
     * Translates payment methods.
     *
     * @return array Translated payments
     */
    public function translatePaymentMethods(array $payments, ?int $language = null, ?int $fallback = null): array
    {
        $translator = $this->getObjectTranslator('config_payment', $language, $fallback);

        $translatedPayments = array_map(
            static function ($payment) use ($translator) {
                $translatedPayment = $translator->translateObjectProperty($payment, 'description');
                $translatedPayment = $translator->translateObjectProperty(
                $translatedPayment,
                'additionalDescription',
                'additionaldescription'
            );

                return $translatedPayment;
            }, $payments);

        return $translatedPayments;
    }

    /**
     * Creates an object translator for a specific type of translatable object.
     */
    public function getObjectTranslator(string $type, ?int $language = null, ?int $fallback = null): ObjectTranslator
    {
        // Check if the languages are specified and query them if not
        if ($language === null) {
            $language = $this->localeId;
            $fallback = $this->fallbackLocaleId;
        }

        $fallback = $fallback ?: $this->getFallbackLocaleId($language);

        return new ObjectTranslator(
            $this,
            $type,
            $language ?: $this->localeId,
            $fallback
        );
    }

    /**
     * Loads the id of the fallback language.
     */
    public function getFallbackLocaleId(int $currentLocaleId): int
    {
        if ($currentLocaleId === 1) {
            return 1;
        }

        $fallback = $this->connection->fetchColumn(
            'SELECT id FROM s_core_locales WHERE locale = "en_GB"'
        );

        // Fallback onto German if en_GB does not exist
        return ((int) $fallback[0]) ?: 1;
    }

    /**
     * @deprecated in 5.6, will be removed in 5.7 without a replacement
     *
     * Filter translation text method
     *
     * @param string $text
     *
     * @return string
     */
    protected function filterText($text)
    {
        trigger_error(sprintf('%s:%s is deprecated since Shopware 5.6 and will be removed with 5.7. Will be removed without replacement.', __CLASS__, __METHOD__), E_USER_DEPRECATED);

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
     * @param string $type
     *
     * @return array|bool
     */
    protected function getMapping($type)
    {
        switch ($type) {
            case 'article':
                return [
                    'txtArtikel' => 'name',
                    'txtshortdescription' => 'description',
                    'txtlangbeschreibung' => 'descriptionLong',
                    'txtshippingtime' => 'shippingTime',
                    'txtzusatztxt' => 'additionalText',
                    'txtkeywords' => 'keywords',
                    'txtpackunit' => 'packUnit',
                ];
            case 'variant':
                return [
                    'txtshippingtime' => 'shippingTime',
                    'txtzusatztxt' => 'additionalText',
                    'txtpackunit' => 'packUnit',
                ];
            case 'link':
                return [
                    'linkname' => 'description',
                ];
            case 'download':
                return [
                    'downloadname' => 'description',
                ];
            case 'config_countries':
                return [
                    'countryname' => 'name',
                    'notice' => 'description',
                ];
            case 'config_units':
                return [
                    'description' => 'name',
                ];
            case 'config_dispatch':
                return [
                    'dispatch_name' => 'name',
                    'dispatch_description' => 'description',
                    'dispatch_status_link' => 'statusLink',
                ];
            default:
                return false;
        }
    }

    /**
     * Fix product translation table data.
     *
     * @param int    $languageId
     * @param int    $articleId
     * @param string $data
     *
     * @throws \Exception
     */
    protected function fixArticleTranslation($languageId, $articleId, $data)
    {
        $fallbacks = $this->connection->fetchAll(
            'SELECT id FROM s_core_shops WHERE fallback_id = :languageId',
            [':languageId' => $languageId]
        );
        $fallbacks = array_column($fallbacks, 'id');

        $data = $this->prepareArticleData($data);
        $this->addProductTranslation($articleId, $languageId, $data);

        $existQuery = $this->connection->prepare(
            "SELECT 1
             FROM s_core_translations
             WHERE objectlanguage = :language
             AND objecttype = 'article'
             AND objectkey = :articleId LIMIT 1"
        );

        foreach ($fallbacks as $id) {
            //check if fallback ids contains an individual translation
            $existQuery->execute([':language' => $id, ':articleId' => $articleId]);
            $exist = $existQuery->fetch(PDO::FETCH_COLUMN);

            //if shop translation of fallback exists, skip
            if ($exist) {
                continue;
            }
            //add fallback translation to s_articles_translation for search requests.
            $this->addProductTranslation($articleId, $id, $data);
        }
    }

    private function prepareArticleData(string $data): array
    {
        $data = unserialize($data, ['allowed_classes' => false]);
        if (!empty($data['txtlangbeschreibung']) && strlen($data['txtlangbeschreibung']) > 1000) {
            $data['txtlangbeschreibung'] = substr(strip_tags($data['txtlangbeschreibung']), 0, 1000);
        }

        $data = array_merge($data, [
            'name' => isset($data['txtArtikel']) ? (string) $data['txtArtikel'] : '',
            'keywords' => isset($data['txtkeywords']) ? (string) $data['txtkeywords'] : '',
            'description' => isset($data['txtshortdescription']) ? (string) $data['txtshortdescription'] : '',
            'description_long' => isset($data['txtlangbeschreibung']) ? (string) $data['txtlangbeschreibung'] : '',
            'shippingtime' => isset($data['txtshippingtime']) ? (string) $data['txtshippingtime'] : '',
        ]);

        $schemaManager = $this->connection->getSchemaManager();
        $columns = $schemaManager->listTableColumns('s_articles_translations');
        $columns = array_keys($columns);

        foreach ($data as $key => $value) {
            $column = strtolower($key);
            $column = str_replace(CrudService::EXT_JS_PREFIX, '', $column);

            unset($data[$key]);
            if (in_array($column, $columns)) {
                $data[$column] = $value;
            }
        }

        return $data;
    }

    private function addProductTranslation(int $productId, int $languageId, array $data): void
    {
        $query = $this->connection->executeQuery(
            'SELECT id FROM s_articles_translations WHERE articleID = :articleId AND languageID = :languageId LIMIT 1',
            [':articleId' => $productId, ':languageId' => $languageId]
        );
        $exist = $query->fetch(PDO::FETCH_COLUMN);

        if ($exist) {
            $this->updateProductTranslation($exist, $data);
        } else {
            $this->insertProductTranslation($productId, $languageId, $data);
        }
    }

    private function insertProductTranslation(int $productId, int $languageId, array $data): void
    {
        $data = array_merge($data, ['languageID' => $languageId, 'articleID' => $productId]);

        $query = $this->connection->createQueryBuilder();
        $query->insert('s_articles_translations');
        foreach ($data as $key => $value) {
            $query->setValue($key, ':' . $key);
            $query->setParameter(':' . $key, $value);
        }
        $query->execute();
    }

    private function updateProductTranslation(int $id, array $data): void
    {
        $query = $this->connection->createQueryBuilder();

        $query->update('s_articles_translations', 'translation');
        foreach ($data as $key => $value) {
            $query->set($key, ':' . $key);
            $query->setParameter(':' . $key, $value);
        }

        $query->where('id = :id');
        $query->setParameter(':id', $id);
        $query->execute();
    }
}
