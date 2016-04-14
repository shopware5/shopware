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

namespace Shopware\Tests\Components;

use Shopware\Components\NumberRangeManager;

/**
 * @category  Shopware
 * @package   Shopware\Tests\Components
 * @copyright Copyright (c) shopware AG (http://www.shopware.de)
 */
class NumberRangeManagerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Shopware\Components\Model\ModelManager
     */
    public $em;

    /**
     * @var \Zend_Db_Adapter_Pdo_Abstract
     */
    public $db;

    public function setUp()
    {
        parent::setUp();

        $this->em = Shopware()->Models();
        $this->db = Shopware()->Db();
    }

    public function testGetCurrentNumber()
    {
        // Fetch actual number from DB
        $rangeName = 'invoice';
        $expectedNumber = $this->db->fetchOne(
           'SELECT number
            FROM s_order_number
            WHERE name = ?',
            array(
                $rangeName
            )
        );

        $manager = new NumberRangeManager($this->em);

        $currentNumber = $manager->getCurrentNumber($rangeName);

        $this->assertEquals($expectedNumber, $currentNumber);
    }

    public function testGetNextNumber()
    {
        $manager = new NumberRangeManager($this->em);

        $rangeName = 'invoice';
        $currentNumber = $manager->getCurrentNumber($rangeName);
        $nextNumber = $manager->getNextNumber($rangeName);

        $this->assertEquals(($currentNumber + 1), $nextNumber);
    }
}
