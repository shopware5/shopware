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

namespace Shopware\Components\Api\Resource;

use Doctrine\ORM\AbstractQuery;
use Doctrine\ORM\EntityRepository;
use Shopware\Components\Api\BatchInterface;
use Shopware\Components\Api\Exception as ApiException;
use Shopware\Components\Model\QueryBuilder;
use Shopware\Models\Article\Configurator\Group as ConfiguratorGroup;
use Shopware\Models\Article\Configurator\Option as ConfiguratorOption;
use Shopware\Models\Article\Detail;
use Shopware\Models\Article\Supplier;
use Shopware\Models\Country\Country;
use Shopware\Models\Country\State;
use Shopware\Models\Dispatch\Dispatch;
use Shopware\Models\Payment\Payment;
use Shopware\Models\Property\Group;
use Shopware\Models\Property\Option;
use Shopware\Models\Property\Value;
use Shopware\Models\Translation\Translation as TranslationModel;

/**
 * Translation API Resource
 */
class Translation extends Resource implements BatchInterface
{
    const TYPE_PRODUCT = 'article';
    const TYPE_VARIANT = 'variant';
    const TYPE_PRODUCT_LINK = 'link';
    const TYPE_PRODUCT_DOWNLOAD = 'download';
    const TYPE_PRODUCT_MANUFACTURER = 'supplier';
    const TYPE_COUNTRY = 'config_countries';
    const TYPE_COUNTRY_STATE = 'config_country_states';
    const TYPE_DISPATCH = 'config_dispatch';
    const TYPE_PAYMENT = 'config_payment';
    const TYPE_FILTER_SET = 'propertygroup';
    const TYPE_FILTER_GROUP = 'propertyoption';
    const TYPE_FILTER_OPTION = 'propertyvalue';
    const TYPE_CONFIGURATOR_GROUP = 'configuratorgroup';
    const TYPE_CONFIGURATOR_OPTION = 'configuratoroption';

    /**
     * @var \Shopware_Components_Translation
     */
    protected $translationWriter;

    /**
     * @var EntityRepository
     */
    protected $repository;

    /**
     * @var \Shopware_Components_Translation
     */
    private $translationComponent;

    public function __construct(\Shopware_Components_Translation $translationComponent = null)
    {
        $this->translationComponent = $translationComponent ?: Shopware()->Container()->get('translation');
    }

    /**
     * This methods needs to return an ID for the current resource.
     * The ID needs to be the primary ID of the resource (in most cases `id`).
     * If your resource supports other kinds of IDs, too, you should identify
     * your entity by these IDs and return the primary ID of that entity.
     *
     * @param array $data
     *
     * @return int|bool Return the primary ID of the entity, if it exists
     *                  Return false, if no existing entity matches $data
     */
    public function getIdByData($data)
    {
        if ($data['useNumberAsId']) {
            return $this->getIdByNumber(
                $data['key'],
                $data['type']
            );
        }

        return $data['key'];
    }

    /**
     * @return \Shopware_Components_Translation
     */
    public function getTranslationComponent()
    {
        return $this->translationComponent;
    }

    /**
     * @param \Shopware_Components_Translation $translationWriter
     */
    public function setTranslationComponent($translationWriter)
    {
        $this->translationWriter = $translationWriter;
    }

    /**
     * Returns a list of translation objects.
     *
     * @param int $offset
     * @param int $limit
     *
     * @return array
     */
    public function getList(
        $offset = 0,
        $limit = 25,
        array $criteria = [],
        array $orderBy = []
    ) {
        $this->checkPrivilege('read');

        $query = $this->getListQuery($offset, $limit, $criteria, $orderBy)->getQuery();
        $query->setHydrationMode($this->getResultMode());
        $paginator = $this->getManager()->createPaginator($query);

        $translations = $paginator->getIterator()->getArrayCopy();

        foreach ($translations as &$translation) {
            unset($translation['id']);
            $translation['data'] = $this->getTranslationComponent()->unFilterData(
                'article',
                $translation['data']
            );
        }

        return [
            'total' => $paginator->count(),
            'data' => $translations,
        ];
    }

