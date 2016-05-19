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

class Shopware_Tests_Components_Api_ManufacturerTest extends Shopware_Tests_Components_Api_TestCase
{
    /**
     * @return \Shopware\Components\Api\Resource\Manufacturer
     */
    public function createResource()
    {
        return new \Shopware\Components\Api\Resource\Manufacturer();
    }

    public function testCreateShouldBeSuccessful()
    {
        $date = new \DateTime();
        $date->modify('-3 day');
        $changed = $date->format(DateTime::ISO8601);

        $testData = array(
            "name" => "fooobar",
            "description" => "foobar description with exceptionell long text",
            "link" => "http://shopware.com",
            "image" => [
                "link" => "http://assets.shopware.com/sw_logo_white.png"
            ],

            "metaTitle" => "test, test",
            "metaKeywords" => "test, test",
            "metaDescription" => "Description Test",

            "changed" => $changed
        );

        $manufacturer = $this->resource->create($testData);

        $this->assertInstanceOf('\Shopware\Models\Article\Supplier', $manufacturer);
        $this->assertGreaterThan(0, $manufacturer->getId());
        $this->assertNotEmpty($manufacturer->getImage());

        $this->assertEquals($manufacturer->getMetaDescription(), $testData['metaDescription']);

        return $manufacturer->getId();
    }

    /**
     * @depends testCreateShouldBeSuccessful
     */
    public function testGetOneShouldBeSuccessful($id)
    {
        $manufacturer = $this->resource->getOne($id);
        $this->assertGreaterThan(0, $manufacturer['id']);
    }

    /**
     * @depends testCreateShouldBeSuccessful
     */
    public function testGetListShouldBeSuccessful()
    {
        $result = $this->resource->getList();

        $this->assertArrayHasKey('data', $result);
        $this->assertArrayHasKey('total', $result);

        $this->assertGreaterThanOrEqual(1, $result['total']);
        $this->assertGreaterThanOrEqual(1, $result['data']);
    }

    /**
     * @depends testCreateShouldBeSuccessful
     */
    public function testUpdateShouldBeSuccessful($id)
    {
        $testData = array(
            'name'   => uniqid() . 'foobar supplier'
        );

        $manufacturer = $this->resource->update($id, $testData);

        $this->assertInstanceOf('\Shopware\Models\Article\Supplier', $manufacturer);
        $this->assertEquals($id, $manufacturer->getId());

        $this->assertEquals($manufacturer->getName(), $testData['name']);

        return $id;
    }

    /**
     * @expectedException \Shopware\Components\Api\Exception\NotFoundException
     */
    public function testUpdateWithInvalidIdShouldThrowNotFoundException()
    {
        $this->resource->update(9999999, array());
    }

    /**
     * @expectedException \Shopware\Components\Api\Exception\ParameterMissingException
     */
    public function testUpdateWithMissingIdShouldThrowParameterMissingException()
    {
        $this->resource->update('', array());
    }

    /**
     * @depends testUpdateShouldBeSuccessful
     */
    public function testDeleteShouldBeSuccessful($id)
    {
        $manufacturer = $this->resource->delete($id);

        $this->assertInstanceOf('\Shopware\Models\Article\Supplier', $manufacturer);
        $this->assertEquals(null, $manufacturer->getId());
    }

    /**
     * @expectedException \Shopware\Components\Api\Exception\NotFoundException
     */
    public function testDeleteWithInvalidIdShouldThrowNotFoundException()
    {
        $this->resource->delete(9999999);
    }

    /**
     * @expectedException \Shopware\Components\Api\Exception\ParameterMissingException
     */
    public function testDeleteWithMissingIdShouldThrowParameterMissingException()
    {
        $this->resource->delete('');
    }
}
