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
abstract class StoreCommand extends ShopwareCommand
{
    /**
     * @var OutputInterface
     */
    private $output;

    /**
     * @var InputInterface
     */
    private $input;

    /**
     * @var \CommunityStore
     */
    private $store;

    protected function addConfigureAuth()
    {
        $this
            ->addOption(
                'username',
                null,
                InputOption::VALUE_OPTIONAL
            )
            ->addOption(
                'password',
                null,
                InputOption::VALUE_OPTIONAL
            )
        ;
    }

    protected function addConfigureHostname()
    {
        $this->addOption(
            'hostname',
            null,
            InputOption::VALUE_OPTIONAL,
            'Override default shop domain.'
        );
    }

    /**
     * {@inheritdoc}
     */
    protected function addConfigureShopwareVersion()
    {
        $this->addOption(
            'shopware-version',
            null,
            InputOption::VALUE_OPTIONAL,
            'Override numeric shopware version eg. 4130.'
        );
    }

    /**
     * @param array $input
     * @throws \Exception
     */
    protected function handleError(array $input)
    {
        if (OutputInterface::VERBOSITY_VERBOSE <= $this->output->getVerbosity()) {
            $this->output->writeln(print_r($input, true));
        }

        throw new \Exception($input['message']);
    }

    /**
     * @param InputInterface $input
     * @return int|\Shopware_StoreApi_Models_Auth
     */
    protected function setupShopwareVersion(InputInterface $input)
    {
        $version = $input->getOption('shopware-version');

        if (!empty($version)) {
            $this->container->get('config')->offsetSet('version', (int) $version);
        }
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @throws \Exception
     * @return int|\Shopware_StoreApi_Models_Auth
     */
    protected function setupAuth(InputInterface $input, OutputInterface $output)
    {
        $this->output = $output;
        $this->input  = $input;

        /** @var \CommunityStore $store */
        $this->store = $this->container->get('CommunityStore');

        $username = $input->getOption('username');
        $password = $input->getOption('password');

        if ($input->isInteractive()) {
            $dialog = $this->getHelper('dialog');

            if (empty($username)) {
                $username = $dialog->ask(
                    $output,
                    'Please enter the username'
                );
            }

            if (empty($password)) {
                $password = $dialog->askHiddenResponse(
                    $output,
                    'Please enter the password'
                );
            }
        }

        if (empty($username) || empty($password)) {
            throw new \Exception("Username and password are required");
        }

        $output->writeln(sprintf("Connect to Store with username: %s...", $username));

        return $this->login($username, $password);
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @param \Shopware_StoreApi_Models_Auth $auth
     * @throws \Exception
     * @return int|\Shopware_StoreApi_Models_Domain
     */
    protected function setupDomain(InputInterface $input, OutputInterface $output, \Shopware_StoreApi_Models_Auth $auth)
    {
        $this->output = $output;
        $this->input  = $input;

        $hostname = $input->getOption('hostname');
        if (empty($hostname)) {
            $em = $this->container->get('models');
            $shop = $em->getRepository('Shopware\Models\Shop\Shop')->findOneBy(array('default' => true));
            $hostname = $shop->getHost();
        }

        if (empty($hostname)) {
            throw new \Exception("Hostname is required");
        }

        $output->writeln(sprintf("Connect to Domain: %s...", $hostname));
        return $this->connect($auth, $hostname);
    }


    /**
     * @param $user
     * @param $password
     * @return int|\Shopware_StoreApi_Models_Auth
     */
    private function login($user, $password)
    {
        $auth = $this->store->getAuthService()->login($user, $password);
        if ($auth instanceof \Shopware_StoreApi_Exception_Response) {
            $this->handleError(array(
                'success' => false,
                'source'  => 'auth',
                'code'    => $auth->getCode(),
                'message' => $auth->getMessage()
            ));
        }

        return $auth;
    }

    /**
     * @param \Shopware_StoreApi_Models_Auth $auth
     * @param string $hostname
     * @return int|\Shopware_StoreApi_Models_Domain
     */
    private function connect(\Shopware_StoreApi_Models_Auth $auth, $hostname)
    {
        /** @var $domain \Shopware_StoreApi_Models_Domain */
        $domain = $this->store->getAccountService()->getDomain(
            $auth,
            $hostname
        );

        if ($domain instanceof \Shopware_StoreApi_Exception_Response) {
            $this->handleError(array(
                'success' => false,
                'code'    => $domain->getCode(),
                'message' => "Your currently used shop domain isn't associated with your shopware account."
            ));
        }

        return $domain;
    }
}
