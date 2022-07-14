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

namespace Shopware\Tests\Functional\Core;

use Doctrine\DBAL\Connection;
use PHPUnit\Framework\TestCase;
use Shopware\Models\Newsletter\Container;
use Shopware\Tests\Functional\Traits\ContainerTrait;
use sMarketing;

class MarketingTest extends TestCase
{
    use ContainerTrait;

    public function testMailCampaignsGetDetail(): void
    {
        $newsletterId = $this->prepareNewsletter();

        $marketingModule = $this->getContainer()->get('modules')->getModule('Marketing');
        static::assertInstanceOf(sMarketing::class, $marketingModule);

        $newsletter = $marketingModule->sMailCampaignsGetDetail($newsletterId);
        static::assertIsArray($newsletter);

        static::assertSame("&lt;script&gt;alert('XSS')&lt;/script&gt;", $newsletter['containers'][0]['description']);
    }

    private function prepareNewsletter(): int
    {
        $connection = $this->getContainer()->get(Connection::class);

        $connection->insert('s_campaigns_mailings', []);
        $newsletterId = (int) $connection->lastInsertId();

        $connection->insert('s_campaigns_containers', [
            'promotionID' => $newsletterId,
            'type' => Container::TYPE_TEXT,
        ]);
        $newsletterContainerId = (int) $connection->lastInsertId();

        $connection->insert('s_campaigns_html', [
            'parentID' => $newsletterContainerId,
            'headline' => "<script>alert('XSS')</script>",
        ]);

        return $newsletterId;
    }
}
