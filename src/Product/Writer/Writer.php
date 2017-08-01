<?php declare(strict_types=1);

namespace Shopware\Product\Writer;

use Shopware\Product\Writer\Api\Field;
use Shopware\Product\Writer\Api\FieldCollection;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class Writer
{
    /**
     * @var SqlGateway
     */
    private $gateway;

    /**
     * @var FieldCollection
     */
    private $fieldCollection;
    /**
     * @var ValidatorInterface
     */
    private $validator;

    /**
     * @param SqlGateway $gateway
     * @param FieldCollection $fieldCollection
     */
    public function __construct(
        SqlGateway $gateway,
        FieldCollection $fieldCollection,
        ValidatorInterface $validator
    ) {
        $this->gateway = $gateway;
        $this->fieldCollection = $fieldCollection;
        $this->validator = $validator;
    }

    public function insert(array $rawData): void
    {
        $data = $this->filterInputKeys($rawData);

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

            // 2.2 filter unknown columns field based -- OK
            if(!array_key_exists($name, $rawData)) {
                continue;
            }

            $rawValue = $rawData[$name];

            // 3. escaping / filtering - e.g. remove html input, map to password, etc pp --- OK
            $rawValue = $this->applyFilters($field->getFilters(), $rawValue);

            // 4. validation
            $violations = $this->applyValidation($field->getUpdateConstraints(), $field->getName(), $rawValue);

            if(count($violations)) {
                throw new \InvalidArgumentException(sprintf('The value for %s is invalid', $field->getName()));
            }

            // 5. to database value -- OK
            $data[$field->getStorageName()] = $field->getValueTransformer()->transform($rawValue);
        }

        // 6. write
        $this->gateway->update($uuid, $data);
    }

    protected function applyValidation(array $constraints, string $fieldName, $value)
    {
        $violationList = new ConstraintViolationList();

        foreach($constraints as $constraint) {
            $violations = $this->validator
                ->validate($value, $constraint);

            /** @var ConstraintViolation $violation */
            foreach ($violations as $violation) {
                $violationList->add(
                    new ConstraintViolation(
                        $violation->getMessage(),
                        $violation->getMessageTemplate(),
                        $violation->getParameters(),
                        $violation->getRoot(),
                        $fieldName,
                        $violation->getInvalidValue(),
                        $violation->getPlural(),
                        $violation->getCode(),
                        $violation->getConstraint(),
                        $violation->getCause()
                    )
                );
            }
        }

        return $violationList;
    }

    /**
     * @param array $filters
     * @param $value
     * @return mixed
     */
    protected function applyFilters(array $filters, $value)
    {
        foreach($filters as $filter) {
            $value = $filter->filter($value);
        }

        return $value;
    }

    /**
     * @param array $rawData
     * @param $fields
     * @return array
     */
    protected function filterInputKeys(array $rawData): array
    {
        $fieldNames = $this->fieldCollection->getFieldNames();

        $data = array_filter($rawData, function (string $key) use ($fieldNames) {
            return false !== in_array($key, $fieldNames, true);
        }, ARRAY_FILTER_USE_KEY);

        return $data;
    }
}