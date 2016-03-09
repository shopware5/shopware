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
 * @group disable
 * @category  Shopware
 * @package   Shopware\Tests
 * @copyright Copyright (c) 2012, shopware AG (http://www.shopware.de)
 */
class Shopware_Tests_Models_Category_BlogCategoriesByParentQueryTest extends Enlight_Components_Test_TestCase
{
    /**
     * @var \Shopware\Models\Category\Repository
     */
    protected $repo = null;

    /**
     * @return Shopware\Models\Category\Repository
     */
    protected function getRepo()
    {
        if ($this->repo === null) {
            $this->repo = Shopware()->Models()->Category();
        }
        return $this->repo;
    }

    protected $expected = array(
        1 => array(),
        3 => array(
            0 => array(
                'id' => 17,
                'parentId' => 3,
                'name' => 'Trends + News',
                'position' => 3,
                                'metaKeywords' => null,
                'metaDescription' => '',
                'cmsHeadline' => 'Blogfunktion',
                'active' => true,
                'template' => '',
                'blog' => true,
                'external' => '',
                'hideFilter' => false,
                'hideTop' => false,
                'noViewSelect' => false,
                'emotions' => null,
                'articles' => null,
            ),
        ),
        39 => array(
            0 => array(
                'id' => 42,
                'parentId' => 39,
                'name' => 'Trends + News',
                'position' => 0,
                                'metaKeywords' => null,
                'metaDescription' => '',
                'cmsHeadline' => '',
                'active' => true,
                'template' => null,
                'blog' => true,
                'external' => '',
                'hideFilter' => false,
                'hideTop' => false,
                'noViewSelect' => false,
                'emotions' => null,
                'articles' => null,
            ),
        ),
        5 => array(),
        6 => array(),
        8 => array(),
        9 => array(),
        10 => array(),
        17 => array(),
        42 => array(),
        43 => array(),
        44 => array(),
        45 => array(),
        46 => array(),
        61 => array(),
        11 => array(),
        14 => array(),
        15 => array(),
        16 => array(),
        19 => array(),
        20 => array(),
        21 => array(),
        22 => array(),
        23 => array(),
        24 => array(),
        25 => array(),
        27 => array(),
        30 => array(),
        31 => array(),
        32 => array(),
        33 => array(),
        34 => array(),
        35 => array(),
        36 => array(),
        37 => array(),
        38 => array(),
        47 => array(),
        50 => array(),
        51 => array(),
        52 => array(),
        53 => array(),
        54 => array(),
        55 => array(),
        56 => array(),
        57 => array(),
        58 => array(),
        59 => array(),
        60 => array(),
        62 => array(),
        64 => array(),
        65 => array(),
        67 => array(),
        68 => array(),
        69 => array(),
        71 => array(),
        72 => array(),
        73 => array(),
        74 => array(),
        75 => array(),
        12 => array(),
        13 => array(),
        48 => array(),
        49 => array(),
    );

    public function testQuery()
    {
        foreach ($this->expected as $id => $expected) {
            $query = $this->getRepo()->getBlogCategoriesByParentQuery($id);
            $data = $this->removeDates($query->getArrayResult());
            $this->assertEquals($data, $expected);
        }
    }

    protected function removeDates($data)
    {
        foreach ($data as &$subCategory) {
            unset($subCategory['changed']);
            unset($subCategory['cmsText']);
            unset($subCategory['added']);
            foreach ($subCategory['emotions'] as &$emotion) {
                unset($emotion['createDate']);
                unset($emotion['modified']);
            }
            foreach ($subCategory['articles'] as &$article) {
                unset($article['added']);
                unset($article['changed']);
                unset($article['mainDetail']['releaseDate']);
            }
        }
        return $data;
    }
}