    /**
     * Creates a new translation. If a translation already exists for
     * the passed object, the translations will be merged.
     * The reason for no difference between the update and create function
     * is the identifier of a translation.
     * A translation will be identified over the following parameters:
     *  - type      => Type of the translation
     *  - key       => Identifier of the translated object (like article, variant, ...)
     *  - shopId  => Identifier of the shop entity.
     *
     * This three parameters are required in each function: create, update, delete / *-byNumber
     *
     * @throws ApiException\ParameterMissingException
     *
     * @return TranslationModel
     */
    public function create(array $data)
    {
        $this->checkPrivilege('create');

        $this->checkRequirements($data);

        if (!isset($data['key']) || empty($data['key'])) {
            throw new ApiException\ParameterMissingException(
                'The parameter key is required for a object translation.'
            );
        }

        return $this->saveTranslation($data);
    }

    /**
     * Creates a new translation. If a translation already exists for
     * the passed object, the translations will be merged.
     * The reason for no difference between the update and create function
     * is the identifier of a translation.
     * A translation will be identified over the following parameters:
     *  - type      => Type of the translation
     *  - key       => Identifier of the translated object (like article, variant, ...)
     *  - shopId  => Identifier of the shop entity.
     *
     * This three parameters are required in each function: create, update, delete / *-byNumber
     *
     * @throws ApiException\ParameterMissingException
     *
     * @return TranslationModel
     */
    public function createByNumber(array $data)
    {
        $this->checkPrivilege('create');

        if (empty($data['key'])) {
            throw new ApiException\ParameterMissingException(
                'Create by number expects a passed entity number in the key property'
            );
        }

        $this->checkRequirements($data);

        $data['key'] = $this->getIdByNumber(
            $data['key'],
            $data['type']
        );

        return $this->saveTranslation($data);
    }

    /**
     * Creates a new translation. If a translation already exists for
     * the passed object, the translations will be merged.
     * The reason for no difference between the update and create function
     * is the identifier of a translation.
     * A translation will be identified over the following parameters:
     *  - type      => Type of the translation
     *  - key       => Identifier of the translated object (like article, variant, ...)
     *  - shopId  => Identifier of the shop entity.
     *
     * This three parameters are required in each function: create, update, delete / *-byNumber
     *
     * @param int $id - Identifier of the translated object, like the s_articles.id.
     *
     * @throws ApiException\ParameterMissingException
     *
     * @return TranslationModel
     */
    public function update($id, array $data)
    {
        $this->checkPrivilege('update');

        if (empty($id)) {
            throw new ApiException\ParameterMissingException(
                'Update expects a passed id'
            );
        }

        $this->checkRequirements($data);

        $data['key'] = $id;

        return $this->saveTranslation($data);
    }

    /**
     * Creates a new translation. If a translation already exists for
     * the passed object, the translations will be merged.
     * The reason for no difference between the update and create function
     * is the identifier of a translation.
     * A translation will be identified over the following parameters:
     *  - type      => Type of the translation
     *  - key       => Identifier of the translated object (like article, variant, ...)
     *  - shopId  => Identifier of the shop entity.
     *
     * This three parameters are required in each function: create, update, delete / *-byNumber
     *
     * @param string $number - Alphanumeric identifier of the translatable entity.
     *                       This can be a product number, configurator group name or some thing else.
     *                       For more information which number fields are supported, look into the #getIdByNumber
     *
     * @throws ApiException\ParameterMissingException
     *
     * @return TranslationModel
     */
    public function updateByNumber($number, array $data)
    {
        $this->checkPrivilege('update');

        if (empty($number)) {
            throw new ApiException\ParameterMissingException(
                'Create by number expects a passed entity number'
            );
        }
        $this->checkRequirements($data);

        $data['key'] = $this->getIdByNumber(
            $number,
            $data['type']
        );

        return $this->saveTranslation($data);
    }

    /**
     * Deletes a single translation.
     *
     * A translation will be identified over the following parameters:
     *  - type      => Type of the translation
     *  - key       => Identifier of the translated object (like article, variant, ...)
     *  - shopId  => Identifier of the shop entity.
     *
     * This three parameters are required in each function: create, update, delete / *-byNumber
     *
     * @param int   $id
     * @param array $data
     *
     * @throws \Shopware\Components\Api\Exception\NotFoundException
     * @throws ApiException\ParameterMissingException
     *
     * @return bool
     */
    public function delete($id, $data)
    {
        $this->checkPrivilege('delete');

        if (empty($id)) {
            throw new ApiException\ParameterMissingException('id');
        }

        $this->checkRequirements($data, false);

        $translation = $this->getObjectTranslation(
            $data['type'],
            $id,
            $data['shopId'],
            AbstractQuery::HYDRATE_OBJECT
        );

        if (!$translation) {
            throw new ApiException\NotFoundException(
                sprintf(
                    'No translation found for type %s, shop id %s and foreign key %s',
                    $data['type'],
                    $data['shopId'],
                    $id
                )
            );
        }

        $this->getManager()->remove($translation);

        $this->flush($translation);

        return true;
    }

