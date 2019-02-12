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
use Shopware\Models\Order\Detail;

class StockService implements StockServiceInterface
{
    /** @var ModelManager */
    protected $entityManager;

    public function __construct(ModelManager $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function addProductDetail(Detail $detail)
    {
        $product = $this->getProductFromDetail($detail);
        if ($product) {
            $product->setInStock($product->getInStock() - $detail->getQuantity());
            $this->entityManager->persist($product);
            $this->entityManager->flush();
        }
    }

    public function updateProductDetail(Detail $detail, $oldProductNumber = null, $oldQuantity = null, $newProductNumber = null, $newQuantity = null)
    {
        $oldQuantity = $oldQuantity === 0 || $oldQuantity > 0 ? $oldQuantity : $detail->getQuantity();
        $newQuantity = $newQuantity === 0 || $newQuantity > 0 ? $newQuantity : $detail->getQuantity();

        // If the position product has been changed, the old product stock must be increased based on the (old) ordering quantity.
        // The stock of the new product will be reduced by the (new) ordered quantity.
        if ($newProductNumber != $oldProductNumber) {
            //if the old product is a product in the stock, we must increase the stock to the original stock size

            $this->updateProductInStock($oldProductNumber, $oldQuantity);
            $this->updateProductInStock($newProductNumber, -$newQuantity);
        } elseif ($oldQuantity != $newQuantity) {
            $product = $this->getProductFromDetail($detail);
            if (!$product) {
                return;
            }

            //if the product is a product in the stock, we must change the stock size to the new ordered quantity
            $quantityDiff = $oldQuantity - $newQuantity;

            $product->setInStock($product->getInStock() + $quantityDiff);
            $this->entityManager->persist($product);
        }
    }

    public function removeProductDetail(Detail $detail)
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
     * returns the product of the product position
     *
     * @param Detail $detail
     *
     * @return \Shopware\Models\Article\Detail|null
     */
    protected function getProductFromDetail(Detail $detail)
    {
        if (in_array($detail->getMode(), [0, 1], true)) {
            if ($detail->getArticleDetail() && $detail->getArticleDetail()->getId()) { // after the detail got removed, the association to the product detail does not exist anymore.
                return $detail->getArticleDetail();
            } elseif ($detail->getArticleNumber()) {
                return $this->getProductByNumber($detail->getArticleNumber());
            }
        }

        return null;
    }

    /**
     * returns a product by the ordernumber
     *
     * @param $number
     *
     * @return \Shopware\Models\Article\Detail|null
     */
    protected function getProductByNumber($number)
    {
        $product = $this->entityManager->getRepository(\Shopware\Models\Article\Detail::class)->findOneBy(['number' => $number]);

        if ($product instanceof \Shopware\Models\Article\Detail) {
            return $product;
        }

        return null;
    }

    /**
     * @param string $productNumber
     * @param int    $quantity
     */
    protected function updateProductInStock($productNumber, $quantity)
    {
        $product = $this->getProductByNumber($productNumber);
        if ($product) {
            $product->setInStock($product->getInStock() + $quantity);
            $this->entityManager->persist($product);
        }
    }
}
