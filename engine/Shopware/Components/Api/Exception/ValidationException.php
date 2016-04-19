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

namespace Shopware\Components\Api\Exception;

use Symfony\Component\Form\FormErrorIterator;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\ConstraintViolationListInterface;

/**
 * API Exception
 *
 * @category  Shopware
 * @package   Shopware\Components\Api\Exception
 * @copyright Copyright (c) shopware AG (http://www.shopware.de)
 */
class ValidationException extends \Enlight_Exception
{
    /**
     * @var ConstraintViolationListInterface
     */
    protected $violations = null;

    /**
     * @param ConstraintViolationListInterface $violations
     */
    public function __construct(ConstraintViolationListInterface $violations)
    {
        $this->setViolations($violations);

        parent::__construct();
    }

    /**
     * @param ConstraintViolationListInterface $violations
     */
    public function setViolations($violations)
    {
        $this->violations = $violations;
    }

    /**
     * @return ConstraintViolationListInterface
     */
    public function getViolations()
    {
        return $this->violations;
    }

    /**
     * @param FormErrorIterator $errors
     * @return ValidationException
     */
    public static function createFromFormError(FormErrorIterator $errors)
    {
        $violations = [];

        foreach ($errors as $error) {
            $violations[] = new ConstraintViolation(
                $error->getMessage(),
                $error->getMessageTemplate(),
                $error->getMessageParameters(),
                $error->getOrigin()->getRoot(),
                $error->getOrigin()->getPropertyPath(),
                $error->getOrigin()->getData(),
                $error->getMessagePluralization(),
                null,
                null,
                $error->getCause()
            );
        }

        return new self(new ConstraintViolationList($violations));
    }

    /**
     * @return string
     */
    public function __toString()
    {
        $output = "";

        foreach ($this->violations as $violation) {
            $output .= $violation->getMessage().PHP_EOL;
        }

        return $output;
    }
}