    /**
     * Deletes a single translation.
     *
     * A translation will be identified over the following parameters:
     *  - type      => Type of the translation
     *  - key       => Identifier of the translated object (like article, variant, ...)
     *  - shopId  => Identifier of the shop entity.
     *
     * This three parameters are required in each function: create, update, delete / *-byNumber
     *
     * @param string $number - Alphanumeric identifier of the translatable entity.
     *                       This can be a product number, configurator group name or some thing else.
     *                       For more information which number fields are supported, look into the #getIdByNumber
     * @param array  $data
     *
     * @throws ApiException\ParameterMissingException
     *
     * @return bool
     */
    public function deleteByNumber($number, $data)
    {
        if (empty($number)) {
            throw new ApiException\ParameterMissingException(
                'Delete by number expects a passed entity number'
            );
        }

        $this->checkRequirements($data, false);

        $id = $this->getIdByNumber($number, $data['type']);

        return $this->delete($id, $data);
    }

    protected function getRepository()
    {
        if ($this->repository === null) {
            $this->repository = $this->getManager()->getRepository(TranslationModel::class);
        }

        return $this->repository;
    }

    /**
     * Helper function which creates the query builder for the getList function.
     *
     * @param int $offset
     * @param int $limit
     *
     * @return \Doctrine\ORM\QueryBuilder|\Shopware\Components\Model\QueryBuilder
     */
    protected function getListQuery($offset = 0, $limit = 25, array $criteria = [], array $orderBy = [])
    {
        $builder = $this->getManager()->createQueryBuilder();
        $builder->select(['translation'])
            ->from(TranslationModel::class, 'translation')
            ->join('translation.shop', 'shop');

        $builder->setFirstResult($offset)
            ->setMaxResults($limit);

        if (!empty($criteria)) {
            $builder->addFilter($criteria);
        }
        if (!empty($orderBy)) {
            $builder->addOrderBy($orderBy);
        }

        return $builder;
    }

    /**
     * Helper function which handles the update and create process of translations.
     *
     * @return array|TranslationModel|null
     */
    protected function saveTranslation(array $data)
    {
        /** @var array $existing */
        $existing = $this->getObjectTranslation(
            $data['type'], // Translation object type
            $data['key'], // Identifier of the translatable entity (s_articles.id)
            $data['shopId'] // Identifier of the shop object
        );

        if (!$existing) {
            $existing = [];
        } else {
            $existing['data'] = unserialize($existing['data'], ['allowed_classes' => false]);
            $this->delete($data['key'], $data);
        }

        $data = array_replace_recursive(
            $existing,
            $data
        );

        $this->getTranslationComponent()->write(
            $data['shopId'],
            $data['type'],
            $data['key'],
            $data['data']
        );

        return $this->getObjectTranslation(
            $data['type'],
            $data['key'],
            $data['shopId'],
            AbstractQuery::HYDRATE_OBJECT
        );
    }

    /**
     * Helper function which returns a language translation for a single object type.
     *
     * @param string $type       - Type of the translatable object, see class constants
     * @param int    $key        - Identifier of the translatable object. (s_articles.id)
     * @param int    $shopId     - Identifier of the shop object. (s_core_shops.id)
     * @param int    $resultMode - Flag which handles the return value between array and \Shopware\Models\Translation\Translation
     *
     * @return array|TranslationModel|null
     */
    protected function getObjectTranslation($type, $key, $shopId, $resultMode = AbstractQuery::HYDRATE_ARRAY)
    {
        /** @var QueryBuilder $builder */
        $builder = $this->getRepository()->createQueryBuilder('translations');
        $builder->setFirstResult(0)
            ->setMaxResults(1);

        $builder->addFilter([
            'key' => $key,
            'type' => $type,
            'shopId' => $shopId,
        ]);

        return $builder->getQuery()->getOneOrNullResult($resultMode);
    }

