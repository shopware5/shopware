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
use Shopware\Components\Api\BatchInterface;
use Shopware\Components\Api\Exception as ApiException;
use Shopware\Models\Translation\Translation as TranslationModel;
use Shopware\Models\Article\Configurator\Group as ConfiguratorGroup;
use Shopware\Models\Article\Configurator\Option as ConfiguratorOption;
use Shopware\Models\Article\Detail;
use Shopware\Models\Article\Supplier;
use Shopware\Models\Country\State;
use Shopware\Models\Dispatch\Dispatch;
use Shopware\Models\Payment\Payment;
use Shopware\Models\Property\Group;
use Shopware\Models\Property\Option;
use Shopware\Models\Property\Value;

/**
 * Translation API Resource
 *
 * @category  Shopware
 * @package   Shopware\Components\Api\Resource
 * @copyright Copyright (c) shopware AG (http://www.shopware.de)
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

    /** @var \Shopware_Components_Translation $translationWriter */
    protected $translationWriter = null;

    protected $repository = null;

    /**
     * This methods needs to return an ID for the current resource.
     * The ID needs to be the primary ID of the resource (in most cases `id`).
     * If your resource supports other kinds of IDs, too, you should identify
     * your entity by these IDs and return the primary ID of that entity.
     *
     * @param $data
     * @return int|boolean      Return the primary ID of the entity, if it exists
     *                          Return false, if no existing entity matches $data
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

    protected function getRepository()
    {
        if ($this->repository === null) {
            $this->repository = $this->getManager()->getRepository('Shopware\Models\Translation\Translation');
        }
        return $this->repository;
    }

    /**
     * @return \Shopware_Components_Translation
     */
    public function getTranslationComponent()
    {
        if ($this->translationWriter === null) {
            $this->translationWriter = new \Shopware_Components_Translation();
        }
        return $this->translationWriter;
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
     * @param array $criteria
     * @param array $orderBy
     * @return array
     */
    public function getList(
        $offset = 0,
        $limit = 25,
        array $criteria = array(),
        array $orderBy = array())
    {
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

        return array(
            'total' => $paginator->count(),
            'data' => $translations
        );
    }

    /**
     * Helper function which creates the query builder for the getList function.
     *
     * @param int $offset
     * @param int $limit
     * @param array $criteria
     * @param array $orderBy
     * @return \Doctrine\ORM\QueryBuilder|\Shopware\Components\Model\QueryBuilder
     */
    protected function getListQuery($offset = 0, $limit = 25, array $criteria = array(), array $orderBy = array())
    {
        $builder = $this->getManager()->createQueryBuilder();
        $builder->select(array('translation'))
            ->from('Shopware\Models\Translation\Translation', 'translation')
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
     * @param array $data
     * @throws \Shopware\Components\Api\Exception\ParameterMissingException
     * @return TranslationModel
     */
    public function create(array $data)
    {
        $this->checkPrivilege('create');

        $this->checkRequirements($data);

        if (!isset($data['key']) || empty($data['key'])) {
            throw new ApiException\ParameterMissingException(
                "The parameter key is required for a object translation."
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
     * @param array $data
     * @throws \Shopware\Components\Api\Exception\ParameterMissingException
     *
     * @return TranslationModel
     */
    public function createByNumber(array $data)
    {
        $this->checkPrivilege('create');

        if (empty($data['key'])) {
            throw new ApiException\ParameterMissingException(
                "Create by number expects a passed entity number in the key property"
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
     * @param $id int - Identifier of the translated object, like the s_articles.id.
     *
     * @param array $data
     * @throws \Shopware\Components\Api\Exception\ParameterMissingException
     * @return TranslationModel
     */
    public function update($id, array $data)
    {
        $this->checkPrivilege('update');

        if (empty($id)) {
            throw new ApiException\ParameterMissingException(
                "Update expects a passed id"
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
     * @param $number string - Alphanumeric identifier of the translatable entity.
     *                         This can be a article number, configurator group name or some thing else.
     *                         For more information which number fields are supported, look into the #getIdByNumber
     *
     * @param array $data
     *
     * @throws \Shopware\Components\Api\Exception\ParameterMissingException
     * @return TranslationModel
     */
    public function updateByNumber($number, array $data)
    {
        $this->checkPrivilege('update');

        if (empty($number)) {
            throw new ApiException\ParameterMissingException(
                "Create by number expects a passed entity number"
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
     * @param $id
     * @param array $data
     * @throws \Shopware\Components\Api\Exception\NotFoundException
     * @throws \Shopware\Components\Api\Exception\ParameterMissingException
     * @return boolean
     */
    public function delete($id, $data)
    {
        $this->checkPrivilege('delete');

        if (empty($id)) {
            throw new ApiException\ParameterMissingException();
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
                    "No translation found for type %s, shop id %s and foreign key %s",
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
     * @param $number string - Alphanumeric identifier of the translatable entity.
     *                         This can be a article number, configurator group name or some thing else.
     *                         For more information which number fields are supported, look into the #getIdByNumber
     *
     * @param array $data
     * @throws \Shopware\Components\Api\Exception\ParameterMissingException
     * @return boolean
     */
    public function deleteByNumber($number, $data)
    {
        if (empty($number)) {
            throw new ApiException\ParameterMissingException(
                "Delete by number expects a passed entity number"
            );
        }

        $this->checkRequirements($data, false);

        $id = $this->getIdByNumber($number, $data['type']);

        return $this->delete($id, $data);
    }


    /**
     * Helper function which handles the update and create process of translations.
     *
     * @param array $data
     * @throws \Shopware\Components\Api\Exception\CustomValidationException
     * @throws \Shopware\Components\Api\Exception\ParameterMissingException
     * @return null|object
     */
    protected function saveTranslation(array $data)
    {
        $existing = $this->getObjectTranslation(
            $data['type'], //translation object type
            $data['key'], //identifier of the translatable entity (s_articles.id)
            $data['shopId'] //identifier of the shop object
        );

        if (!$existing) {
            $existing = array();
        } else {
            $existing['data'] = unserialize($existing['data']);
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
     * @param string $type - Type of the translatable object, see class constants
     * @param int $key - Identifier of the translatable object. (s_articles.id)
     * @param int $shopId - Identifier of the shop object. (s_core_shops.id)
     * @param int $resultMode - Flag which handles the return value between array and \Shopware\Models\Translation\Translation
     * @return array|TranslationModel
     */
    protected function getObjectTranslation($type, $key, $shopId, $resultMode = AbstractQuery::HYDRATE_ARRAY)
    {
        $builder = $this->getRepository()->createQueryBuilder('translations');
        $builder->setFirstResult(0)
            ->setMaxResults(1);

        $builder->addFilter(array(
            'key' => $key,
            'type' => $type,
            'shopId' => $shopId
        ));

        return $builder->getQuery()->getOneOrNullResult($resultMode);
    }


    /**
     * Helper function which returns for the passed object type and the passed object alphanumeric value
     * the real identifier of the translatable object.
     * This function is used from every *byNumber function.
     *
     * @param $number
     * @param $type
     * @return int
     * @throws \Exception
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
                    sprintf("Unknown translation type %s", $type)
                );
        }
    }

    /**
     * Returns the identifier of the product (s_articles.id).
     * The function expects a variant order number as alphanumeric identifier (s_articles_details.ordernumber)
     *
     * @param $number - Alphanumeric order number of the variant.
     * @return int - Identifier of the article.
     * @throws \Exception
     */
    protected function getProductIdByNumber($number)
    {
        /**@var $entity Detail */
        $entity = $this->findEntityByConditions(
            'Shopware\Models\Article\Detail',
            array(array('number' => $number))
        );

        if (!$entity) {
            throw new ApiException\NotFoundException(
                sprintf("Variant by order number %s not found", $number)
            );
        }

        return $entity->getArticle()->getId();
    }


    /**
     * Returns the identifier of the product (s_articles_details.id).
     * The function expects a variant order number as alphanumeric identifier (s_articles_details.ordernumber)
     *
     * @param $number - Alphanumeric order number of the variant.
     * @return int - Identifier of the article.
     * @throws \Exception
     */
    protected function getProductVariantIdByNumber($number)
    {
        /**@var $entity Detail */
        $entity = $this->findEntityByConditions(
            'Shopware\Models\Article\Detail',
            array(array('number' => $number))
        );

        if (!$entity) {
            throw new ApiException\NotFoundException(
                sprintf("Variant by order number %s not found", $number)
            );
        }

        return $entity->getId();
    }


    /**
     * Would be used to select the primary key of the article links.
     * But the article links have no alphanumeric identifier, so the function
     * throws only an exception.
     *
     * @param $number
     * @throws ApiException\CustomValidationException
     */
    protected function getLinkIdByNumber($number)
    {
        throw new ApiException\CustomValidationException(
            "Article links can not be found via an alphanumeric key"
        );
    }

    /**
     * Would be used to select the primary key of the article downloads.
     * But the article downloads have no alphanumeric identifier, so the function
     * throws only an exception.
     *
     * @param $number
     * @throws ApiException\CustomValidationException
     */
    protected function getDownloadIdByNumber($number)
    {
        throw new ApiException\CustomValidationException(
            "Article downloads can not be found via an alphanumeric key"
        );
    }


    /**
     * Returns the primary identifier of the passed alphanumeric manufacturer number.
     *
     * @param $number
     * @return int
     * @throws \Exception
     */
    protected function getManufacturerIdByNumber($number)
    {
        /**@var $entity Supplier */
        $entity = $this->findEntityByConditions(
            'Shopware\Models\Article\Supplier',
            array(array('name' => $number))
        );

        if (!$entity) {
            throw new ApiException\NotFoundException(
                sprintf("Manufacturer by name %s not found", $number)
            );
        }
        return $entity->getId();
    }


    /**
     * Returns the primary identifier for the passed country name/iso.
     * The passed number can contain a country iso or name.
     * @param $number
     * @return int
     * @throws \Exception
     */
    protected function getCountryIdByNumber($number)
    {
        /**@var $entity Country */
        $entity = $this->findEntityByConditions(
            'Shopware\Models\Country\Country',
            array(
                array('name' => $number),
                array('iso' => $number)
            )
        );

        if (!$entity) {
            throw new ApiException\NotFoundException(
                sprintf("Country by iso/name %s not found", $number)
            );
        }

        return $entity->getId();
    }

    /**
     * Returns the primary identifier for the passed country state name/short code.
     * The passed number can contain a country state short code or name.
     *
     * @param $number
     * @return int
     * @throws \Exception
     */
    protected function getCountryStateIdByNumber($number)
    {
        /**@var $entity State */
        $entity = $this->findEntityByConditions(
            'Shopware\Models\Country\State',
            array(
                array('name' => $number),
                array('shortCode' => $number)
            )
        );

        if (!$entity) {
            throw new ApiException\NotFoundException(
                sprintf("Country state by name/short code %s not found", $number)
            );
        }

        return $entity->getId();
    }


    /**
     * Returns the primary identifier for the passed dispatch name.
     *
     * @param $number
     * @return int
     * @throws \Exception
     */
    protected function getDispatchIdByNumber($number)
    {
        /**@var $entity Dispatch */
        $entity = $this->findEntityByConditions(
            'Shopware\Models\Dispatch\Dispatch',
            array(
                array('name' => $number),
            )
        );

        if (!$entity) {
            throw new ApiException\NotFoundException(
                sprintf("Dispatch by name code %s not found", $number)
            );
        }

        return $entity->getId();
    }

    /**
     * Returns the primary identifier for the passed payment name/description.
     *
     * @param $number
     * @return int
     * @throws \Exception
     */
    protected function getPaymentIdByNumber($number)
    {
        /**@var $entity Payment */
        $entity = $this->findEntityByConditions(
            'Shopware\Models\Payment\Payment',
            array(
                array('name' => $number),
                array('description' => $number)
            )
        );

        if (!$entity) {
            throw new ApiException\NotFoundException(
                sprintf("Payment by name/description code %s not found", $number)
            );
        }

        return $entity->getId();
    }

    /**
     * Returns the primary identifier for the passed filter set name.
     *
     * @param $number
     * @return int
     * @throws \Exception
     */
    protected function getFilterSetIdByNumber($number)
    {
        /**@var $entity Group */
        $entity = $this->findEntityByConditions(
            'Shopware\Models\Property\Group',
            array(
                array('name' => $number)
            )
        );

        if (!$entity) {
            throw new ApiException\NotFoundException(
                sprintf("Filter set by name code %s not found", $number)
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
     *
     * @param $number
     * @return int
     * @throws \Exception
     */
    protected function getFilterGroupIdByNumber($number)
    {
        $numbers = explode('|', $number);

        if (count($numbers) < 2) {
            throw new ApiException\CustomValidationException(
                sprintf("Passed filter group number %s contains not the full path: set|group", $number)
            );
        }


        /**@var $set Group */
        $set = $this->findEntityByConditions(
            'Shopware\Models\Property\Group',
            array(
                array('name' => $numbers[0])
            )
        );

        if (!$set) {
            throw new ApiException\NotFoundException(
                sprintf("Filter set by name code %s not found", $numbers[0])
            );
        }

        /**@var $group Option */
        $group = $this->getCollectionElementByProperty(
            $set->getOptions(),
            'name',
            $numbers[1]
        );


        if (!$group) {
            throw new ApiException\NotFoundException(
                sprintf("Filter group by name code %s not found", $numbers[1])
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
     *
     * @param $number
     * @return int
     * @throws \Exception
     */
    protected function getFilterOptionIdByNumber($number)
    {
        $numbers = explode('|', $number);

        if (count($numbers) < 3) {
            throw new ApiException\CustomValidationException(
                sprintf("Passed filter option number %s contains not the full path: set|group|option", $number)
            );
        }

        /**@var $set Group */
        $set = $this->findEntityByConditions(
            'Shopware\Models\Property\Group',
            array(
                array('name' => $numbers[0])
            )
        );

        if (!$set) {
            throw new ApiException\NotFoundException(
                sprintf("Filter set by name %s not found", $numbers[0])
            );
        }

        /**@var $group Option */
        $group = $this->getCollectionElementByProperty(
            $set->getOptions(),
            'name',
            $numbers[1]
        );


        if (!$group) {
            throw new ApiException\NotFoundException(
                sprintf("Filter group by name %s not found", $numbers[1])
            );
        }

        /**@var $option Value */
        $option = $this->getCollectionElementByProperty(
            $group->getValues(),
            'value',
            $numbers[2]
        );

        if (!$option) {
            throw new ApiException\NotFoundException(
                sprintf("Filter option by name %s not found", $numbers[2])
            );
        }

        return $option->getId();
    }

    /**
     * Returns the primary identifier for the passed configurator group name.
     *
     * @param $number
     * @return int
     * @throws \Exception
     */
    protected function getConfiguratorGroupIdByNumber($number)
    {
        /**@var $entity ConfiguratorGroup */
        $entity = $this->findEntityByConditions(
            'Shopware\Models\Article\Configurator\Group',
            array(array('name' => $number))
        );

        if (!$entity) {
            throw new ApiException\NotFoundException(
                sprintf("Configurator group by name %s not found", $number)
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
     * @param $number
     * @throws \Shopware\Components\Api\Exception\CustomValidationException
     * @throws \Shopware\Components\Api\Exception\NotFoundException
     * @return int
     */
    protected function getConfiguratorOptionIdByNumber($number)
    {
        $numbers = explode('|', $number);

        if (count($numbers) < 2) {
            throw new ApiException\CustomValidationException(
                sprintf("Passed configurator option name %s contains not the full path: group|option", $number)
            );
        }

        /**@var $group ConfiguratorGroup */
        $group = $this->findEntityByConditions(
            'Shopware\Models\Article\Configurator\Group',
            array(array('name' => $numbers[0]))
        );

        if (!$group) {
            throw new ApiException\NotFoundException(
                sprintf("Configurator group by name %s not found", $numbers[0])
            );
        }

        /**@var $option ConfiguratorOption */
        $option = $this->getCollectionElementByProperty(
            $group->getOptions(),
            'name',
            $numbers[1]
        );
        if (!$option) {
            throw new ApiException\NotFoundException(
                sprintf("Configurator option by name %s not found", $numbers[1])
            );
        }

        return $option->getId();
    }


    protected function checkRequirements($data, $checkData = true)
    {
        if (!isset($data['type']) || empty($data['type'])) {
            throw new ApiException\ParameterMissingException(
                "Passed translation contains no object type"
            );
        }

        if (empty($data['shopId'])) {
            throw new ApiException\ParameterMissingException(
                "Passed translation contains no shop id"
            );
        }

        if ($checkData) {
            if (!isset($data['data']) || empty($data['data'])) {
                throw new ApiException\ParameterMissingException(
                    "The parameter data is required for a object translation"
                );
            }

            if (!is_array($data['data'])) {
                throw new ApiException\CustomValidationException(
                    "The parameter data has to be an array."
                );
            }
        }
    }
}
