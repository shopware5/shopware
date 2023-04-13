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

namespace Shopware\Tests\Functional\Controllers\Backend;

use Doctrine\DBAL\Connection;
use Enlight_Controller_Request_RequestHttp;
use Enlight_Template_Manager;
use Enlight_View_Default;
use PHPUnit\Framework\TestCase;
use Shopware\Tests\Functional\Traits\ContainerTrait;
use Shopware\Tests\Functional\Traits\DatabaseTransactionBehaviour;
use Shopware_Controllers_Backend_CanceledOrder;

class CanceledOrderTest extends TestCase
{
    use ContainerTrait;
    use DatabaseTransactionBehaviour;

    private const FIRST_DUMMY_SESSION_ID = '1231231231231231231231231231231231231320';
    private const SECOND_DUMMY_SESSION_ID = '1231231231231231231231231231231231231321';

    private const DEMO_CUSTOMER_ID = 1;
    private const DEMO_CANCELED_ORDER_ID = 52;
    private const DEMO_VOUCHER_ID = 4;
    private const TEST_MAIL_ADDRESS = 'test@example.com';
    private const TEMPLATE_CANCELED_QUESTION = 'sCANCELEDQUESTION';
    private const TEMPLATE_CANCELED_VOUCHER = 'sCANCELEDVOUCHER';

    /**
     * Test if the canceled order statistic returns the right values
     *
     * @ticket SW-6624
     */
    public function testCanceledOrderSummary(): void
    {
        $this->insertTestOrder();

        $controller = $this->getController();
        $request = new Enlight_Controller_Request_RequestHttp();
        $request->setQuery([
            'fromDate' => '2101-09-10T00:00:00',
            'toDate' => '2101-09-11T00:00:00',
        ]);
        $controller->setRequest($request);
        $controller->getBasketAction();

        $data = $controller->View()->getAssign('data');

        $firstRow = $data[0];
        static::assertEquals('2101-09-10', $firstRow['date']);
        static::assertEquals('2499', $firstRow['price']);
        static::assertEquals('2499', $firstRow['average']);
        static::assertEquals('1', $firstRow['number']);
        static::assertEquals('2101', $firstRow['year']);
        static::assertEquals('9', $firstRow['month']);

        $secondRow = $data[1];
        static::assertEquals('2101-09-11', $secondRow['date']);
        static::assertEquals('125.72', $secondRow['price']);
        static::assertEquals('25.144', $secondRow['average']);
        static::assertEquals('2', $secondRow['number']);
        static::assertEquals('2101', $secondRow['year']);
        static::assertEquals('9', $secondRow['month']);
    }

    public function testSendCanceledQuestionMailActionWithoutMailParameter(): void
    {
        $controller = $this->getController();
        $request = new Enlight_Controller_Request_RequestHttp();
        $controller->setRequest($request);
        $controller->sendCanceledQuestionMailAction();

        $view = $controller->View();
        static::assertFalse($view->getAssign('success'));
        static::assertSame('Es wurde keine E-Mail übergeben.', $view->getAssign('message'));
    }

    public function testSendCanceledQuestionMailActionWithoutTemplateParameter(): void
    {
        $controller = $this->getController();
        $request = new Enlight_Controller_Request_RequestHttp();
        $request->setParams([
            'mail' => self::TEST_MAIL_ADDRESS,
        ]);
        $controller->setRequest($request);
        $controller->sendCanceledQuestionMailAction();

        $view = $controller->View();
        static::assertFalse($view->getAssign('success'));
        static::assertSame('Es wurde kein Template übergeben.', $view->getAssign('message'));
    }

    public function testSendCanceledQuestionMailActionWithoutVoucherIdParameter(): void
    {
        $controller = $this->getController();
        $request = new Enlight_Controller_Request_RequestHttp();
        $request->setParams([
            'mail' => self::TEST_MAIL_ADDRESS,
            'template' => self::TEMPLATE_CANCELED_VOUCHER,
        ]);
        $controller->setRequest($request);
        $controller->sendCanceledQuestionMailAction();

        $view = $controller->View();
        static::assertFalse($view->getAssign('success'));
        static::assertSame('Es wurde kein voucherId (GutscheinId) übergeben.', $view->getAssign('message'));
    }

    public function testSendCanceledQuestionMailActionWithoutCustomerIdParameter(): void
    {
        $controller = $this->getController();
        $request = new Enlight_Controller_Request_RequestHttp();
        $request->setParams([
            'mail' => self::TEST_MAIL_ADDRESS,
            'template' => self::TEMPLATE_CANCELED_QUESTION,
        ]);
        $controller->setRequest($request);
        $controller->sendCanceledQuestionMailAction();

        $view = $controller->View();
        static::assertFalse($view->getAssign('success'));
        static::assertSame('Es wurde keine customerId (KundenId) übergeben.', $view->getAssign('message'));
    }

    public function testSendCanceledQuestionMailActionWithoutOrderIdParameter(): void
    {
        $controller = $this->getController();
        $request = new Enlight_Controller_Request_RequestHttp();
        $request->setParams([
            'mail' => self::TEST_MAIL_ADDRESS,
            'template' => self::TEMPLATE_CANCELED_QUESTION,
            'customerId' => self::DEMO_CUSTOMER_ID,
        ]);
        $controller->setRequest($request);
        $controller->sendCanceledQuestionMailAction();

        $view = $controller->View();
        static::assertFalse($view->getAssign('success'));
        static::assertSame('Es wurde keine orderId übergeben.', $view->getAssign('message'));
    }

