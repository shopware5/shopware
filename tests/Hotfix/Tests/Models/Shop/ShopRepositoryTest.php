<?php

/**
 * Tests the shop duplication bug caused by the fixActive method
 * of the shop repository (Shopware\Models\Shop\Repsoitory).
 *
 * Remark: This is a simple testcase, that is only intended to showcase the bug
 * and it's hotfix. Therefore it is based on the current demo dataset and
 * changes made to the database will not be cleaned up.
 */
class Hotfix_Tests_Models_Shop_ShopRepositoryTest extends Enlight_Components_Test_TestCase
{

    public function testFixActive()
    {
        $em = Shopware()->Models();
        $shopRepository = $em->getRepository('Shopware\Models\Shop\Shop');
        $orderRepository = $em->getRepository('Shopware\Models\Order\Order');

        // Get inital number of shops
        $numberOfShopsBefore = Shopware()->Db()->fetchOne("SELECT count(*) FROM s_core_shops");

        // Load arbitrary order from demo dataset
        $orderId = 57;
        $order = $orderRepository->find($orderId);

        // Modify order entitiy to trigger an update action, when the entity is flushed to the database
        $order->setComment('Dummy');

        // Get shop entity via repository method. Among other things this will invoke the fixActive method
        $shop = $shopRepository->getActiveById($order->getLanguageSubShop()->getId());

        // Flush changes to the database
        $em->flush($order);

        // Get actual number of shops
        $numberOfShopsAfter = Shopware()->Db()->fetchOne("SELECT count(*) FROM s_core_shops");

        // Check that the number of shops has not been changed
        $this->assertSame($numberOfShopsBefore, $numberOfShopsAfter);
    }

}
