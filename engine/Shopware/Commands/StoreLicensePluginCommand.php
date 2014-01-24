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

namespace Shopware\Commands;

use CommunityStore;
use Shopware\Components\Plugin\Manager;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @category  Shopware
 * @package   Shopware\Components\Console\Command
 * @copyright Copyright (c) shopware AG (http://www.shopware.de)
 */
class StoreLicensePluginCommand extends StoreCommand
{
    /**
     * @var \CommunityStore
     */
    private $store;

    /**
     * @var OutputInterface
     */
    private $output;

    /**
     * @var InputInterface
     */
    private $input;

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        parent::addConfigureShopwareVersion();
        parent::addConfigureAuth();
        parent::addConfigureHostname();

        $this
            ->setName('sw:store:licenseplugin')
            ->setDescription('Installs license plugin.')
        ;
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->output = $output;
        $this->input  = $input;

        if ($this->container->has('license')) {
            $output->writeln("LicenseManager already installed.");
            return 1;
        }

        /** @var \CommunityStore $store */
        $this->store = $store = $this->container->get('CommunityStore');

        $this->setupShopwareVersion($input);

        $auth   = $this->setupAuth($input, $output);
        $domain = $this->setupDomain($input, $output, $auth);

        $licensePlugin = $store->getLicensePlugin();
        if ($licensePlugin instanceof \Shopware_StoreApi_Exception_Response) {
            return $this->handleError(array(
                'success' => false,
                'code'    => $licensePlugin->getCode(),
                'message' => $licensePlugin->getMessage()
            ));
        }

        $this->buyProduct($domain, $auth, $licensePlugin, false, array('SwagLicense'), null);

        $licensedProduct = $this->getLicensedProduct($auth, $domain, $licensePlugin->getId());

        $this->downloadProduct($licensedProduct);

        $output->writeln("License manager downloaded.");

        /** @var Manager $pluginManager */
        $pluginManager  = $this->container->get('shopware.plugin_manager');

        $output->writeln("Refresh PluginList.");
        $pluginManager->refreshPluginList();

        $pluginName = 'SwagLicense';
        try {
            $plugin = $pluginManager->getPluginByName($pluginName);
        } catch (\Exception $e) {
            $output->writeln(sprintf('Unknown plugin: %s.', $pluginName));
            return 1;
        }

        $output->writeln(sprintf('Going to install: %s.', $plugin->getName()));

        if ($plugin->getInstalled()) {
            $output->writeln(sprintf('The plugin %s is already installed.', $pluginName));
            return 1;
        }

        $pluginManager->installPlugin($plugin);

        $output->writeln(sprintf('Plugin %s has been installed successfully.', $pluginName));

        $pluginManager->activatePlugin($plugin);

        $output->writeln(sprintf('Plugin %s has been activated successfully.', $pluginName));
    }

    /**
     * @param $auth
     * @param $domain
     * @param $productId
     * @return mixed|\Shopware_StoreApi_Exception_Response
     */
    private function getLicensedProduct(\Shopware_StoreApi_Models_Auth$auth, \Shopware_StoreApi_Models_Domain $domain, $productId)
    {
        //if the order process was successful we have to get the licenced product to get the available downloads.
        $licencedProduct = $this->store->getAccountService()->getLicencedProductById(
            $auth,
            $domain,
            $productId,
            $this->store->getNumericShopwareVersion()
        );

        //check if the product was found
        if ($licencedProduct instanceof \Shopware_StoreApi_Exception_Response) {
            $this->handleError(array(
                'source'  => 'licencedProduct',
                'code'    => $licencedProduct->getCode(),
                'message' => $licencedProduct->getMessage()
            ));
        }

        return $licencedProduct;
    }

    /**
     * @param \Shopware_StoreApi_Models_Domain $domain
     * @param \Shopware_StoreApi_Models_Auth $auth
     * @param $productId
     * @param $rentVersion
     * @param $pluginNames
     * @param $licence
     * @return array|\Shopware_StoreApi_Models_Order
     */
    private function buyProduct(
        \Shopware_StoreApi_Models_Domain $domain,
        \Shopware_StoreApi_Models_Auth $auth,
        \Shopware_StoreApi_Models_Product $product,
        $rentVersion,
        $pluginNames,
        $licence
    ) {
        //check if the user is logged in the community store and the token is valid
        if (!$this->store->getAuthService()->isTokenValid($auth)) {
            $this->handleError(array(
                'success'       => false,
                'loginRequired' => true
            ));
        }

        $licence = $licence . '';
//        //licence required and isn't licence plugin?
//        if (!empty($licence) && strlen($licence) > 0 && !in_array('SwagLicense', $pluginNames) ) {
//            die('never');
//            //the licence plugin requires the ionCubeLoader
//            if (!$this->isIonCubeLoaderLoaded()) {
//                return array(
//                    'success' => false,
//                    'noDecoder' => true
//                );
//            }
//
//            $localeLicensePlugin = $this->getLocaleLicensePlugin();
//
//            //license plugin exist on the shopware shop?
//            if (!$localeLicensePlugin instanceof \Shopware\Models\Plugin\Plugin) {
//                //return licensePluginRequired to send a new ajax request to buy the license plugin
//                return array(
//                    'success' => false,
//                    'licensePluginRequired' => true
//                );
//            } else {
//                /**@var $localeLicensePlugin \Shopware\Models\Plugin\Plugin*/
//                if ($localeLicensePlugin->getInstalled() === null) {
//                    $this->installPlugin($localeLicensePlugin);
//                }
//                if (!$localeLicensePlugin->getActive()) {
//                    $this->activatePlugin($localeLicensePlugin);
//                }
//            }
//        }

//        $domain = new \Shopware_StoreApi_Models_Domain(array(
//            'domain'     => $httpHost,
//            'account_id' =>  Shopware()->BackendSession()->pluginManagerAccountId
//        ));


        // after the domain resolved, we can perform the order
        /** @var $orderModel \Shopware_StoreApi_Models_Order*/
        $orderModel = $this->store->getOrderService()->orderProduct(
            $auth,
            $domain,
            $product,
            $rentVersion
        );

        //first we have to check if an request error occurred. This errors will be displayed in a growl message
        if ($orderModel instanceof \Shopware_StoreApi_Exception_Response) {
            $this->handleError(array(
                'code'    => $orderModel->getCode(),
                'message' => $this->getOrderExceptionMessage($orderModel->getCode())
            ));
        }

        //if the request was successfully but the order process wasn't successfully, the account data are not completed
        // for example: The user hasn't enough credits or the user bought the plugin already.
        if (!$orderModel->wasSuccessful()) {
            $this->handleError(array(
                'code'    => $orderModel->getErrorType(),
                'message' => $this->getOrderExceptionMessage($orderModel->getErrorType(), $orderModel->getErrorData())
            ));
        }

        return $orderModel;
    }

    /**
     * @param \Shopware_StoreApi_Models_Licence $licencedProduct
     * @return array
     */
    private function downloadProduct(\Shopware_StoreApi_Models_Licence $licencedProduct)
    {
        /**@var $licencedProduct \Shopware_StoreApi_Models_Licence*/
        $downloads = $licencedProduct->getDownloads();

        $namespace = Shopware()->Snippets()->getNamespace('backend/plugin_manager/main');

        if (empty($downloads)) {
            $this->handleError(array(
                'message' => $namespace->get('no_download', 'No download available')
            ));
        }

        $url = $downloads['download']['url'];

        $url = "http://localhost/SwagLicense.zip";
        $downloads['type'] = 'plain';

        if ($downloads['type'] === 'plain') {
            $result = $this->store->downloadPlugin($url);
            if ($result['success']) {
                return array(
                    'success' => true,
                    'license' => $licencedProduct->getLicence()
                );
            } else {
                return $result;
            }
        }

        if (!$this->isIonCubeLoaderLoaded()) {
            $this->handleError(array(
                'message' => 'Ioncube loader is requied to install this plugin.'
            ));
        }

        $result = $this->store->downloadPlugin($url);
        if ($result['success']) {
            return array(
                'success' => true,
                'license' => $licencedProduct->getLicence()
            );
        } else {
            return $result;
        }
    }

    /**
     * Helper function to check if the ion cube loader is loaded
     * @return bool
     */
    private function isIonCubeLoaderLoaded()
    {
        return extension_loaded('ionCube Loader');
    }

    /**
     * Internal helper function to check if the license plugin exist on the system.
     * @return mixed
     */
    private function getLocaleLicensePlugin()
    {
        $builder = Shopware()->Models()->createQueryBuilder();
        return $builder->select(array('plugin'))
            ->from('Shopware\Models\Plugin\Plugin', 'plugin')
            ->where('plugin.name = :name')
            ->setParameter('name', 'SwagLicense')
            ->setFirstResult(0)
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult(\Doctrine\ORM\AbstractQuery::HYDRATE_OBJECT);
    }

    /**
     * Internal helper function to map the StoreApi error codes to an helpfully error message.
     * @param      $code
     * @param null $errorData
     * @return string
     */
    private function getOrderExceptionMessage($code, $errorData = null)
    {
        $namespace = Shopware()->Snippets()->getNamespace('backend/plugin_manager/main');

        switch ($code) {
            case \Shopware_StoreApi_Exception_Response::ACCESS_FORBIDDEN:
                $message = $namespace->get('access_forbidden', 'Access prohibited – Token expired or insufficient rights.', true);
                break;
            case \Shopware_StoreApi_Exception_Response::DOMAIN_ACCESS_FORBIDDEN:
                $message = $namespace->get('domain_access_forbidden', 'Access to transferred domain denied.', true);
                break;
            case \Shopware_StoreApi_Exception_Response::PRODUCT_NOT_FOUND:
                $message = $namespace->get('product_not_found', 'Article could not be found.', true);
                break;
            case \Shopware_StoreApi_Exception_Response::NO_RENT_VERSION_AVAILABLE:
                $message = $namespace->get('no_rent_version_available', 'No rental version of this article existing.', true);
                break;
            case \Shopware_StoreApi_Exception_Response::PRODUCT_COULD_NOT_ADDED:
                $message = $namespace->get('product_could_not_added', 'Article could not be added to store shopping basket.', true);
                break;
            case \Shopware_StoreApi_Models_Order::BILLING_ADDRESS_INCOMPLETE:
                $link = "http://account.shopware.de";
                $message = $namespace->get('billing_address_incomplete', "Please complete your billing information and contact details under <a href='http://account.shopware.de' target='_blank'>account.shopware.de</a> first.", true);
                $message = array(
                    'link' => $link,
                    'message' => $message
                );
                break;
            case \Shopware_StoreApi_Models_Order::TRADE_TERMS_NOT_ACCEPTED:
                $link = "http://account.shopware.de";
                $message =  $namespace->get('trade_terms_not_accepted', "Please accept the terms and conditions under <a href='http://account.shopware.de' target='_blank'>account.shopware.de</a> first.", true);
                $message = array(
                    'link' => $link,
                    'message' => $message
                );
                break;
            case \Shopware_StoreApi_Models_Order::PRODUCT_ALREADY_BOUGHT:
                $message = $namespace->get('product_already_bought', 'You have already purchased this module!');
                $message = array(
                    'link' => null,
                    'message' => $message
                );
                break;
            case \Shopware_StoreApi_Models_Order::CREDITS_NOT_ENOUGH:
                $message = $namespace->get('credits_not_enough', "The order value is %s EUR. Please charge %s EUR to purchase the article.");
                $message = sprintf($message, str_replace('.', ',', $errorData['basket_amount']), str_replace('.', ',', $errorData['amount_difference']));
                $message = array(
                    'link' => $errorData['charge_link'],
                    'message' => $message
                );
                break;
            default:
                $message = $namespace->get('unknown_error', "Unknown error");
                break;
        }

        return $message;
    }
}
