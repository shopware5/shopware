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

    public function testLocaleTranslations()
    {
        $list = $this->resource->getList(
            0, 5,
            array(
                array('property' => 'translation.localeId', 'value' => 2)
            )
        );
        $shop = $this->getShopWithLocale(2);

        foreach ($list['data'] as $item) {
            $this->assertEquals(2, $item['localeId']);
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
                    'property' => 'translation.localeId',
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

        $this->assertEquals(2, $shop['locale']['id']);

        $this->assertArrayHasKey('txtArtikel', $data['data']);
        $this->assertArrayHasKey('txtlangbeschreibung', $data['data']);
    }

    public function testCreateArticle()
    {
        $data = $this->getDummyData('article');

        /**@var $translation \Shopware\Models\Translation\Translation */
        $translation = $this->resource->create($data);

        $this->assertInstanceOf('Shopware\Models\Translation\Translation', $translation);
        $this->assertEquals(
            $data['key'],
            $translation->getKey(),
            'Translation key do not match'
        );
        $this->assertEquals(
            $data['type'],
            $translation->getType(),
            'Translation type do not match'
        );
        $this->assertEquals(
            $data['data'],
            unserialize($translation->getData()),
            'Translation data do not match'
        );

        return $translation->getKey();
    }

    public function testCreateArticleByNumber()
    {
        $data = $this->getDummyData('article');
        $article = Shopware()->Db()->fetchRow("SELECT ordernumber, articleID FROM s_articles_details LIMIT 1");
        $data['key'] = $article['ordernumber'];

        /**@var $translation \Shopware\Models\Translation\Translation */
        $translation = $this->resource->createByNumber($data['key'], $data);

        $this->assertInstanceOf('Shopware\Models\Translation\Translation', $translation);

        $this->assertEquals(
            $article['articleID'],
            $translation->getKey(),
            'Translation key do not match'
        );

        $this->assertEquals(
            $data['type'],
            $translation->getType(),
            'Translation type do not match'
        );
        $this->assertEquals(
            $data['data'],
            unserialize($translation->getData()),
            'Translation data do not match'
        );
    }

    /**
     * @depends testCreateArticle
     */
    public function testArticleUpdateOverride($key)
    {
        $this->resource->setResultMode(2);
        $translation = $this->resource->getList(0, 1, array(
            array('property' => 'translation.type',     'value' => 'article'),
            array('property' => 'translation.key',      'value' => $key),
            array('property' => 'translation.localeId', 'value' => 2)
        ));

        $translation = $translation['data'][0];

        foreach($translation['data'] as &$fieldTranslation) {
            $fieldTranslation = 'UPDATE - ' . $fieldTranslation;
        }

        $updated = $this->resource->update($key, $translation);

        $this->assertEquals(
            $translation['key'],
            $updated->getKey(),
            'Translation key do not match'
        );
        $this->assertEquals(
            $translation['type'],
            $updated->getType(),
            'Translation type do not match'
        );

        $this->assertEquals(
            $translation['data'],
            unserialize($updated->getData()),
            'Translation data do not match'
        );

        return $key;
    }

    /**
     * @depends testArticleUpdateOverride
     */
    public function testArticleUpdateMerge($key)
    {
        $this->resource->setResultMode(2);
        $translation = $this->resource->getList(0, 1, array(
            array('property' => 'translation.type',     'value' => 'article'),
            array('property' => 'translation.key',      'value' => $key),
            array('property' => 'translation.localeId', 'value' => 2)
        ));

        $translation = $translation['data'][0];
        $translation['data'] = array(
            'txtArtikel' => 'Update-2'
        );

        $updated = $this->resource->update($key, $translation);

        $this->assertEquals(
            $translation['key'],
            $updated->getKey(),
            'Translation key do not match'
        );
        $this->assertEquals(
            $translation['type'],
            $updated->getType(),
            'Translation type do not match'
        );


        $dataTranslation = unserialize($updated->getData());
        $this->assertEquals(
            $translation['data']['txtArtikel'],
            $dataTranslation['txtArtikel']
        );

        $this->assertEquals(
            'UPDATE - Dummy Translation',
            $dataTranslation['txtlangbeschreibung']
        );
    }

    public function testRecursiveMerge()
    {
        $create = $this->getDummyData('article');

        $create['type'] = 'recursive';
        $create['data'] = array(
            'a1' => 'create',
            'b1' => array(
                'a2' => 'create',
                'b2' => array(
                    'a3' => 'create',
                    'b3' => array(
                        'a4' => 'create'
                    )
                )
            )
        );

        $created = $this->resource->create($create);

        $update = $create;
        $update['data'] = array(
            'a1' => 'update',
            'b1' => array(
                'a2' => 'update',
                'b2' => array(
                    'a3' => 'update',
                )
            )
        );

        $updated = $this->resource->update($created->getKey(), $update);

        $updateData = $update['data'];
        $updatedData = unserialize($updated->getData());

        $this->assertEquals(
            $updateData['a1'],
            $updatedData['a1'],
            'First level not updated'
        );

        $this->assertEquals(
            $updateData['b1']['a2'],
            $updatedData['b1']['a2'],
            'Second level not updated'
        );

        $this->assertEquals(
            $updateData['b1']['b2']['a3'],
            $updatedData['b1']['b2']['a3'],
            'Third level not updated'
        );

        $this->assertEquals(
            $create['data']['b1']['b2']['b3']['a4'],
            $updatedData['b1']['b2']['b3']['a4'],
            'Fourth level not updated'
        );
    }

    protected function getDummyData($type, $localeId = 2)
    {
        return array(
            'type' => $type,
            'key' => rand(2000, 10000),
            'data' => $this->getTypeFields($type),
            'localeId' => $localeId
        );
    }

    protected function getTypeFields($type)
    {
        switch (strtolower($type)) {
            case 'article':
                return array(
                    'txtArtikel' => 'Dummy Translation',
                    'txtshortdescription' => 'Dummy Translation',
                    'txtlangbeschreibung' => 'Dummy Translation',
                    'txtzusatztxt' => 'Dummy Translation',
                    'txtkeywords' => 'Dummy Translation',
                    'txtpackunit' => 'Dummy Translation',
                );
            case 'variant':
                return array(
                    'txtzusatztxt' => 'Dummy Translation',
                    'txtpackunit' => 'Dummy Translation',
                );
            case 'link':
                return array(
                    'linkname' => 'Dummy Translation'
                );
            case 'download':
                return array(
                    'downloadname' => 'Dummy Translation'
                );
            case 'config_countries':
                return array(
                    'countryname' => 'Dummy Translation',
                    'notice' => 'Dummy Translation',
                );
            case 'config_units':
                return array(
                    'description' => 'Dummy Translation',
                );
            case 'config_dispatch':
                return array(
                    'dispatch_name' => 'Dummy Translation',
                    'dispatch_status_link' => 'Dummy Translation',
                    'dispatch_description' => 'Dummy Translation',
                );
            default:
                return array(
                    'name' => 'Dummy Translation',
                    'description' => 'Dummy Translation',
                    'link' => 'Dummy Translation',
                );
        }
    }


    protected function getDummyArticle()
    {
        return array(
            'type' => 'article',
            'key' => rand(2000, 10000),
            'data' => array(
                'txtArtikel' => 'Dummy translation',
                'txtlangbeschreibung' => 'Dummy translation'
            ),
            'localeId' => 2
        );
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

    /**
     * @group disable
     */
    public function testGetOneWithMissingPrivilegeShouldThrowPrivilegeException()
    {
    }

    /**
     * @group disable
     */
    public function testGetOneWithInvalidIdShouldThrowNotFoundException()
    {
    }

    /**
     * @group disable
     */
    public function testGetOneWithMissingIdShouldThrowParameterMissingException()
    {
    }

}
