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

namespace Shopware\Tests\Models\Order\Document;

use Shopware\Models\Order\Document\Document;
use Shopware_Components_Document;

class DocumentTest extends \Enlight_Components_Test_TestCase
{
    public function testSetAttribute()
    {
        $document = new Document();
        $attribute = new \Shopware\Models\Attribute\Document();
        $document->setAttribute($attribute);

        static::assertSame($document, $attribute->getDocument());
        static::assertSame($attribute, $document->getAttribute());
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
        } catch (\Exception $e) {
            // Append exception to possible failure ouputs (caused by the assert in the finally block)
            throw $e;
        } finally {
            // Make sure we always clean up, since an inactive default shop would otherwise crash the next test execution
            $shop->setActive(true);
            Shopware()->Models()->flush($shop);

            // Check that document was actually created
            static::assertNotNull($document);
        }
    }

    /**
     * Test the creation of multiple documents of the same order and the same type.
     * Asserts the number of created documents is 2 if the _allowMultipleDocuments flag is set.
     */
    public function testMultipleDocumentsOfTheSameType()
    {
        $document1 = Shopware_Components_Document::initDocument(
            15,
            1,
            [
                '_renderer' => 'pdf',
                '_preview' => false,
                '_allowMultipleDocuments' => true,
            ]
        );
        $document1->render();

        $document2 = Shopware_Components_Document::initDocument(
            15,
            1,
            [
                '_renderer' => 'pdf',
                '_preview' => false,
                '_allowMultipleDocuments' => true,
            ]
        );
        $document2->render();

        $documents = Shopware()->Container()->get('models')->getRepository(Document::class)
            ->findBy([
                'typeId' => 1,
                'orderId' => 15,
            ]);
        static::assertCount(2, $documents);

        // Revert changes of this test
        foreach ($documents as $document) {
            Shopware()->Container()->get('models')->remove($document);
        }
        Shopware()->Container()->get('models')->flush();
    }

    /**
     * Test the creation of multiple documents of the same order and the same type.
     * Asserts the number of created documents stays 1 if the _allowMultipleDocuments flag is not set.
     */
    public function testMultipleDocumentsOfTheSameTypeNegative()
    {
        $document1 = Shopware_Components_Document::initDocument(
            15,
            1,
            [
                '_renderer' => 'pdf',
                '_preview' => false,
                '_allowMultipleDocuments' => false,
            ]
        );
        $document1->render();

        $document2 = Shopware_Components_Document::initDocument(
            15,
            1,
            [
                '_renderer' => 'pdf',
                '_preview' => false,
                '_allowMultipleDocuments' => false,
            ]
        );
        $document2->render();

        $documents = Shopware()->Container()->get('models')->getRepository(Document::class)
            ->findBy([
                'typeId' => 1,
                'orderId' => 15,
            ]);
        static::assertCount(1, $documents);

        // Revert changes of this test
        foreach ($documents as $document) {
            Shopware()->Container()->get('models')->remove($document);
        }
        Shopware()->Container()->get('models')->flush();
    }
}
