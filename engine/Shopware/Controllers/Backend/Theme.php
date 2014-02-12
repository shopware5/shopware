<?php

use Doctrine\Common\Collections\ArrayCollection;
use Shopware\Models\Shop\Shop;
use Shopware\Models\Shop\Template;

class Shopware_Controllers_Backend_Theme extends Shopware_Controllers_Backend_Application
{
    /**
     * Model which handled through this controller
     * @var string
     */
    protected $model = 'Shopware\Models\Shop\Template';

    /**
     * SQL alias for the internal query builder
     * @var string
     */
    protected $alias = 'template';

    /**
     * Controller action which called to assign a shop template.
     */
    public function assignAction()
    {
        $this->View()->assign(
            $this->assign(
                $this->Request()->getParam('shopId', null),
                $this->Request()->getParam('themeId', null)
            )
        );
    }

    /**
     * Override of the application controller
     * to trigger the theme and template registration when the
     * list should be displayed.
     */
    public function listAction()
    {
        $this->container->get('theme_manager')->registerTemplates();
        $this->container->get('theme_manager')->registerThemes();

        parent::listAction();
    }

    /**
     * @param $id
     * @return \Doctrine\ORM\QueryBuilder|\Shopware\Components\Model\QueryBuilder
     */
    protected function getDetailQuery($id)
    {
        $builder = parent::getDetailQuery($id);
        $builder->addSelect(array(
            'elements',
            'values'
        ))
            ->leftJoin('template.elements', 'elements')
            ->leftJoin('elements.values', 'values', 'WITH', 'values.shopId = :shopId')
            ->orderBy('elements.position')
            ->addOrderBy('elements.name')
            ->setParameter('shopId', 1);

        return $builder;
    }

    /**
     * Saves the passed theme configuration.
     *
     * @param $data
     * @return array|void
     */
    public function save($data)
    {
        $theme = $this->getRepository()->find($data['id']);

        /**@var $theme Template */
        foreach ($data['elements'] as $elementData) {
            $element = $this->getElementByName(
                $theme->getElements(),
                $elementData['name']
            );

            if (!($element instanceof Template\ConfigElement)) {
                continue;
            }

            foreach ($elementData['values'] as $valueData) {
                $value = $this->getElementShopValue(
                    $element->getValues(),
                    $valueData['shopId']
                );

                $shop = $this->getManager()->getReference(
                    'Shopware\Models\Shop\Shop',
                    $valueData['shopId']
                );

                $value->setShop($shop);
                $value->setElement($element);

                $value->setValue($valueData['value']);
            }
        }


        $this->getManager()->flush();
    }

    /**
     * Helper function to get the theme configuration value of the passed
     * value collection.
     * If no shop value exist, the function creates a new value object.
     *
     * @param ArrayCollection $collection
     * @param $shopId
     * @return Template\ConfigValue
     */
    private function getElementShopValue(ArrayCollection $collection, $shopId)
    {
        /**@var $value Template\ConfigValue */
        foreach ($collection as $value) {
            if ($value->getShop()->getId() == $shopId) {
                return $value;
            }
        }
        $value = new Template\ConfigValue();
        $collection->add($value);
        return $value;
    }

    /**
     * The getList function returns an array of the configured class model.
     * The listing query created in the getListQuery function.
     * The pagination of the listing is handled inside this function.
     *
     * @param int $offset
     * @param int $limit
     * @param array $sort Contains an array of Ext JS sort conditions
     * @param array $filter Contains an array of Ext JS filters
     * @param array $wholeParams Contains all passed request parameters
     * @return array
     */
    protected function getList($offset, $limit, $sort = array(), $filter = array(), array $wholeParams = array())
    {
        if (!isset($wholeParams['shopId'])) {
            $wholeParams['shopId'] = $this->getDefaultShopId();
        }

        $data = parent::getList(null, null, $sort, $filter, $wholeParams);

        $template = $this->getShopTemplate($wholeParams['shopId']);

        if (!$template instanceof Template) {
            return $data;
        }

        foreach ($data['data'] as &$theme) {
            $instance = $this->getRepository()->find($theme['id']);

            if ($theme['version'] < 3) {
                $theme['screen'] = $this->container->get('theme_manager')->getTemplateImage($instance);
            } else {
                $theme['screen'] = $this->container->get('theme_manager')->getThemeImage($instance);
            }
            $theme['enabled'] = ($theme['id'] === $template->getId());
        }

        return $data;
    }

    /**
     * @return \Shopware\Components\Model\QueryBuilder
     */
    protected function getListQuery()
    {
        $builder = parent::getListQuery();
        $builder->addSelect('elements')
            ->leftJoin('template.elements', 'elements');

        return $builder;
    }


    /**
     * Assigns the passed theme (identified over the primary key)
     * to the passed shop (identified over the shop primary key)
     *
     * @param $shopId
     * @param $themeId
     * @return array
     */
    protected function assign($shopId, $themeId)
    {
        /**@var $shop Shop */
        $shop = $this->getManager()->find('Shopware\Models\Shop\Shop', $shopId);

        /**@var $theme Template */
        $theme = $this->getManager()->find('Shopware\Models\Shop\Template', $themeId);

        $shop->setTemplate($theme);

        $this->getManager()->flush();

        return array('success' => true);
    }

    protected function getShopTemplate($shopId)
    {
        $builder = $this->getRepository()->createQueryBuilder('template');
        $builder->innerJoin('template.shops', 'shops')
            ->where('shops.id = :shopId')
            ->setParameter('shopId', $shopId);

        return $builder->getQuery()->getOneOrNullResult(
            \Doctrine\ORM\AbstractQuery::HYDRATE_OBJECT
        );
    }

    private function getDefaultShopId()
    {
        return Shopware()->Db()->fetchOne(
            'SELECT id FROM s_core_shops WHERE `default` = 1'
        );
    }

    /**
     * Helper function which checks if the element name is already exists in the
     * passed collection of config elements.
     *
     * @param $collection
     * @param $name
     * @return null|Template\ConfigElement
     */
    private function getElementByName($collection, $name)
    {
        /**@var $element Template\ConfigElement */
        foreach ($collection as $element) {
            if ($element->getName() == $name) {
                return $element;
            }
        }
        return null;
    }
}