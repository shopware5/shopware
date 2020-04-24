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

class LogTest extends \Enlight_Components_Test_Controller_TestCase
{
    /**
     * Standard set up for every test - just disable auth
     */
    public function setUp(): void
    {
        parent::setUp();
        // Disable auth and acl
        Shopware()->Plugins()->Backend()->Auth()->setNoAuth();
        Shopware()->Plugins()->Backend()->Auth()->setNoAcl();
    }

    /**
     * Tests the getLogsAction()
     * to test if reading the logs is working
     */
    public function testGetLogs()
    {
        /* @var \Enlight_Controller_Response_ResponseTestCase */
        $this->dispatch('backend/log/getLogs');
        static::assertTrue($this->View()->success);

        $jsonBody = $this->View()->getAssign();

        static::assertArrayHasKey('total', $jsonBody);
        static::assertArrayHasKey('data', $jsonBody);
        static::assertArrayHasKey('success', $jsonBody);
    }

    /**
     * This test tests the creating of a new log.
     * This function is called before testDeleteLogs
     */
    public function testCreateLog()
    {
        Shopware()->Container()->get(\Doctrine\DBAL\Connection::class)->beginTransaction();

        $this->Request()->setClientIp('10.0.0.3', false);
        $this->Request()->setMethod('POST')->setPost(
            [
                'type' => 'backend',
                'key' => 'Log',
                'text' => 'DummyText',
                'date' => new \DateTime('now'),
                'user' => 'Administrator',
                'value4' => '',
            ]
        );

        $this->dispatch('backend/logger/createLog');
        static::assertTrue($this->View()->success);

        $jsonBody = $this->View()->getAssign();

        static::assertArrayHasKey('data', $jsonBody);
        static::assertArrayHasKey('success', $jsonBody);
        static::assertArrayHasKey('id', $jsonBody['data']);

        return $jsonBody['data']['id'];
    }

    /**
     * This test-method tests the deleting of a log.
     *
     * @depends testCreateLog
     *
     * @param string $lastId
     */
    public function testDeleteLogs($lastId)
    {
        $this->Request()->setMethod('POST')->setPost(['id' => $lastId]);

        $this->dispatch('backend/log/deleteLogs');

        $jsonBody = $this->View()->getAssign();

        static::assertArrayHasKey('success', $jsonBody);
        static::assertArrayHasKey('data', $jsonBody);

        Shopware()->Container()->get(\Doctrine\DBAL\Connection::class)->rollBack();
    }

    /**
     * This test tests the creating of a new log.
     * This function is called before testDeleteLogs
     */
    public function testCreateDeprecatedLog()
    {
        Shopware()->Container()->get(\Doctrine\DBAL\Connection::class)->beginTransaction();

        $this->Request()->setClientIp('10.0.0.3', false);
        $this->Request()->setMethod('POST')->setPost(
            [
                'type' => 'backend',
                'key' => 'Log',
                'text' => 'DummyText',
                'date' => new \DateTime('now'),
                'user' => 'Administrator',
                'value4' => '',
            ]
        );

        $this->dispatch('backend/log/createLog');
        static::assertTrue($this->View()->success);

        $jsonBody = $this->View()->getAssign();

        static::assertArrayHasKey('data', $jsonBody);
        static::assertArrayHasKey('success', $jsonBody);
        static::assertArrayHasKey('id', $jsonBody['data']);

        Shopware()->Container()->get(\Doctrine\DBAL\Connection::class)->rollBack();
    }

    public function testSystemLogList(): void
    {
        $container = Shopware()->Container();
        $pluginLogger = $container->get('pluginlogger');
        $coreLogger = $container->get('corelogger');

        // making sure that at least 1 entry for pluginlogger & corelogger are available
        $pluginLogger->info('Running test...');
        $coreLogger->info('Running test...');

        // general settings
        $this->Request()->setMethod('GET');
        $this->dispatch('backend/log/getLogFileList');
        $jsonBody = $this->View()->getAssign();

        static::assertArrayHasKey('data', $jsonBody);
        static::assertArrayHasKey('success', $jsonBody);
        static::assertTrue($jsonBody['success']);
        static::assertArrayHasKey('total', $jsonBody);
        static::assertGreaterThanOrEqual(2, $jsonBody['total']);
    }

    public function testSystemLogListWithLimit(): void
    {
        $container = Shopware()->Container();
        $pluginLogger = $container->get('pluginlogger');
        $coreLogger = $container->get('corelogger');

        // making sure that at least 1 entry for pluginlogger & corelogger are available
        $pluginLogger->info('Running test...');
        $coreLogger->info('Running test...');

        // test against limit
        $this->Request()->setMethod('GET')->setParams([
            'limit' => 1,
        ]);
        $this->dispatch('backend/log/getLogFileList');
        $jsonBody = $this->View()->getAssign();

        static::assertArrayHasKey('data', $jsonBody);
        static::assertCount(1, $jsonBody['data']);
    }

    public function testSystemLogListWithFilter(): void
    {
        $container = Shopware()->Container();
        $pluginLogger = $container->get('pluginlogger');
        $coreLogger = $container->get('corelogger');

        // making sure that at least 1 entry for pluginlogger & corelogger are available
        $pluginLogger->info('Running test...');
        $coreLogger->info('Running test...');

        // test filtering
        $file = sprintf('core_%s', $container->getParameter('kernel.environment'));
        $this->Request()->setParams([
            'limit' => 1,
            'query' => $file,
        ]);
        $this->dispatch('backend/log/getLogFileList');
        $jsonBody = $this->View()->getAssign();

        static::assertCount(1, $jsonBody['data']);
        static::assertNotFalse(stripos($jsonBody['data'][0]['name'], $file));
    }
}
