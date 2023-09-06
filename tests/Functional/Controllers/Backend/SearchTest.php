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

use Enlight_Components_Test_Controller_TestCase;
use Shopware\Tests\Functional\Bundle\StoreFrontBundle\Helper;
use Shopware\Tests\Functional\Traits\ContainerTrait;

class SearchTest extends Enlight_Components_Test_Controller_TestCase
{
    use ContainerTrait;

    private Helper $helper;

    /**
     * Standard set up for every test - just disable auth
     */
    public function setUp(): void
    {
        parent::setUp();

        // Disable auth and acl
        Shopware()->Plugins()->Backend()->Auth()->setNoAuth();
        Shopware()->Plugins()->Backend()->Auth()->setNoAcl();

        $this->helper = new Helper($this->getContainer());
    }

    /**
     * @group elasticSearch
     */
    public function testSearchForVariants(): void
    {
        $this->helper->refreshBackendSearchIndex();

        $this->Request()->setMethod('POST')->setPost(['search' => 'SW10002.1']);
        $this->dispatch('backend/search');

        $jsonBody = $this->View()->getAssign();

        static::assertEquals(2, $jsonBody['searchResult']['articles']['SW10002.1']['kind']);
        $this->reset();
    }
}
