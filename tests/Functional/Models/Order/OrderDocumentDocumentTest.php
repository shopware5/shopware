<?php

declare(strict_types=1);
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

namespace Shopware\Tests\Functional\Models\Order;

use Enlight_Components_Test_TestCase;
use Exception;
use Generator;
use RuntimeException;
use Shopware\Components\Model\ModelManager;
use Shopware\Models\Attribute\Document as DocumentAttribute;
use Shopware\Models\Order\Document\Document;
use Shopware\Models\Shop\Shop;
use Shopware\Tests\Functional\Traits\ContainerTrait;
use Shopware_Components_Document;

class OrderDocumentDocumentTest extends Enlight_Components_Test_TestCase
{
    use ContainerTrait;

    private ModelManager $modelManager;

    protected function setUp(): void
    {
        $this->modelManager = $this->getContainer()->get(ModelManager::class);
        parent::setUp();
    }

    public function testSetAttribute(): void
    {
        $document = new Document();
        $attribute = new DocumentAttribute();
        $document->setAttribute($attribute);

        static::assertSame($document, $attribute->getDocument());
        static::assertSame($attribute, $document->getAttribute());
    }

    /*
     * Test order document creation for inactive subshops
     */

    public function testDocumentForInactiveSubshop(): void
    {
        // Temporarily set main shop to inactive
        $shop = $this->modelManager->getRepository(Shop::class)->find(1);
        $shop->setActive(false);

        // Flush changed shop to the database, needed for the Document code
        $this->modelManager->flush($shop);

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
            // Append exception to possible failure outputs (caused by the assert in the finally block)
            throw $e;
        } finally {
            // Make sure we always clean up, since an inactive default shop would otherwise crash the next test execution
            $shop->setActive(true);
            $this->modelManager->flush($shop);

            // Check that document was actually created
            static::assertNotNull($document);
        }
    }

    /**
     * Test the creation of multiple documents of the same order and the same type.
     * Asserts the number of created documents is 2 if the _allowMultipleDocuments flag is set.
     */
    public function testMultipleDocumentsOfTheSameType(): void
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

        $documents = $this->modelManager->getRepository(Document::class)
            ->findBy([
                'typeId' => 1,
                'orderId' => 15,
            ]);
        static::assertCount(2, $documents);

        // Revert changes of this test
        foreach ($documents as $document) {
            $this->modelManager->remove($document);
        }
        $this->modelManager->flush();
    }

    /**
     * Test the creation of multiple documents of the same order and the same type.
     * Asserts the number of created documents stays 1 if the _allowMultipleDocuments flag is not set.
     */
    public function testMultipleDocumentsOfTheSameTypeNegative(): void
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

        $documents = $this->modelManager->getRepository(Document::class)
            ->findBy([
                'typeId' => 1,
                'orderId' => 15,
            ]);
        static::assertCount(1, $documents);

        // Revert changes of this test
        foreach ($documents as $document) {
            $this->modelManager->remove($document);
        }
        $this->modelManager->flush();
    }

    /**
     * @dataProvider orderIdProvider
     */
    public function testOrderWithoutBillingAddress(int $orderId, bool $expectException): void
    {
        if ($expectException) {
            $this->expectException(RuntimeException::class);
            $this->expectExceptionMessage('Country ID not set in shipping address');
        }
        $document = Shopware_Components_Document::initDocument($orderId, 1, [
            '_renderer' => 'pdf',
            '_preview' => false,
        ]);
        $document->render();

        $documents = $this->modelManager->getRepository(Document::class)->findBy(['typeId' => 1]);
        static::assertCount(1, $documents);
    }

    /**
     * @return Generator<string, array<int|bool>>
     */
    public function orderIdProvider(): Generator
    {
        yield 'Invalid order from the demo data without billing and shipping address' => [52, true];
        yield 'Valid order' => [15, false];
    }
}
