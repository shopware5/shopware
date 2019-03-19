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

use Shopware\Components\Api\Exception as ApiException;

/**
 * REST API Error Handler
 */
class Shopware_Controllers_Api_Error extends Shopware_Controllers_Api_Rest
{
    public function invalidAction()
    {
        $this->View()->assign(['success' => false, 'message' => 'Invalid method or invalid json string.']);
    }

    public function noAuthAction()
    {
        $this->View()->assign(['success' => false, 'message' => 'Invalid or missing auth']);
    }

    /**
     * Error Action to catch all Exceptions and return valid json response
     */
    public function errorAction()
    {
        $error = $this->Request()->getParam('error_handler');

        /** @var \Exception $exception */
        $exception = $error->exception;

        if ($exception instanceof Enlight_Controller_Exception) {
            $this->Response()->setStatusCode(404);

            $this->View()->assign([
                'success' => false,
                'message' => 'Resource not found',
            ]);

            return;
        }

        if ($exception instanceof ApiException\PrivilegeException) {
            $this->Response()->setStatusCode(403);

            $this->View()->assign([
                'success' => false,
                'message' => $exception->getMessage(),
            ]);

            return;
        }

        if ($exception instanceof ApiException\NotFoundException) {
            $this->Response()->setStatusCode(404);

            $this->View()->assign([
                'success' => false,
                'message' => $exception->getMessage(),
            ]);

            return;
        }

        if ($exception instanceof ApiException\ParameterMissingException) {
            $this->Response()->setStatusCode(400);

            if ($exception->getMissingParam() === null) {
                $this->View()->assign([
                   'success' => false,
                   'code' => 400,
                   'message' => 'A required parameter is missing',
                ]);
            } else {
                $this->View()->assign([
                   'success' => false,
                   'code' => 400,
                   'message' => sprintf('A required parameter is missing: %s', $exception->getMissingParam()),
                ]);
            }

            return;
        }

        if ($exception instanceof ApiException\CustomValidationException) {
            $this->Response()->setStatusCode(400);

            $this->View()->assign([
                'success' => false,
                'message' => $exception->getMessage(),
            ]);

            return;
        }

        if ($exception instanceof ApiException\ValidationException) {
            /* @var \Shopware\Components\Api\Exception\ValidationException $exception */
            $this->Response()->setStatusCode(400);

            $errors = [];
            /** @var \Symfony\Component\Validator\ConstraintViolation $violation */
            foreach ($exception->getViolations() as $violation) {
                $errors[] = sprintf(
                    '%s: %s',
                    $violation->getPropertyPath(),
                    $violation->getMessage()
                );
            }

            $this->View()->assign([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $errors,
            ]);

            return;
        }

        if ($exception instanceof ApiException\BatchInterfaceNotImplementedException) {
            $this->Response()->setStatusCode(405);
            $this->View()->assign([
                'success' => false,
                'code' => 405,
                'message' => 'This resource has no support for batch operations.',
            ]);

            return;
        }

        $this->Response()->setStatusCode(500);
        $this->View()->assign(['success' => false, 'message' => 'Error message: ' . $exception->getMessage()]);
    }
}
