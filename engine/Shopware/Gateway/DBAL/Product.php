<?php

namespace Shopware\Gateway\DBAL;

use Shopware\Struct;

class Product extends ListProduct
{
    /**
     * @param $number
     * @param \Shopware\Struct\Context $context
     * @return Struct\Product
     */
    public function get($number, Struct\Context $context)
    {
        $products = $this->getList(array($number), $context);

        return array_shift($products);
    }

    /**
     * Returns a list of Product structs which used for example
     * on the article detail page.
     *
     * @param array $numbers
     * @param \Shopware\Struct\Context $context
     * @return Struct\Product[] indexed by product number
     */
    public function getList(array $numbers, Struct\Context $context)
    {
        $query = $this->getQuery($numbers, $context);

        /**@var $statement \Doctrine\DBAL\Driver\ResultStatement */
        $statement = $query->execute();

        $data = $statement->fetchAll(\PDO::FETCH_ASSOC);

        $products = array();
        foreach ($data as $product) {
            $key = $product['__variant_ordernumber'];

            $products[$key] = $this->hydrator->hydrateProduct($product);
        }

        return $products;
    }

}
