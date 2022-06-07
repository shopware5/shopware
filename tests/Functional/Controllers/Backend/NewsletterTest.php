<?php

declare(strict_types=1);
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
use Enlight_Components_Test_Plugin_TestCase;
use Shopware\Tests\Functional\Traits\ContainerTrait;
use Shopware\Tests\Functional\Traits\DatabaseTransactionBehaviour;

class NewsletterTest extends Enlight_Components_Test_Plugin_TestCase
{
    use DatabaseTransactionBehaviour;
    use ContainerTrait;

    public function setUp(): void
    {
        parent::setUp();
        Shopware()->Plugins()->Backend()->Auth()->setNoAuth();
        Shopware()->Plugins()->Backend()->Auth()->setNoAcl();

        $sql = file_get_contents(__DIR__ . '/_fixtures/newsletter/mail.sql');
        static::assertIsString($sql);
        $this->getContainer()->get(Connection::class)->executeStatement($sql);
    }

    /**
     * @ticket SW-4747
     */
    public function testNewsletterLock(): void
    {
        $this->Front()->setParam('noViewRenderer', false);
        $this->setConfig('MailCampaignsPerCall', 1);

        $this->dispatch('/backend/newsletter/cron');
        $responseBody = $this->Response()->getBody();
        static::assertIsString($responseBody);
        static::assertMatchesRegularExpression('#\d+ Recipients fetched#', $responseBody);
        $this->reset();

        $this->dispatch('/backend/newsletter/cron');
        $responseBody = $this->Response()->getBody();
        static::assertIsString($responseBody);
        static::assertMatchesRegularExpression('#Wait \d+ seconds ...#', $responseBody);
        $this->reset();
    }

    public function testGetMailsWithoutGroups(): void
    {
        $newsletterController = $this->getContainer()->get('shopware_controllers_backend_newsletter');
        $emails = $newsletterController->getMailingEmails(2);

        static::assertSame([], $emails);
    }
}
