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

namespace Shopware\Bundle\OrderBundle\Service;

use Shopware\Components\Model\ModelManager;
use Shopware\Models\Article\Detail as ProductDetail;
use Shopware\Models\Order\Detail as OrderDetail;

class StockService implements StockServiceInterface
{
    /** @var ModelManager */
    protected $entityManager;

    public function __construct(ModelManager $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function addProductDetail(OrderDetail $detail): void
    {
        $product = $this->getProductFromDetail($detail);
        if ($product) {
            $product->setInStock($product->getInStock() - $detail->getQuantity());
            $this->entityManager->persist($product);
            $this->entityManager->flush($product);
        }
    }

    public function updateProductDetail(OrderDetail $detail, ?string $oldProductNumber = null, ?int $oldQuantity = null, ?string $newProductNumber = null, ?int $newQuantity = null): void
    {
        $oldQuantity = $oldQuantity === 0 || $oldQuantity > 0 ? $oldQuantity : $detail->getQuantity();
        $newQuantity = $newQuantity === 0 || $newQuantity > 0 ? $newQuantity : $detail->getQuantity();

        // If the position product has been changed, the old product stock must be increased based on the (old) ordering quantity.
        // The stock of the new product will be reduced by the (new) ordered quantity.
        if ($newProductNumber !== $oldProductNumber) {
            // If the old product is a product in the stock, we must increase the stock to the original stock size
            $this->updateProductInStock($oldProductNumber, $oldQuantity);
            $this->updateProductInStock($newProductNumber, -$newQuantity);
        } elseif ($oldQuantity !== $newQuantity) {
            $product = $this->getProductFromDetail($detail);
            if (!$product) {
                return;
            }

            // If the product is a product in the stock, we must change the stock size to the new ordered quantity
            $quantityDiff = $oldQuantity - $newQuantity;

            $product->setInStock($product->getInStock() + $quantityDiff);
            $this->entityManager->persist($product);
        }
    }

    public function removeProductDetail(OrderDetail $detail): void
    {
        // Do not increase instock for canceled orders
        if ($detail->getOrder() && $detail->getOrder()->getOrderStatus()->getId() === -1) {
            return;
        }

        $product = $this->getProductFromDetail($detail);
        if ($product) {
            $product->setInStock($product->getInStock() + $detail->getQuantity());
            $this->entityManager->persist($product);
        }
    }

    /**
     * Returns the product of the product position
     */
    protected function getProductFromDetail(OrderDetail $detail): ?ProductDetail
    {
        if (\in_array($detail->getMode(), [0, 1], true)) {
            if ($detail->getArticleDetail() && $detail->getArticleDetail()->getId()) { // After the detail got removed, the association to the product detail does not exist anymore.
                return $detail->getArticleDetail();
            } elseif ($detail->getArticleNumber()) {
                return $this->getProductByNumber($detail->getArticleNumber());
            }
        }

        return null;
    }

    /**
     * Returns a product by the ordernumber
     */
    protected function getProductByNumber(string $number): ?ProductDetail
    {
        $product = $this->entityManager->getRepository(\Shopware\Models\Article\Detail::class)->findOneBy(['number' => $number]);

        if ($product instanceof \Shopware\Models\Article\Detail) {
            return $product;
        }

        return null;
    }

    protected function updateProductInStock(string $productNumber, int $quantity): void
    {
        $product = $this->getProductByNumber($productNumber);
        if (!$product) {
            return;
        }

        $product->setInStock($product->getInStock() + $quantity);
        $this->entityManager->persist($product);
    }
}
