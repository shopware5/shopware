<?php

/**
 * Class RemoveAndCreateResourceTest
 */
class RemoveAndCreateResourceTest extends Enlight_Components_Test_TestCase
{
    public function test_remove_and_create_a_resource()
    {
        $em = Shopware()->Container()->get('models');

        $acl = new Shopware_Components_Acl($em);

        $name = 'test' . mt_rand(0,5000000);

        $this->assertNull($acl->createResource($name, ['read', 'write']));
        $this->assertTrue($acl->deleteResource($name));
        $this->assertNull($acl->createResource($name, ['read', 'write']));
        $this->assertTrue($acl->deleteResource($name));
    }
}