    public function testSendCanceledQuestionMailActionWithoutVoucher(): void
    {
        $controller = $this->getController();
        $request = new Enlight_Controller_Request_RequestHttp();
        $request->setParams([
            'mail' => self::TEST_MAIL_ADDRESS,
            'template' => self::TEMPLATE_CANCELED_QUESTION,
            'customerId' => self::DEMO_CUSTOMER_ID,
            'orderId' => self::DEMO_CANCELED_ORDER_ID,
        ]);
        $controller->setRequest($request);
        $controller->sendCanceledQuestionMailAction();

        $view = $controller->View();
        static::assertTrue($view->getAssign('success'));
        $comment = $this->getContainer()->get(Connection::class)->fetchOne(
            'SELECT comment FROM s_order WHERE id = :id',
            ['id' => self::DEMO_CANCELED_ORDER_ID]
        );
        static::assertSame('Frage gesendet', $comment);
    }

    public function testSendCanceledQuestionMailActionWithVoucher(): void
    {
        $controller = $this->getController();
        $request = new Enlight_Controller_Request_RequestHttp();
        $request->setParams([
            'mail' => self::TEST_MAIL_ADDRESS,
            'template' => self::TEMPLATE_CANCELED_VOUCHER,
            'voucherId' => self::DEMO_VOUCHER_ID,
            'customerId' => self::DEMO_CUSTOMER_ID,
            'orderId' => self::DEMO_CANCELED_ORDER_ID,
        ]);
        $controller->setRequest($request);
        $controller->sendCanceledQuestionMailAction();

        $view = $controller->View();
        static::assertTrue($view->getAssign('success'));

        $comment = $this->getContainer()->get(Connection::class)->fetchOne(
            'SELECT comment FROM s_order WHERE id = :id',
            ['id' => self::DEMO_CANCELED_ORDER_ID]
        );
        static::assertSame(' Gutschein gesendet', $comment);

        $codes = $this->getContainer()->get(Connection::class)->fetchAllAssociative(
            'SELECT * FROM s_emarketing_voucher_codes WHERE userID = :customerId',
            ['customerId' => self::DEMO_CUSTOMER_ID]
        );
        static::assertCount(1, $codes);
        $code = $codes[0];
        static::assertSame(self::DEMO_VOUCHER_ID, (int) $code['voucherID']);
        static::assertFalse((bool) $code['cashed']);
        static::assertStringStartsWith('23A7B', $code['code']);
    }

    private function getController(): Shopware_Controllers_Backend_CanceledOrder
    {
        $controller = new Shopware_Controllers_Backend_CanceledOrder();
        $controller->setContainer($this->getContainer());
        $controller->setView(new Enlight_View_Default(new Enlight_Template_Manager()));

        return $controller;
    }

    private function insertTestOrder(): void
    {
        $sql = "
              INSERT INTO `s_order_basket` (`sessionID`, `userID`, `articlename`, `articleID`, `ordernumber`, `shippingfree`, `quantity`, `price`, `netprice`, `tax_rate`, `datum`, `modus`, `esdarticle`, `partnerID`, `lastviewport`, `useragent`, `config`, `currencyFactor`) VALUES
                (:firstSession, 0, 'Sonnenbrille Red', 170, 'SW10170', 0, 4, 39.95, 33.571428571429, 19, '2101-09-11 11:49:54', 0, 0, '', 'index', 'Mozilla/5.0 (Windows NT 6.1; WOW64; rv:22.0) Gecko/20100101 Firefox/22.0 FirePHP/0.7.2', '', 1),
                (:firstSession, 0, 'Fliegenklatsche grün', 98, 'SW10101', 0, 1, 0.79, 0.66386554621849, 19, '2101-09-11 11:50:02', 0, 0, '', 'index', 'Mozilla/5.0 (Windows NT 6.1; WOW64; rv:22.0) Gecko/20100101 Firefox/22.0 FirePHP/0.7.2', '', 1),
                (:firstSession, 0, 'Bumerang', 245, 'SW10236', 0, 1, 20, 16.806722689076, 19, '2101-09-11 11:50:13', 0, 0, '', 'index', 'Mozilla/5.0 (Windows NT 6.1; WOW64; rv:22.0) Gecko/20100101 Firefox/22.0 FirePHP/0.7.2', '', 1),
                (:secondSession, 0, 'Dartscheibe Circle', 240, 'SW10231', 0, 1, 49.99, 42.008403361345, 19, '2101-09-11 11:50:17', 0, 0, '', '', '', '', 1),
                (:secondSession, 0, 'Dartpfeil Steel Atomic', 241, 'SW10232', 0, 1, 14.99, 12.596638655462, 19, '2101-09-11 11:50:20', 0, 0, '', '', '', '', 1),
                (:firstSession, 0, 'Dart Automat Standgerät', 239, 'SW10230', 0, 1, 2499, 2100, 19, '2101-09-10 11:50:22', 0, 0, '', 'index', 'Mozilla/5.0 (Windows NT 6.1; WOW64; rv:22.0) Gecko/20100101 Firefox/22.0 FirePHP/0.7.2', '', 1),
                (:firstSession, 0, 'Warenkorbrabatt', 0, 'SHIPPINGDISCOUNT', 0, 1, -2, -1.68, 19, '2101-09-10 11:50:22', 4, 0, '', 'index', 'Mozilla/5.0 (Windows NT 6.1; WOW64; rv:22.0) Gecko/20100101 Firefox/22.0 FirePHP/0.7.2', '', 1);
        ";

        $this->getContainer()->get(Connection::class)->executeStatement($sql, [
            'firstSession' => self::FIRST_DUMMY_SESSION_ID,
            'secondSession' => self::SECOND_DUMMY_SESSION_ID,
        ]);
    }
}