    /**
     * Helper function which returns for the passed object type and the passed object alphanumeric value
     * the real identifier of the translatable object.
     * This function is used from every *byNumber function.
     *
     * @param string $number
     * @param string $type
     *
     * @throws \Exception
     *
     * @return int
     */
    protected function getIdByNumber($number, $type)
    {
        switch (strtolower($type)) {
            case self::TYPE_PRODUCT:
                return $this->getProductIdByNumber($number);
            case self::TYPE_VARIANT:
                return $this->getProductVariantIdByNumber($number);
            case self::TYPE_PRODUCT_LINK:
                return $this->getLinkIdByNumber($number);
            case self::TYPE_PRODUCT_DOWNLOAD:
                return $this->getDownloadIdByNumber($number);
            case self::TYPE_PRODUCT_MANUFACTURER:
                return $this->getManufacturerIdByNumber($number);
            case self::TYPE_COUNTRY:
                return $this->getCountryIdByNumber($number);
            case self::TYPE_COUNTRY_STATE:
                return $this->getCountryStateIdByNumber($number);
            case self::TYPE_DISPATCH:
                return $this->getDispatchIdByNumber($number);
            case self::TYPE_PAYMENT:
                return $this->getPaymentIdByNumber($number);
            case self::TYPE_FILTER_SET:
                return $this->getFilterSetIdByNumber($number);
            case self::TYPE_FILTER_GROUP:
                return $this->getFilterGroupIdByNumber($number);
            case self::TYPE_FILTER_OPTION:
                return $this->getFilterOptionIdByNumber($number);
            case self::TYPE_CONFIGURATOR_GROUP:
                return $this->getConfiguratorGroupIdByNumber($number);
            case self::TYPE_CONFIGURATOR_OPTION:
                return $this->getConfiguratorOptionIdByNumber($number);
            default:
                throw new ApiException\CustomValidationException(
                    sprintf('Unknown translation type %s', $type)
                );
        }
    }

    /**
     * Returns the identifier of the product (s_articles.id).
     * The function expects a variant order number as alphanumeric identifier (s_articles_details.ordernumber)
     *
     * @param string $number - Alphanumeric order number of the variant
     *
     * @throws \Exception
     *
     * @return int - Identifier of the product
     */
    protected function getProductIdByNumber($number)
    {
        /** @var Detail|null $entity */
        $entity = $this->findEntityByConditions(
            Detail::class,
            [['number' => $number]]
        );

        if (!$entity) {
            throw new ApiException\NotFoundException(
                sprintf('Variant by order number %s not found', $number)
            );
        }

        return $entity->getArticle()->getId();
    }

    /**
     * Returns the identifier of the product (s_articles_details.id).
     * The function expects a variant order number as alphanumeric identifier (s_articles_details.ordernumber)
     *
     * @param string $number - Alphanumeric order number of the variant
     *
     * @throws \Exception
     *
     * @return int - Identifier of the product
     */
    protected function getProductVariantIdByNumber($number)
    {
        /** @var Detail|null $entity */
        $entity = $this->findEntityByConditions(
            Detail::class,
            [['number' => $number]]
        );

        if (!$entity) {
            throw new ApiException\NotFoundException(
                sprintf('Variant by order number %s not found', $number)
            );
        }

        return $entity->getId();
    }

    /**
     * Would be used to select the primary key of the product links.
     * But the product links have no alphanumeric identifier, so the function
     * throws only an exception.
     *
     * @param string $number
     *
     * @throws ApiException\CustomValidationException
     */
    protected function getLinkIdByNumber($number)
    {
        throw new ApiException\CustomValidationException(
            'Product links can not be found via an alphanumeric key'
        );
    }

    /**
     * Would be used to select the primary key of the product downloads.
     * But the product downloads have no alphanumeric identifier, so the function
     * throws only an exception.
     *
     * @param string $number
     *
     * @throws ApiException\CustomValidationException
     */
    protected function getDownloadIdByNumber($number)
    {
        throw new ApiException\CustomValidationException(
            'Product downloads can not be found via an alphanumeric key'
        );
    }

