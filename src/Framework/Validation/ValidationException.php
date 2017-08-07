<?php declare(strict_types=1);

namespace Shopware\B2B\Common\Validator;

use Exception;
use Shopware\B2B\Common\B2BException;
use Shopware\B2B\Common\Entity;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\ConstraintViolationListInterface;

class ValidationException extends \InvalidArgumentException implements B2BException
{
    /**
     * @var ConstraintViolationListInterface
     */
    private $violations;

    /**
     * @var array
     */
    private $sortedViolations = [];

    /**
     * @var Entity
     */
    private $entity;

    /**
     * @param Entity $entity
     * @param ConstraintViolationListInterface $violations
     * @param string $message
     * @param null $code
     * @param Exception|null $previous
     */
    public function __construct(
        Entity $entity,
        ConstraintViolationListInterface $violations,
        $message,
        $code = null,
        Exception $previous = null
    ) {
        $readableViolationList = [];

        /** @var ConstraintViolation $violation */
        foreach ($violations as $violation) {
            $fieldName = $violation->getPropertyPath();

            if (!isset($this->sortedViolations[$fieldName])) {
                $this->sortedViolations[$fieldName] = [];
            }

            $this->sortedViolations[$fieldName][] = $violation;
            $readableViolationList[] = $violation->getPropertyPath() . ': ' . $violation->getMessage();
        }

        parent::__construct($message . "\n\t" . implode("\n\t<br>", $readableViolationList), $code, $previous);

        $this->violations = $violations;
        $this->entity = $entity;
    }

    /**
     * @param string $fieldName
     * @param string $cause
     * @return bool
     */
    public function hasViolationsForFieldWithCause(string $fieldName, string $cause): bool
    {
        if (!isset($this->sortedViolations[$fieldName])) {
            return false;
        }

        /** @var ConstraintViolation $violation */
        foreach ($this->sortedViolations[$fieldName] as $violation) {
            if ($violation->getCause() === $cause) {
                return true;
            }
        }

        return false;
    }

    /**
     * @return ConstraintViolationListInterface
     */
    public function getViolations()
    {
        return $this->violations;
    }

    /**
     * @return Entity
     */
    public function getEntity(): Entity
    {
        return $this->entity;
    }
}
