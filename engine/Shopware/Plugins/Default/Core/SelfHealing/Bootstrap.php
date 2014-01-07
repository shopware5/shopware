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
 * @category  Shopware
 * @package   Shopware\Plugins\Core
 * @copyright Copyright (c) shopware AG (http://www.shopware.de)
 */
class Shopware_Plugins_Core_SelfHealing_Bootstrap extends Shopware_Components_Plugin_Bootstrap
{
    const redirectCookieString = 'ShopwarePluginsCoreSelfHealingRedirect';

    /**
     * Class property which contains the enlight http response object
     *
     * @var Enlight_Controller_Response_ResponseHttp
     */
    protected $response = null;

    /**
     * Class property which contains the enlight http request object
     *
     * @var Enlight_Controller_Request_RequestHttp
     */
    protected $request = null;

    /**
     * Standard plugin install function.
     * @return bool
     */
    public function install()
    {
        $this->subscribeEvent(
            'Enlight_Controller_Front_RouteShutdown',
            'onDispatchEvent',
            100
        );

        $this->subscribeEvent(
            'Enlight_Controller_Front_PostDispatch',
            'onDispatchEvent',
            100
        );

        $this->subscribeEvent(
            'Enlight_Controller_Front_DispatchLoopShutdown',
            'onDispatchEvent',
            100
        );

        return true;
    }

    /**
     * Returns capabilities so the plugin is default not installable and hidden in the plugin manager
     */
    public function getCapabilities()
    {
        return array(
            'install' => false,
            'enable' => false,
            'update' => true
        );
    }

    /**
     * Listener method for the Enlight_Controller_Front_PostDispatch event.
     *
     * @param   Enlight_Event_EventArgs $args
     * @return  void
     */
    public function onDispatchEvent(Enlight_Event_EventArgs $args)
    {
        if (!$args->getResponse()->isException()) {
            return;
        }

        $exception = $args->getResponse()->getException();
        $this->handleException($exception[0]);
    }

    /**
     *
     * @param $exception Exception
     *
     * @throws Exception
     * @return void
     */
    public function handleException($exception)
    {
        $this->request = new Enlight_Controller_Request_RequestHttp();
        $this->response = new Enlight_Controller_Response_ResponseHttp();

        if ($this->isModelException($exception)) {
            $path = Shopware()->Models()->getConfiguration()->getAttributeDir();

            $result = $this->generateModels($path);
            if ($result['success'] === true) {
                $this->response->setRedirect(
                    $this->request->getRequestUri()
                );

                setcookie(self::redirectCookieString, true, time() + 5);
                $this->response->sendResponse();
                exit();
            } else {
                die(sprintf("Failed to create the attribute models, please check the permissions of the '%s' directory", $path));
            }
        }
    }

    /**
     * Helper function to validate if the thrown exception is an shopware attribute model exception.
     *
     * @param $exception Exception
     *
     * @return bool
     */
    private function isModelException(Exception $exception)
    {
        if (isset($_COOKIE[self::redirectCookieString])) {
            return false;
        }

        /**
         * This case matches, when a query selects a doctrine association, which isn't defined in the doctrine model
         */
        if ($exception instanceof \Doctrine\ORM\Query\QueryException && strpos($exception->getMessage(), 'Shopware\Models\Attribute')) {
            return true;
        }

        /**
         * This case matches, when a doctrine attribute model don't exist
         */
        if ($exception instanceof ReflectionException && strpos($exception->getMessage(), 'Shopware\Models\Attribute')) {
            return true;
        }

        /**
         * This case matches, when a doctrine model field defined which not exist in the database
         */
        if ($exception instanceof PDOException && strpos($exception->getFile(), '/Doctrine/DBAL/')) {
            return true;
        }

        /**
         * This case matches, when a parent model selected and the child model loaded the attribute over the lazy loading process.
         */
        if ($exception instanceof \Doctrine\ORM\Mapping\MappingException && strpos($exception->getMessage(), 'Shopware\Models\Attribute')) {
            return true;
        }

        return false;
    }

    /**
     * Internal helper function which regenerates all shopware attribute model.
     */
    private function generateModels($path)
    {
        /** @var $generator \Shopware\Components\Model\Generator */
        $generator = new \Shopware\Components\Model\Generator();

        $generator->setPath($path);
        $generator->setModelPath(Shopware()->AppPath('Models'));
        $generator->setSchemaManager(
            $this->Application()->Models()->getConnection()->getSchemaManager()
        );

        return $generator->generateAttributeModels(array());
    }
}
