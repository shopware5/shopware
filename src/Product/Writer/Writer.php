<?php declare(strict_types=1);

namespace Shopware\Product\Writer;

class Writer
{
    /**
     * @var SqlGateway
     */
    private $gateway;
    /**
     * @var ProductFieldConfiguration
     */
    private $productFieldConfiguration;

    public function __construct(
        SqlGateway $gateway,
        ProductFieldConfiguration $productFieldConfiguration
    ) {
        $this->gateway = $gateway;
        $this->productFieldConfiguration = $productFieldConfiguration;
    }

    public function insert(array $rawData): void
    {
        $this->gateway->insert($rawData);

    }

    public function update(string $uuid, array $rawData): void
    {
        // not here
        // 1. deserialize - HTTP Responsibility

        // 2. Normalize -> extract ids from subresources

        // 3. type cast

        // 3. escaping / filtering - e.g. remove html input

        // 4. validation

        // 5. write
        $this->gateway->update($uuid, $rawData);
    }
}