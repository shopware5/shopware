<?php
/**
 * Shopware 4.0
 * Copyright © 2013 shopware AG
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

namespace Shopware\Components\Console\Command;

use CommunityStore;
use Shopware\Components\Plugin\Installer;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @category  Shopware
 * @package   Shopware\Components\Console\Command
 * @copyright Copyright (c) 2013, shopware AG (http://www.shopware.de)
 */
class StoreDownloadUpdateCommand extends StoreCommand
{
    protected $store;

    protected $output;

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        parent::addConfigureShopwareVersion();
        parent::addConfigureAuth();
        parent::addConfigureHostname();

        $this
            ->setName('sw:store:download:update')
            ->setDescription('Downloads plugin update.')
            ->addOption(
                'plugin-id',
                null,
                InputOption::VALUE_REQUIRED
            )
        ;
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->setupShopwareVersion($input);

        $auth   = $this->setupAuth($input, $output);
        $domain = $this->setupDomain($input, $output, $auth);

        $this->output = $output;

        /** @var \CommunityStore $store */
        $this->store = $store = $this->container->get('CommunityStore');

        $pluginId = $input->getOption('plugin-id');

        $product = $this->getLicensedProduct($auth, $domain, $pluginId);

        /** @var Installer $installer */
        $installer  = $this->container->get('shopware.plugin_installer');

        $result = $this->downloadProduct($product);

        $installer->refreshPluginList();
    }

    /**
     * @param $auth
     * @param $domain
     * @param $productId
     * @return mixed|\Shopware_StoreApi_Exception_Response
     */
    private function getLicensedProduct(\Shopware_StoreApi_Models_Auth $auth, \Shopware_StoreApi_Models_Domain $domain, $productId)
    {
        //if the order process was successful we have to get the licenced product to get the available downloads.
        /** @var \Shopware_StoreApi_Models_Licence $licencedProduct */
        $licencedProduct = $this->store->getAccountService()->getLicencedProductById(
            $auth,
            $domain,
            $productId,
            $this->store->getNumericShopwareVersion()
        );

        if ($licencedProduct->getLicence()
            && $this->container->has('license')
            && $this->getApplication()->has('swaglicense:license:import')
        ) {
            /** @var $license \Shopware_Components_License */
            $license = $this->container->get('license');
            $info = $license->readLicenseInfo($licencedProduct->getLicence());

            /** @var \Shopware\Plugin\SwagLicense\Command\LicenseAddCommand $command */
            $command = $this->getApplication()->get('swaglicense:license:import');
            $command->saveLicense($info, $this->output);
        }

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
}
