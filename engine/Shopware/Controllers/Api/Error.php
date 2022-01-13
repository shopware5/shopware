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

use Shopware\Components\Api\Exception\ApiException;
use Shopware\Components\Api\Exception\NonUniqueIdentifierUsedException;
use Shopware\Components\Api\Exception\ValidationException;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Validator\ConstraintViolationListInterface;

/**
 * REST API Error Handler
 */
class Shopware_Controllers_Api_Error extends Shopware_Controllers_Api_Rest
{
    /**
     * @return void
     */
    public function invalidAction()
    {
        $this->View()->assign(['success' => false, 'message' => 'Invalid method or invalid json string.']);
    }

    /**
     * @return void
     */
    public function noAuthAction()
    {
        $this->View()->assign(['success' => false, 'message' => 'Invalid or missing auth']);
    }

    /**
     * Error Action to catch all Exceptions and return valid json response
     *
     * @return void
     */
    public function errorAction()
    {
        $exception = $this->Request()->getParam('error_handler')->exception;

        if ($exception instanceof ApiException && $exception instanceof Enlight_Exception) {
            $this->handleApiExceptions($exception);

            return;
        }

        if ($exception instanceof Enlight_Controller_Exception) {
            $this->Response()->setStatusCode(Response::HTTP_NOT_FOUND);

            $this->View()->assign([
                'success' => false,
                'message' => 'Resource not found',
            ]);

            return;
        }

        $this->Response()->setStatusCode(Response::HTTP_INTERNAL_SERVER_ERROR);
        $this->View()->assign(['success' => false, 'message' => 'Error message: ' . $exception->getMessage()]);
    }

    private function handleApiExceptions(Enlight_Exception $exception): void
    {
        $code = (int) $exception->getCode();
        if ($code === 0) {
            $code = Response::HTTP_INTERNAL_SERVER_ERROR;
        }
        $message = $exception->getMessage();

        if ($exception instanceof ValidationException) {
            $message = 'Validation error';

            if ($exception->getViolations() instanceof ConstraintViolationListInterface) {
                $errors = [];
                foreach ($exception->getViolations() as $violation) {
                    $errors[] = sprintf(
                        '%s: %s',
                        $violation->getPropertyPath(),
                        $violation->getMessage()
                    );
                }

                $this->View()->assign('errors', $errors);
            }
        }

        if ($exception instanceof NonUniqueIdentifierUsedException) {
            $this->View()->assign('foundIds', $exception->getAlternativeIds());
        }

        $this->Response()->setStatusCode($code);
        $this->View()->assign([
            'success' => false,
            'message' => $message,
            'code' => $code,
        ]);
    }
}
