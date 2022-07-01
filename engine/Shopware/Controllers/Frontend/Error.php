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

use Shopware\Bundle\StoreFrontBundle\Service\ContextServiceInterface;
use Shopware\Components\CSRFTokenValidationException;
use Shopware\Components\CSRFWhitelistAware;
use Shopware\Components\Theme\PathResolver;

class Shopware_Controllers_Frontend_Error extends Enlight_Controller_Action implements CSRFWhitelistAware
{
    /**
     * Disable front plugins
     *
     * @return void
     */
    public function init()
    {
        $this->Front()->Plugins()->ScriptRenderer()->setRender(false);
        $this->Front()->Plugins()->ViewRenderer()->setNoRender(false);
        $this->Front()->Plugins()->Json()->setRenderer(false);
    }

    /**
     * Load correct template
     */
    public function preDispatch()
    {
        if ($this->Request()->getActionName() === 'service') {
            return;
        }
        $templateModule = 'frontend';
        if ($this->Request()->getModuleName() === 'backend') {
            $templateModule = 'backend';
            $this->enableBackendTheme();
        }

        if ($this->Request()->isXmlHttpRequest() || !$this->container->initialized('db')) {
            $this->View()->loadTemplate($templateModule . '/error/exception.tpl');
        } elseif (isset($_ENV['SHELL']) || PHP_SAPI === 'cli') {
            $this->View()->loadTemplate($templateModule . '/error/cli.tpl');
        } elseif (empty($_SERVER['SERVER_NAME'])) {
            $this->View()->loadTemplate($templateModule . '/error/ajax.tpl');
        } else {
            $this->View()->loadTemplate($templateModule . '/error/index.tpl');
        }

        if ($this->isCsrfValidationException()) {
            $backUrl = htmlspecialchars($_SERVER['HTTP_REFERER']);
            if (!empty($backUrl)) {
                $this->View()->assign('backUrl', $backUrl);
            }
            $this->View()->assign('isCsrfException', 'true');
        }
    }

    /**
     * Controller action that handles all error rendering
     * either by itself or by delegating specific scenarios to other actions
     *
     * @return void
     */
    public function errorAction()
    {
        $error = $this->Request()->getParam('error_handler');
        if (empty($error)) {
            return;
        }

        $code = $error->exception->getCode();
        switch ($code) {
            case Enlight_Controller_Exception::Controller_Dispatcher_Controller_Not_Found:
            case Enlight_Controller_Exception::Controller_Dispatcher_Controller_No_Route:
            case Enlight_Controller_Exception::PROPERTY_NOT_FOUND:
            case Enlight_Controller_Exception::ActionNotFound:
            case 404:
                $this->forward('pageNotFoundError');
                break;
            case 400:
            case 401:
            case 413:
                $this->forward('genericError', null, null, ['code' => $code]);
                break;
            default:
                $this->forward('genericError', null, null, ['code' => 503]);
                break;
        }
    }

    /**
     * Handles "Page Not Found" errors
     *
     * @return void
     */
    public function pageNotFoundErrorAction()
    {
        $response = $this->Response();

        $targetEmotionId = (int) $this->get('config')->get('PageNotFoundDestination');
        $targetErrorCode = $this->get('config')->get('PageNotFoundCode', 404);

        $response->setStatusCode($targetErrorCode);

        // Page not Found should not get logged in error handler
        $response->unsetExceptions();

        switch ($targetEmotionId) {
            case -2:
            case null:
                $this->forward(
                    $this->get('front')->Dispatcher()->getDefaultAction(),
                    $this->get('front')->Dispatcher()->getDefaultControllerName()
                );
                break;
            case -1:
                $this->forward('genericError', null, null, ['code' => $targetErrorCode]);
                break;
            default:
                // Try to load the emotion landingpage, render default error in case it is unavailable
                try {
                    $result = $this->get('shopware.emotion.emotion_landingpage_loader')->load(
                        $targetEmotionId,
                        $this->get(ContextServiceInterface::class)->getShopContext()
                    );

                    $this->View()->loadTemplate('frontend/campaign/index.tpl');
                    $this->View()->assign(json_decode((string) json_encode($result), true));
                } catch (Exception $ex) {
                    $this->forward(
                        $this->get('front')->Dispatcher()->getDefaultAction(),
                        $this->get('front')->Dispatcher()->getDefaultControllerName()
                    );
                }
        }
    }

    /**
     * Generic error handling controller action
     *
     * @return void
     */
    public function genericErrorAction()
    {
        $response = $this->Response();
        $errorCode = $this->Request()->getParam('code', 503);
        $response->setStatusCode($errorCode);

        if ($this->Request()->getModuleName() === 'frontend') {
            $this->View()->assign('Shop', $this->get('shop'));
        }

        $error = $this->Request()->getParam('error_handler');

        /*
         * If the system is configured to display the exception data, we need
         * to pass it to the template
        */
        if ($this->Front()->getParam('showException')) {
            $rootDir = $this->container->getParameter('shopware.app.rootDir');
            if (!\is_string($rootDir)) {
                throw new RuntimeException('Parameter shopware.app.rootDir has to be an string');
            }

            $path = $rootDir . '/';

            /** @var Exception $exception */
            $exception = $error->exception;
            $errorFile = $exception->getFile();
            $errorFile = str_replace($path, '', $errorFile);

            $errorTrace = $error->exception->getTraceAsString();
            $errorTrace = str_replace($path, '', $errorTrace);
            $this->View()->assign([
                'exception' => $exception,
                'error' => $exception->getMessage(),
                'error_message' => $exception->getMessage(),
                'error_file' => $errorFile,
                'error_trace' => $errorTrace,
            ]);
        }

        if ($this->View()->getAssign('success') !== null) {
            $this->Response()->setStatusCode(200);
            $this->View()->clearAssign('exception');
            $this->View()->assign('message', $error->exception->getMessage());
        }
    }

    /**
     * @return void
     */
    public function serviceAction()
    {
        $this->Response()->setStatusCode(503);
        $this->Response()->headers->set('retry-after', '1800');
    }

    /**
     * Returns a list with actions which should not be validated for CSRF protection
     *
     * @return string[]
     */
    public function getWhitelistedCSRFActions()
    {
        return [
            'error',
            'pageNotFoundError',
            'genericError',
            'service',
        ];
    }

    /**
     * Ensure the backend theme is enabled.
     * This is important in cases when a backend request uses the storefront context eg. "$shop->registerResources($this)".
     */
    private function enableBackendTheme(): void
    {
        $directory = $this->get(PathResolver::class)->getExtJsThemeDirectory();
        $this->get('template')->setTemplateDir([
            'backend' => $directory,
            'include_dir' => '.',
        ]);
    }

    /**
     * Checks if the Response contains a CSRF Token validation exception
     */
    private function isCsrfValidationException(): bool
    {
        $exceptions = $this->Response()->getException();
        if (empty($exceptions)) {
            return false;
        }
        foreach ($exceptions as $exception) {
            if ($exception instanceof CSRFTokenValidationException) {
                return true;
            }
        }

        return false;
    }
}
