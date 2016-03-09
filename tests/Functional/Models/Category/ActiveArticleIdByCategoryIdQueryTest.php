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
class Shopware_Tests_Models_Category_ActiveArticleIdByCategoryIdQueryTest extends Enlight_Components_Test_TestCase
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
        1 => null,
        3 => '2',
        39 => '2',
        5 => '2',
        6 => '96',
        8 => '63',
        9 => '113',
        10 => '2',
        17 => null,
        42 => null,
        43 => '2',
        44 => '116',
        45 => '64',
        46 => '102',
        61 => '2',
        11 => '13',
        14 => '2',
        15 => '37',
        16 => '113',
        19 => '194',
        20 => '197',
        21 => '2',
        22 => '202',
        23 => '206',
        24 => '219',
        25 => '227',
        27 => null,
        30 => '210',
        31 => '134',
        32 => '63',
        33 => '78',
        34 => '96',
        35 => '128',
        36 => '102',
        37 => '157',
        38 => '93',
        47 => '13',
        50 => '2',
        51 => '39',
        52 => '142',
        53 => '116',
        54 => '64',
        55 => '78',
        56 => '225',
        57 => '153',
        58 => '129',
        59 => '102',
        60 => '157',
        62 => null,
        64 => '210',
        65 => '202',
        67 => '2',
        68 => '197',
        69 => '194',
        71 => '227',
        72 => '219',
        73 => '206',
        74 => '244',
        75 => null,
        12 => '13',
        13 => '22',
        48 => '13',
        49 => '22',
    );

    public function testGetActiveArticleIdByCategoryIdReturnsExpectedData()
    {
        foreach ($this->expected as $id => $expected) {
            $data = $this->getRepo()->getActiveArticleIdByCategoryId($id);
            $this->assertEquals($data, $expected);
        }
    }
}
