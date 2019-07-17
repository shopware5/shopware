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

namespace Shopware\Components;

use Enlight\Event\SubscriberInterface;
use Enlight_Event_EventManager as EnlightEventManager;
use Psr\Log\LoggerInterface;

/**
 * Subscriber to catch possibly occurring exceptions in the controller.
 * It will catch the exceptions, pass them to an error controller and display them.
 */
class ErrorSubscriber implements SubscriberInterface
{
    /**
     * Are we already inside the error handler loop?
     *
     * @var bool
     */
    private $isInsideErrorHandlerLoop = false;

    /**
     * Exception count logged at first invocation of plugin
     *
     * @var int
     */
    private $exceptionCountAtFirstEncounter = 0;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var EnlightEventManager
     */
    private $eventManager;

    /**
     * @var string[]
     */
    private $ignoredExceptionClasses;

    public function __construct(LoggerInterface $logger, EnlightEventManager $eventManager, array $ignoredExceptionClasses = [])
    {
        $this->logger = $logger;
        $this->eventManager = $eventManager;
        $this->ignoredExceptionClasses = $ignoredExceptionClasses;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            'Enlight_Controller_Front_RouteShutdown' => ['handleError', 500],
            'Enlight_Controller_Front_PostDispatch' => ['handleError', 500],
        ];
    }

    /**
     * @throws \Exception
     */
    public function handleError(\Enlight_Controller_EventArgs $args)
    {
        $front = $args->getSubject();
        $request = $args->getRequest();

        if ($front->getParam('noErrorHandler')) {
            return;
        }

        $response = $front->Response();

        if ($this->isInsideErrorHandlerLoop) {
            $exceptions = $response->getException();
            if (count($exceptions) > $this->exceptionCountAtFirstEncounter) {
                // Exception thrown by error handler; tell the front controller to throw it
                $front->throwExceptions(true);
                throw array_pop($exceptions);
            }

            if (is_array($exceptions)) {
                $last = array_pop($exceptions);

                if ($this->eventManager->notifyUntil('Shopware_Components_Error_Log', [
                    'exception' => $last,
                ])) {
                    return false;
                }

                // Make sure this is an Exception and also no minor one
                if ($last instanceof \Exception
                    && !in_array($last->getCode(), [
                    \Enlight_Controller_Exception::ActionNotFound,
                    \Enlight_Controller_Exception::Controller_Dispatcher_Controller_Not_Found,
                    \Enlight_Controller_Exception::Controller_Dispatcher_Controller_No_Route,
                    \Enlight_Controller_Exception::NO_ROUTE,
                    ], true)
                    && !in_array(get_class($last), $this->ignoredExceptionClasses, true) // Check for exceptions to be ignored
                ) {
                    if ($last instanceof CSRFTokenValidationException) {
                        $this->logger->warning($last->getMessage());
                    } else {
                        $this->logger->critical($last->getMessage());
                    }
                }
            }

            return;
        }

        if (!$response->isException()) {
            return;
        }

        $this->isInsideErrorHandlerLoop = true;

        // Get exception information
        $error = new \ArrayObject([], \ArrayObject::ARRAY_AS_PROPS);
        $exceptions = $response->getException();
        $exception = $exceptions[0];
        $error->exception = $exception;

        // Keep a copy of the original request
        $error->request = clone $request;

        // Get a count of the number of exceptions encountered
        $this->exceptionCountAtFirstEncounter = count($exceptions);

        // Forward to the error handler
        $request->setParam('error_handler', $error)
            ->setControllerName('error')
            ->setActionName('error')
            ->setDispatched(false);
    }
}
