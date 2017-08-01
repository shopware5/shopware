<?php declare(strict_types=1);

namespace Shopware\Product\Writer;

use Shopware\Product\Writer\Api\Field;
use Shopware\Product\Writer\Api\FieldCollection;

class Writer
{
    /**
     * @var SqlGateway
     */
    private $gateway;
    /**
     * @var ProductFieldConfiguration
     */
    private $fieldCollection;

    public function __construct(
        SqlGateway $gateway,
        FieldCollection $fieldCollection
    ) {
        $this->gateway = $gateway;
        $this->fieldCollection = $fieldCollection;
    }

    public function insert(array $rawData): void
    {
        $fields = $this->fieldCollection->getFields();

        $data = $this->filterInputKeys($rawData, $fields);

        $this->gateway->insert($data);

    }

    /**
     * not here
     * 1. deserialize - HTTP Responsibility
     *
     * @param string $uuid
     * @param array $rawData
     */
    public function update(string $uuid, array $rawData): void
    {
        $fields = $this->fieldCollection->getFields();

        // 2. Normalize Collection
        // 2.1 Extract ids from subresources in collection

        $data = [];
        foreach($fields as $field) {
            $name = $field->getName();

            // 2.2 filter unknown columns field based
            if(!array_key_exists($name, $rawData)) {
                continue;
            }

            $rawValue = $rawData[$name];

            // 3. escaping / filtering - e.g. remove html input, map to password, etc pp
            $filters = $field->getFilters();


            // 4. validation
            $constraints = $field->getUpdateConstraints();

            // 5. to database value
            $data[$field->getStorageName()] = $field->getValueTransformer()->transform($rawValue);
        }

        // 6. write
        $this->gateway->update($uuid, $data);
    }

    /**
     * @param array $rawData
     * @param $fields
     * @return array
     */
    protected function filterInputKeys(array $rawData, $fields): array
    {
        $fieldNames = array_map(function (Field $field) {
            return $field->getName();
        }, $fields);

        $data = array_filter($rawData, function (string $key) use ($fieldNames) {
            return false !== in_array($key, $fieldNames, true);
        }, ARRAY_FILTER_USE_KEY);

        return $data;
    }
}