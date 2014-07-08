<?php

class Shopware_Tests_Models_Order_Document_DocumentTest extends Enlight_Components_Test_TestCase
{
    public function testSetAttribute()
    {
        $document = new \Shopware\Models\Order\Document\Document();
        $attribute = new \Shopware\Models\Attribute\Document();
        $document->setAttribute($attribute);

        $this->assertSame($document, $attribute->getDocument());
        $this->assertSame($attribute, $document->getAttribute());
    }
}
