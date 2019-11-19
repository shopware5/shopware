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

class FormTest extends \Enlight_Components_Test_Controller_TestCase
{
    /**
     * Standard set up for every test - just disable auth
     */
    public function setUp(): void
    {
        parent::setUp();

        // disable auth and acl
        Shopware()->Plugins()->Backend()->Auth()->setNoAuth();
        Shopware()->Plugins()->Backend()->Auth()->setNoAcl();
    }

    public function testGetFormsShouldBeSuccessful()
    {
        $this->dispatch('/backend/form/getForms?page=1&start=0&limit=25');

        static::assertTrue($this->View()->success);
        static::assertNotEmpty($this->View()->data);
        static::assertGreaterThan(5, $this->View()->total);
    }

    public function testGetFormsShouldBeFilterAndSortable()
    {
        $queryParams = [
            'page' => '1',
            'start' => '0',
            'limit' => 25,
            'sort' => json_encode(
                [
                    [
                        'property' => 'name',
                        'direction' => 'ASC',
                    ],
                ]
            ),
            'filter' => json_encode(
                [
                    [
                        'property' => 'name',
                        'value' => 'def%',
                    ],
                ]
            ),
        ];

        $query = http_build_query($queryParams);

        $url = 'backend/form/getForms?';

        $this->dispatch($url . $query);

        static::assertTrue($this->View()->success);
        static::assertNotEmpty($this->View()->data);
        static::assertEquals(2, $this->View()->total);
    }

    public function testGetFormsWithIdShouldReturnSingleForm()
    {
        $this->dispatch('/backend/form/getForms?&id=22');

        $data = $this->View()->data;

        static::assertTrue($this->View()->success);
        static::assertNotEmpty($this->View()->data);
        static::assertNotEmpty($data[0]['fields']);
        static::assertGreaterThan(5, $data[0]['fields']);
        static::assertEquals(1, $this->View()->total);
    }

    public function testGetFormsWithInvalidIdShouldReturnFailure()
    {
        $this->dispatch('/backend/form/getForms?&id=99999999');

        static::assertFalse($this->View()->success);
    }

    public function testGetFieldsShouldReturnFields()
    {
        $this->dispatch('/backend/form/getFields?formId=5');
        static::assertTrue($this->View()->success);
        static::assertNotEmpty($this->View()->data);
        static::assertGreaterThan(2, $this->View()->total);
    }
}
