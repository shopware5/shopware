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

namespace Shopware\Commands;

use Shopware\Bundle\PluginInstallerBundle\Context\LicenceRequest;
use Shopware\Bundle\PluginInstallerBundle\Struct\LicenceStruct;
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
class StoreListCommand extends StoreCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        parent::addConfigureShopwareVersion();
        parent::addConfigureAuth();
        parent::addConfigureHostname();

        $this
            ->setName('sw:store:list')
            ->setDescription('List licensed plugins.')
        ;
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $version = $this->setupShopwareVersion($input);
        $token   = $this->setupAuth($input, $output);
        $domain  = $this->setupDomain($input, $output);

        $context = new LicenceRequest(
            null,
            $version,
            $domain,
            $token
        );

        try {
            $licences = $this->container->get('shopware_plugininstaller.plugin_service_store_production')
                ->getLicences($context);
        } catch (\Exception $e) {
            $this->handleError([
                'message' => $e->getMessage()
            ]);
            return;
        }

        /**@var $licence LicenceStruct*/
        foreach ($licences as $licence) {
            $result[] = [
                'technicalName' => $licence->getTechnicalName(),
                'label'         => $licence->getLabel(),
                'domain'        => $licence->getShop(),
                'createDate'    => $licence->getCreationDate()->format('Y-m-d'),
                'type'          => $licence->getPriceModel()->getType()
            ];
        }

        $table = $this->getHelperSet()->get('table');
        $table->setHeaders(array('Technical name', 'Description', 'domain', 'Creation date', 'Type'))
              ->setRows($result);

        $table->render($output);
    }
}
