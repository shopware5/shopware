<?php
/**
 * Shopware 4
 * Copyright © shopware AG
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

/**
 */
class Shopware_Controllers_Frontend_Error extends Enlight_Controller_Action
{
    /**
     * Disable front plugins
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
        if ($this->Request()->getActionName() !== 'service') {
            if (strpos($this->Request()->getHeader('Content-Type'), 'application/json') === 0) {
                $this->Front()->Plugins()->Json()->setRenderer();
                $this->View()->assign('success', false);
            } elseif ($this->Request()->isXmlHttpRequest() || !Shopware()->Bootstrap()->issetResource('Db')) {
                $this->View()->loadTemplate('frontend/error/exception.tpl');
            } elseif (isset($_ENV['SHELL']) || empty($_SERVER['SERVER_NAME'])) {
                $this->View()->loadTemplate('frontend/error/ajax.tpl');
            } else {
                $this->View()->loadTemplate('frontend/error/index.tpl');
            }
        }
    }

    public function cliAction()
    {
        $this->view->setTemplate();

        $response = new Enlight_Controller_Response_ResponseCli();
        $response->appendBody(strip_tags($this->View()->exception) . "\n");

        $this->front->setResponse($response);
    }

    public function errorAction()
    {
        $error = $this->Request()->getParam('error_handler');

        if (!empty($error)) {
            if ($this->Front()->getParam('showException')) {
                $paths = array(Enlight()->Path(), Enlight()->AppPath(), Enlight()->OldPath());
                $replace = array('', Enlight()->App() . '/', '');

                $exception = $error->exception;
                $error_file = $exception->getFile();
                $error_file = str_replace($paths, $replace, $error_file);

                $error_trace = $error->exception->getTraceAsString();
                $error_trace = str_replace($paths, $replace, $error_trace);
                $this->View()->assign(array(
                    'exception' => $exception,
                    'error_message' => $exception->getMessage(),
                    'error_file' => $error_file,
                    'error_trace' => $error_trace
                ));
            }

            $code = $error->exception->getCode();
            switch ($code) {
                case 404:
                case 401:
                    $this->Response()->setHttpResponseCode($code);
                    break;
                default:
                    $this->Response()->setHttpResponseCode(503);
                    break;
            }

            if ($this->View()->getAssign('success') !== null) {
                $this->Response()->setHttpResponseCode(200);
                $this->View()->clearAssign('exception');
                $this->View()->assign('message', $error->exception->getMessage());
            }
        }
    }

    public function serviceAction()
    {
        $this->Response()->setHttpResponseCode(503);
    }
}
