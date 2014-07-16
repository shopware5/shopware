<?php
/**
 * Shopware 4.0
 * Copyright © 2012 shopware AG
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
 * @category  Shopware
 * @package   Shopware\Tests
 * @copyright Copyright (c) 2012, shopware AG (http://www.shopware.de)
 */
class Shopware_RegressionTests_Ticket5052 extends Enlight_Components_Test_Controller_TestCase
{

    protected $articles = array(
        //Artikel mit Standardkonfigurator
        202 => array(
            'id' => 202,
            'categories' => array(
                array('id' => 22, 'name' => 'Deutsch>Beispiele>Konfiguratorartikel'),
                array('id' => 65, 'name' => 'English>Examples>Configurator articles')
            ),
            'configuratorSet' => array(
                'id' => 25,
                'name' => 'Set-SW10201',
                'groups' => 2,
                'options' => 10
            ),
            'images' => array(
                array('id' => 484, 'path' => 'Sandale-Beach-blau5034a27da447a'),
                array('id' => 485, 'path' => 'Sandale-Beach-braun5034a281417e3'),
                array('id' => 486, 'path' => 'Sandale-Beach-pink5034a28440b0f')
            )
        ),

        //Artikel mit Emailbenachrichtigung
        243 => array(
            'id' => 243,
            'configuratorSet' => array(),
            'categories' => array(
                array('id' => 20, 'name' => 'Deutsch>Beispiele>Darstellung'),
                array('id' => 68, 'name' => 'English>Examples>Presentation'),
            ),
            'images' => array(
                array('id' => 593, 'path' => 'Koffer-rot-gruen-gelb-blau503f1b16660bd')
            )
        ),

        //Staffelpreise
        209 => array(
            'id' => 209,
            'configuratorSet' => array(),
            'categories' => array(
                array('id' => 23, 'name' => 'Deutsch>Beispiele>Preisgestaltung'),
                array('id' => 73, 'name' => 'English>Examples>Price strategies'),
            ),
            'images' => array(
                array('id' => 523, 'path' => 'Glas_Muensterlaender_Aperitif_Imagefoto5034e02761d58')
            )
        ),

        //Cigar Special 40%
        6 => array(
            'id' => 6,
            'configuratorSet' => array(),
            'categories' => array(
                array('id' => 14, 'name' => 'Deutsch>Genusswelten>Edelbrände'),
                array('id' => 21, 'name' => 'Deutsch>Beispiele>Produktvergleiche & Filter'),
                array('id' => 50, 'name' => 'English>Worlds of indulgence>Brandies'),
            ),
            'images' => array(
                array('id' => 14, 'path' => 'Cigar_Special')
            )
        ),

    );

    public function testArticleDetail()
    {
        Shopware()->Plugins()->Backend()->Auth()->setNoAuth();

        foreach($this->articles as $articleId => $article) {
            // Request a variant that is not the default one
            $this->Request()->setMethod('GET');
            $this->dispatch('backend/Article/getArticle?articleId=' . $articleId);
            $data = $this->View()->getAssign('data');
            $this->checkArticle($data[0], $article);
        }
    }

    protected function checkArticle($data, $article) {
        $this->checkCategories($data, $article);
        $this->checkConfiguratorSet($data, $article);
        $this->checkImages($data, $article);
    }

    protected function checkImages($data, $article) {
        $this->assertCount(count($article['images']), $data['images']);

        for($i=0; $i<=count($article['images'])-1;$i++) {
            $image = $data['images'][$i];
            $expected = $article['images'][$i];

            $this->assertEquals($expected['id'], $image['id'] , 'Image id not matched  article =>' . $article['id']);
            $this->assertEquals($expected['path'], $image['path'] , 'Image id not matched  article =>' . $article['id']);
        }
    }

    protected function checkConfiguratorSet($data, $article) {
        if (empty($article['configuratorSet'])) {
            $this->assertTrue(empty($data['configuratorSet']));
            return;
        }
        $set = $data['configuratorSet'];
        $expected = $article['configuratorSet'];

        $this->assertEquals($set['id'], $expected['id'], 'Configurator set id not matched  article =>' . $article['id']);
        $this->assertEquals($set['name'], $expected['name'], 'Configurator set name not matched  article =>' . $article['id']);
        $this->assertCount($expected['groups'], $set['groups']);
        $this->assertCount($expected['options'], $set['options']);
    }

    protected function checkCategories($data, $article) {
        $this->assertCount(count($article['categories']), $data['categories'], 'Categories count not match   article =>' . $article['id']);

        for($i=0; $i<=count($article['categories'])-1;$i++) {
            $expected = $article['categories'][$i];
            $category = $data['categories'][$i];

            $this->assertEquals($expected['path'], $category['path'], 'Category path not match   article =>' . $article['id']);
            $this->assertEquals($expected['id'], $category['id'], 'Category id not match   article =>' . $article['id']);
        }
    }
}
