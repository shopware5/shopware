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

    /*
     * Test order document creation for inactive subshops
     */
    public function testDocumentForInactiveSubshop()
    {
        // Temporarily set main shop to inactive
        $shop = Shopware()->Models()->getRepository(\Shopware\Models\Shop\Shop::class)->find(1);
        $shop->setActive(false);

        // Flush changed shop to the database, needed for the Document code
        Shopware()->Models()->flush($shop);

        try {
            /*
             * Used to fail with the following error before allowing inactive subshops
             *  Error: Call to a member function setCurrency() on null
             *  in engine/Shopware/Components/Document.php:538
             */
            $document = Shopware_Components_Document::initDocument(
                57, // Arbitrary order
                1,
                [
                    '_renderer' => 'pdf',
                    '_preview' => true,
                ]
            );
        } catch (Exception $e) {
            // Append exception to possible failure ouputs (caused by the assert in the finally block)
            throw $e;
        } finally {
            // Make sure we always clean up, since an inactive default shop would otherwise crash the next test execution
            $shop->setActive(true);
            Shopware()->Models()->flush($shop);

            // Check that document was actually created
            $this->assertNotNull($document);
        }
    }
}
