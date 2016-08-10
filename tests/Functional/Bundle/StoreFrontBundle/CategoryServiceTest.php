<?php

namespace Shopware\Tests\Bundle\StoreFrontBundle;

use Shopware\Bundle\StoreFrontBundle;


class CategoryServiceTest extends TestCase
{
    public function testCategorySorting()
    {
        $first  = $this->helper->createCategory(array('name' => 'first',  'parent' => 3));
        $second = $this->helper->createCategory(array('name' => 'second', 'parent' => $first->getId(), 'position' => 1));
        $third  = $this->helper->createCategory(array('name' =>  'third', 'parent' => $first->getId(), 'position' => 2));
        $fourth = $this->helper->createCategory(array('name' => 'fourth', 'parent' => $first->getId(), 'position' => 2));

        $categories = Shopware()->Container()->get('shopware_storefront.category_service')->getList(
            array(
                $second->getId(),
                $third->getId(),
                $fourth->getId()
            ),
            $this->getContext()
        );

        foreach ($categories as $id => $category) {
            $this->assertEquals($id, $category->getId());
        }

        $categories = array_values($categories);
        $this->assertEquals($second->getId(), $categories[0]->getId());
        $this->assertEquals($third->getId(),  $categories[1]->getId());
        $this->assertEquals($fourth->getId(), $categories[2]->getId());
    }

    public function testBlockedCustomerGroups()
    {
        $first  = $this->helper->createCategory(array('name' => 'first',  'parent' => 3));
        $second = $this->helper->createCategory(array('name' => 'second', 'parent' => $first->getId()));
        $third = $this->helper->createCategory(array('name' => 'third',   'parent' => $second->getId()));

        $context = $this->getContext();

        Shopware()->Db()->query(
            "INSERT INTO s_categories_avoid_customergroups (categoryID, customerGroupID) VALUES (?, ?)",
            array($second->getId(), $context->getCurrentCustomerGroup()->getId())
        );
        Shopware()->Db()->query(
            "INSERT INTO s_categories_avoid_customergroups (categoryID, customerGroupID) VALUES (?, ?)",
            array($third->getId(), $context->getCurrentCustomerGroup()->getId())
        );


        $categories = Shopware()->Container()->get('shopware_storefront.category_service')->getList(
            array(
                $first->getId(),
                $second->getId(),
                $third->getId(),
            ),
            $context
        );

        $this->assertCount(1, $categories);

        $this->assertArrayHasKey($first->getId(), $categories);
    }

    public function testOnlyActiveCategories()
    {
        $first  = $this->helper->createCategory(array('name' => 'first',  'parent' => 3, 'active' => false));
        $second = $this->helper->createCategory(array('name' => 'second', 'parent' => $first->getId(), 'active' => false));
        $third = $this->helper->createCategory(array('name' => 'third',   'parent' => $second->getId()));

        $categories = Shopware()->Container()->get('shopware_storefront.category_service')->getList(
            array(
                $first->getId(),
                $second->getId(),
                $third->getId(),
            ),
            $this->getContext()
        );

        $this->assertCount(1, $categories);
        $this->assertArrayHasKey($third->getId(), $categories);
    }
}