    /**
     * Returns the primary identifier of the passed alphanumeric manufacturer number.
     *
     * @param string $number
     *
     * @throws \Exception
     *
     * @return int
     */
    protected function getManufacturerIdByNumber($number)
    {
        /** @var Supplier|null $entity */
        $entity = $this->findEntityByConditions(
            Supplier::class,
            [['name' => $number]]
        );

        if (!$entity) {
            throw new ApiException\NotFoundException(
                sprintf('Manufacturer by name %s not found', $number)
            );
        }

        return $entity->getId();
    }

    /**
     * Returns the primary identifier for the passed country name/iso.
     * The passed number can contain a country iso or name.
     *
     * @param string $number
     *
     * @throws \Exception
     *
     * @return int
     */
    protected function getCountryIdByNumber($number)
    {
        /** @var Country|null $country */
        $country = $this->findEntityByConditions(
            Country::class,
            [
                ['name' => $number],
                ['iso' => $number],
            ]
        );

        if (!$country) {
            throw new ApiException\NotFoundException(
                sprintf('Country by iso/name %s not found', $number)
            );
        }

        return $country->getId();
    }

    /**
     * Returns the primary identifier for the passed country state name/short code.
     * The passed number can contain a country state short code or name.
     *
     * @param string $number
     *
     * @throws \Exception
     *
     * @return int
     */
    protected function getCountryStateIdByNumber($number)
    {
        /** @var State|null $entity */
        $entity = $this->findEntityByConditions(
            State::class,
            [
                ['name' => $number],
                ['shortCode' => $number],
            ]
        );

        if (!$entity) {
            throw new ApiException\NotFoundException(
                sprintf('Country state by name/short code %s not found', $number)
            );
        }

        return $entity->getId();
    }

    /**
     * Returns the primary identifier for the passed dispatch name.
     *
     * @param string $number
     *
     * @throws \Exception
     *
     * @return int
     */
    protected function getDispatchIdByNumber($number)
    {
        /** @var Dispatch|null $entity */
        $entity = $this->findEntityByConditions(
            Dispatch::class,
            [
                ['name' => $number],
            ]
        );

        if (!$entity) {
            throw new ApiException\NotFoundException(
                sprintf('Dispatch by name code %s not found', $number)
            );
        }

        return $entity->getId();
    }

    /**
     * Returns the primary identifier for the passed payment name/description.
     *
     * @param string $number
     *
     * @throws \Exception
     *
     * @return int
     */
    protected function getPaymentIdByNumber($number)
    {
        /** @var Payment|null $entity */
        $entity = $this->findEntityByConditions(
            Payment::class,
            [
                ['name' => $number],
                ['description' => $number],
            ]
        );

        if (!$entity) {
            throw new ApiException\NotFoundException(
                sprintf('Payment by name/description code %s not found', $number)
            );
        }

        return $entity->getId();
    }

    /**
     * Returns the primary identifier for the passed filter set name.
     *
     * @param string $number
     *
     * @throws \Exception
     *
     * @return int
     */
    protected function getFilterSetIdByNumber($number)
    {
        /** @var Group|null $entity */
        $entity = $this->findEntityByConditions(
            Group::class,
            [
                ['name' => $number],
            ]
        );

        if (!$entity) {
            throw new ApiException\NotFoundException(
                sprintf('Filter set by name code %s not found', $number)
            );
        }

        return $entity->getId();
    }

    /**
     * Returns the primary identifier for the passed filter group name.
     * To identify the filter group over the name, the function needs
     * a piped separated name with the set and group name.
     * Example:
     *     SET-A|GROUP-A
     *
     * @param string $number
     *
     * @throws \Exception
     *
     * @return int
     */
    protected function getFilterGroupIdByNumber($number)
    {
        $numbers = explode('|', $number);

        if (count($numbers) < 2) {
            throw new ApiException\CustomValidationException(
                sprintf('Passed filter group number %s contains not the full path: set|group', $number)
            );
        }

        /** @var Group|null $set */
        $set = $this->findEntityByConditions(
            Group::class,
            [
                ['name' => $numbers[0]],
            ]
        );

        if (!$set) {
            throw new ApiException\NotFoundException(
                sprintf('Filter set by name code %s not found', $numbers[0])
            );
        }

        /** @var Option|null $group */
        $group = $this->getCollectionElementByProperty(
            $set->getOptions(),
            'name',
            $numbers[1]
        );

        if (!$group) {
            throw new ApiException\NotFoundException(
                sprintf('Filter group by name code %s not found', $numbers[1])
            );
        }

        return $group->getId();
    }

