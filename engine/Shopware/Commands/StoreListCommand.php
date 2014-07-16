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
        $this->setupShopwareVersion($input);

        $auth   = $this->setupAuth($input, $output);
        $domain = $this->setupDomain($input, $output, $auth);

        /** @var \CommunityStore $store */
        $store = $this->container->get('CommunityStore');

        $resultSet = $store->getAccountService()->getLicencedProducts(
            $auth,
            $domain,
            $store->getNumericShopwareVersion()
        );

        $products = array();

        /** @var $product \Shopware_StoreApi_Models_Licence */
        foreach ($resultSet as $product) {
            $data = $product->getRawData();

            $payed = (int) $data['payed'];
            if ($payed === 1) {
                if (empty($data['downloads'])) {
                    continue;
                }

                $products[] = array(
                    'id'          => $data['id'],
                    'ordernumber' => $data['ordernumber'],
                    'plugin'      => $data['plugin'],
                );
            }
        }

        $table = $this->getHelperSet()->get('table');
        $table->setHeaders(array('id', 'OrderNumber', 'Name'))
              ->setRows($products);

        $table->render($output);
    }
}
