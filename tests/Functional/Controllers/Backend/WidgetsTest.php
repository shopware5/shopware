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
 * @category  Shopware
 *
 * @copyright Copyright (c) shopware AG (http://www.shopware.de)
 */
class WidgetsTest extends Enlight_Components_Test_Controller_TestCase
{
    public function setUp()
    {
        parent::setUp();
        Shopware()->Plugins()->Backend()->Auth()->setNoAuth();
        Shopware()->Plugins()->Backend()->Auth()->setNoAcl();

        Shopware()->Container()->get('dbal_connection')->beginTransaction();
        Shopware()->Db()->exec('DELETE FROM s_statistics_visitors');
        Shopware()->Db()->exec('DELETE FROM s_order');
        Shopware()->Db()->exec('DELETE FROM s_user');
    }

    protected function tearDown()
    {
        parent::tearDown();

        Shopware()->Container()->get('dbal_connection')->rollBack();
    }

    public function testConversionIsEmpty()
    {
        $this->dispatch('backend/widgets/getTurnOverVisitors');

        $response = $this->View()->getAssign();

        $this->assertTrue($response['success']);
        $this->assertEquals('0.00', $response['conversion']);
    }

    public function testConversionIsCalucatedFromBeginningOfDay()
    {
        $date = new DateTime();
        $date->sub(new DateInterval('P7DT1M'));

        Shopware()->Container()->get('dbal_connection')
            ->insert('s_statistics_visitors', [
                'shopID' => 1,
                'datum' => $date->format('Y-m-d'),
                'pageimpressions' => 1,
                'uniquevisits' => 1,
                'deviceType' => 'desktop',
            ]);

        Shopware()->Container()->get('dbal_connection')
            ->insert('s_order', [
                'ordernumber' => 999,
                'userID' => 97,
                'invoice_amount' => 977,
                'ordertime' => $date->format('Y-m-d H:i:s'),
                'status' => 8,
                'cleared' => 10,
                'paymentID' => 2,
            ]);

        $this->dispatch('backend/widgets/getTurnOverVisitors');

        $response = $this->View()->getAssign();

        $this->assertTrue($response['success']);
        $this->assertEquals('100.00', $response['conversion']);
    }

    public function testConversionStillWorks()
    {
        $date = new DateTime();
        $date->sub(new DateInterval('P6DT59M'));

        Shopware()->Container()->get('dbal_connection')
            ->insert('s_statistics_visitors', [
                'shopID' => 1,
                'datum' => $date->format('Y-m-d'),
                'pageimpressions' => 1,
                'uniquevisits' => 1,
                'deviceType' => 'desktop',
            ]);

        Shopware()->Container()->get('dbal_connection')
            ->insert('s_order', [
                'ordernumber' => 999,
                'userID' => 97,
                'invoice_amount' => 977,
                'ordertime' => $date->format('Y-m-d H:i:s'),
                'status' => 8,
                'cleared' => 10,
                'paymentID' => 2,
            ]);

        $this->dispatch('backend/widgets/getTurnOverVisitors');

        $response = $this->View()->getAssign();

        $this->assertTrue($response['success']);
        $this->assertEquals('100.00', $response['conversion']);
    }

    public function testIfNoConversionAfterEightDays()
    {
        $date = new DateTime();
        $date->sub(new DateInterval('P8D'));

        Shopware()->Container()->get('dbal_connection')
            ->insert('s_statistics_visitors', [
                'shopID' => 1,
                'datum' => $date->format('Y-m-d'),
                'pageimpressions' => 1,
                'uniquevisits' => 1,
                'deviceType' => 'desktop',
            ]);

        Shopware()->Container()->get('dbal_connection')
            ->insert('s_order', [
                'ordernumber' => 999,
                'userID' => 97,
                'invoice_amount' => 977,
                'ordertime' => $date->format('Y-m-d H:i:s'),
                'status' => 8,
                'cleared' => 10,
                'paymentID' => 2,
            ]);

        $this->dispatch('backend/widgets/getTurnOverVisitors');

        $response = $this->View()->getAssign();

        $this->assertTrue($response['success']);
        $this->assertEquals('0.00', $response['conversion']);
    }
}
