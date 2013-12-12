<?php
/**
 * Shopware 4
 * Copyright © shopware AG
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

use Shopware\Components\Api\Exception as ApiException;

/**
 * Translation API Resource
 *
 * @category  Shopware
 * @package   Shopware\Components\Api\Resource
 * @copyright Copyright (c) shopware AG (http://www.shopware.de)
 */
class Translation extends Resource
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
            $translation['data'] = unserialize($translation['data']);
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
        $builder->select(array('translation', 'locale'))
            ->from('Shopware\Models\Translation\Translation', 'translation')
            ->leftJoin('translation.locale', 'locale');

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
}