    /**
     * Returns the primary identifier for the passed filter value name.
     * To identify the filter value over the name, the function needs
     * a piped separated name with the set, group and value name.
     * Example:
     *     SET-A|GROUP-A
     *
     * @param string $number
     *
     * @throws \Exception
     *
     * @return int
     */
    protected function getFilterOptionIdByNumber($number)
    {
        $numbers = explode('|', $number);

        if (count($numbers) < 3) {
            throw new ApiException\CustomValidationException(
                sprintf('Passed filter option number %s contains not the full path: set|group|option', $number)
            );
        }

        /** @var Group|null $set */
        $set = $this->findEntityByConditions(
            Group::class,
            [
                ['name' => $numbers[0]],
            ]
        );

        if (!$set) {
            throw new ApiException\NotFoundException(
                sprintf('Filter set by name %s not found', $numbers[0])
            );
        }

        /** @var Option|null $group */
        $group = $this->getCollectionElementByProperty(
            $set->getOptions(),
            'name',
            $numbers[1]
        );

        if (!$group) {
            throw new ApiException\NotFoundException(
                sprintf('Filter group by name %s not found', $numbers[1])
            );
        }

        /** @var Value|null $option */
        $option = $this->getCollectionElementByProperty(
            $group->getValues(),
            'value',
            $numbers[2]
        );

        if (!$option) {
            throw new ApiException\NotFoundException(
                sprintf('Filter option by name %s not found', $numbers[2])
            );
        }

        return $option->getId();
    }

    /**
     * Returns the primary identifier for the passed configurator group name.
     *
     * @param string $number
     *
     * @throws \Exception
     *
     * @return int
     */
    protected function getConfiguratorGroupIdByNumber($number)
    {
        /** @var ConfiguratorGroup|null $entity */
        $entity = $this->findEntityByConditions(
            ConfiguratorGroup::class,
            [['name' => $number]]
        );

        if (!$entity) {
            throw new ApiException\NotFoundException(
                sprintf('Configurator group by name %s not found', $number)
            );
        }

        return $entity->getId();
    }

    /**
     * Returns the primary identifier for the passed configurator option name.
     * To identify the configurator option over the name, the function needs
     * a piped separated name with the group and option name.
     * Example:
     *     GROUP-A|OPTION-A
     *
     * @param string $number
     *
     * @throws ApiException\CustomValidationException
     * @throws \Shopware\Components\Api\Exception\NotFoundException
     *
     * @return int
     */
    protected function getConfiguratorOptionIdByNumber($number)
    {
        $numbers = explode('|', $number);

        if (count($numbers) < 2) {
            throw new ApiException\CustomValidationException(
                sprintf('Passed configurator option name %s contains not the full path: group|option', $number)
            );
        }

        /** @var ConfiguratorGroup|null $group */
        $group = $this->findEntityByConditions(
            ConfiguratorGroup::class,
            [['name' => $numbers[0]]]
        );

        if (!$group) {
            throw new ApiException\NotFoundException(
                sprintf('Configurator group by name %s not found', $numbers[0])
            );
        }

        /** @var ConfiguratorOption|null $option */
        $option = $this->getCollectionElementByProperty(
            $group->getOptions(),
            'name',
            $numbers[1]
        );
        if (!$option) {
            throw new ApiException\NotFoundException(
                sprintf('Configurator option by name %s not found', $numbers[1])
            );
        }

        return $option->getId();
    }

    protected function checkRequirements($data, $checkData = true)
    {
        if (!isset($data['type']) || empty($data['type'])) {
            throw new ApiException\ParameterMissingException(
                'Passed translation contains no object type'
            );
        }

        if (empty($data['shopId'])) {
            throw new ApiException\ParameterMissingException(
                'Passed translation contains no shop id'
            );
        }

        if ($checkData) {
            if (!isset($data['data']) || empty($data['data'])) {
                throw new ApiException\ParameterMissingException(
                    'The parameter data is required for a object translation'
                );
            }

            if (!is_array($data['data'])) {
                throw new ApiException\CustomValidationException(
                    'The parameter data has to be an array.'
                );
            }
        }
    }
}
