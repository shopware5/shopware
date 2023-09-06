<?php
/**
 * Shopware 5
 * Copyright (c) shopware AG
 *
 * According to our licensing model, this program can be used
 * under the terms of the GNU Affero General Public License, version 3.
 *
 * The texts of the GNU Affero General Public License with an additional
 * permission can be found at and in the LICENSE file you have received
 * along with this program.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 * See the GNU Affero General Public License for more details.
 *
 * "Shopware" is a registered trademark of shopware AG.
 * The licensing of the program under the AGPLv3 does not imply a
 * trademark license. Therefore, any rights, title and interest in
 * our trademarks remain entirely with the shopware AG.
 */

namespace Shopware\Tests\Functional\Controllers\Backend;

use Enlight_Components_Test_Plugin_TestCase;
use Shopware\Tests\Functional\Traits\DatabaseTransactionBehaviour;

class NewsletterManagerTest extends Enlight_Components_Test_Plugin_TestCase
{
    use DatabaseTransactionBehaviour;

    public function setUp(): void
    {
        parent::setUp();
        Shopware()->Plugins()->Backend()->Auth()->setNoAuth();
        Shopware()->Plugins()->Backend()->Auth()->setNoAcl();

        /** @var string $sql */
        $sql = file_get_contents(__DIR__ . '/_fixtures/newsletter/mail.sql');
        static::assertIsString($sql);
        Shopware()->Models()->getConnection()->exec($sql);
    }

    /**
     * @ticket SW-23211
     */
    public function testNewsletterGroup(): void
    {
        $this->dispatch('/backend/NewsletterManager/getGroups');

        static::assertTrue($this->View()->getAssign('success'));
    }
}
