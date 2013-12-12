<?php

/**
 * Shopware 4.0
 * Copyright © 2013 shopware AG
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

use Shopware\Components\Api\Resource\Translation;

/**
 * @category  Shopware
 * @package   Shopware\Tests
 * @copyright Copyright (c) 2013, shopware AG (http://www.shopware.de)
 */
class Shopware_Tests_Components_Api_TranslationTest extends Shopware_Tests_Components_Api_TestCase
{
    /**
     * @var \Shopware\Components\Api\Resource\Translation
     */
    protected $resource;

    /**
     * @return \Shopware\Components\Api\Resource\Resource
     */
    public function createResource()
    {
        return new Translation();
    }


    protected $translationTypes = array(
        'config_countries',
        'config_dispatch',
        'config_payment',
        'config_country_states',
        'propertyoption',
        'propertygroup',
        'propertyvalue',
        'configuratorgroup',
        'configuratoroption',
        'article',
        'variant',
        'link',
        'download',
        'supplier'
    );

    public function testList()
    {
        $list = $this->resource->getList(
            0, 5
        );
        $this->assertCount(5, $list['data']);

        foreach ($list['data'] as $item) {
            $this->assertArrayHasKey('locale', $item);
        }
    }

    public function testShopTranslations()
    {
        $list = $this->resource->getList(
            0, 5,
            array(
                array('property' => 'translation.shopId', 'value' => 2)
            )
        );
        $shop = $this->getShopWithLocale(2);

        foreach ($list['data'] as $item) {
            $this->assertEquals(2, $item['shopId']);
            $this->assertEquals($shop['locale']['locale'], $item['locale']['locale']);
            $this->assertEquals($shop['locale']['language'], $item['locale']['language']);
            $this->assertEquals($shop['locale']['territory'], $item['locale']['territory']);
        }
    }

    public function testArticleTranslationList()
    {
        $list = $this->resource->getList(0, 5, array(
            array(
                'property' => 'translation.type',
                'value' => \Shopware\Components\Api\Resource\Translation::TYPE_PRODUCT
            )
        ));

        foreach ($list['data'] as $item) {
            $article = Shopware()->Models()->find('Shopware\Models\Article\Article', $item['key']);
            $this->assertInstanceOf('Shopware\Models\Article\Article', $article);
            $this->assertEquals(
                \Shopware\Components\Api\Resource\Translation::TYPE_PRODUCT,
                $item['type']
            );
        }
    }


    public function testSingleArticleTranslation()
    {
        $list = $this->resource->getList(0, 1, array(
            array(
                'property' => 'translation.type',
                'value' => \Shopware\Components\Api\Resource\Translation::TYPE_PRODUCT
            ),
            array(
                'property' => 'translation.key',
                'value' => Shopware()->Db()->fetchOne("SELECT objectkey FROM s_core_translations WHERE objecttype='article' LIMIT 1")
            ),
            array(
                array(
                    'property' => 'translation.shopId',
                    'value' => 2
                )
            )
        ));
        $shop = $this->getShopWithLocale(2);

        $this->assertCount(1, $list['data']);
        $data = $list['data'][0];

        $this->assertEquals(
            \Shopware\Components\Api\Resource\Translation::TYPE_PRODUCT,
            $data['type']
        );

        $this->assertEquals(2, $shop['id']);
        $this->assertArrayHasKey('txtArtikel', $data['data']);
        $this->assertArrayHasKey('txtlangbeschreibung', $data['data']);
    }


    protected function getShopWithLocale($id)
    {
        $builder = Shopware()->Models()->createQueryBuilder();
        $builder->select(array('shop', 'locale'))
            ->from('Shopware\Models\Shop\Shop', 'shop')
            ->leftJoin('shop.locale', 'locale')
            ->andWhere('shop.id = :id')
            ->setParameter('id', $id);

        return $builder->getQuery()->getOneOrNullResult(
            \Doctrine\ORM\AbstractQuery::HYDRATE_ARRAY
        );
    }


    public function testGetOneWithMissingPrivilegeShouldThrowPrivilegeException()
    {
    }

    public function testGetOneWithInvalidIdShouldThrowNotFoundException()
    {
    }

    public function testGetOneWithMissingIdShouldThrowParameterMissingException()
    {
    }

}
