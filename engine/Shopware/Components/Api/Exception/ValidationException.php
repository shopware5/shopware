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

namespace Shopware\Components\Api\Exception;

use Enlight_Exception;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormErrorIterator;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\ConstraintViolationListInterface;

class ValidationException extends Enlight_Exception implements ApiException
{
    /**
     * @deprecated - Will be native type hinted in Shopware 5.8 and not nullable anymore
     *
     * @var ConstraintViolationListInterface|null
     */
    protected $violations = null;

    public function __construct(ConstraintViolationListInterface $violations)
    {
        $this->setViolations($violations);

        parent::__construct((string) $this, Response::HTTP_BAD_REQUEST);
    }

    /**
     * @return string
     */
    public function __toString()
    {
        $output = '';

        if (!$this->violations instanceof ConstraintViolationListInterface) {
            return $output;
        }

        foreach ($this->violations as $violation) {
            $output .= $violation->getPropertyPath() . ': ' . $violation->getMessage() . PHP_EOL;
        }

        return $output;
    }

    /**
     * @deprecated - Will be native type hinted in Shopware 5.8
     *
     * @param ConstraintViolationListInterface $violations
     *
     * @return void
     */
    public function setViolations($violations)
    {
        $this->violations = $violations;
    }

    /**
     * @deprecated - Will be native type hinted in Shopware 5.8 and will not return null anymore
     *
     * @return ConstraintViolationListInterface|null
     */
    public function getViolations()
    {
        return $this->violations;
    }

    /**
     * @return ValidationException
     */
    public static function createFromFormError(FormErrorIterator $errors)
    {
        $violations = [];

        foreach ($errors as $error) {
            if (!$error instanceof FormError) {
                continue;
            }
            $message = Shopware()->Template()->fetch('string:' . $error->getMessage());

            $origin = $error->getOrigin();
            $violations[] = new ConstraintViolation(
                $message,
                $error->getMessageTemplate(),
                $error->getMessageParameters(),
                $origin ? $origin->getRoot() : null,
                $origin ? (string) $origin->getPropertyPath() : null,
                $origin ? $origin->getData() : null,
                $error->getMessagePluralization(),
                null,
                null,
                $error->getCause()
            );
        }

        return new self(new ConstraintViolationList($violations));
    }
}
