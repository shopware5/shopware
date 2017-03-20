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

use Shopware\Bundle\PluginInstallerBundle\Struct\AccessTokenStruct;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;

/**
 * @category  Shopware
 *
 * @copyright Copyright (c) shopware AG (http://www.shopware.de)
 */
abstract class StoreCommand extends ShopwareCommand
{
    /**
     * @var OutputInterface
     */
    protected $output;

    /**
     * @var InputInterface
     */
    protected $input;

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
     *
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
     *
     * @return string
     */
    protected function setupShopwareVersion(InputInterface $input)
    {
        $version = $input->getOption('shopware-version');
        if (empty($version)) {
            $version = \Shopware::VERSION;
        }

        return $version;
    }

    /**
     * @param InputInterface  $input
     * @param OutputInterface $output
     *
     * @throws \Exception
     *
     * @return AccessTokenStruct
     */
    protected function setupAuth(InputInterface $input, OutputInterface $output)
    {
        $this->output = $output;
        $this->input = $input;

        $username = $input->getOption('username');
        $password = $input->getOption('password');

        if ($input->isInteractive()) {
            /** @var QuestionHelper $questionHelper */
            $questionHelper = $this->getHelper('question');

            if (empty($username)) {
                $username = $questionHelper->ask($input, $output, new Question('Please enter the username: '));
            }

            if (empty($password)) {
                $question = new Question('Please enter the password: ');
                $question->setHidden(true);
                $question->setHiddenFallback(false);

                $password = $questionHelper->ask($input, $output, $question);
            }
        }

        if (empty($username) || empty($password)) {
            throw new \Exception('Username and password are required');
        }

        $output->writeln(sprintf('Connect to Store with username: %s...', $username));

        return $this->container->get('shopware_plugininstaller.store_client')->getAccessToken(
            $username,
            $password
        );
    }

    /**
     * @param InputInterface  $input
     * @param OutputInterface $output
     *
     * @throws \Exception
     *
     * @return string
     */
    protected function setupDomain(InputInterface $input, OutputInterface $output)
    {
        $this->output = $output;
        $this->input = $input;

        $hostname = $input->getOption('hostname');
        if (empty($hostname)) {
            $hostname = $this->container->get('shopware_plugininstaller.account_manager_service')->getDomain();
        }

        if (empty($hostname)) {
            throw new \Exception('Hostname is required');
        }

        return $hostname;
    }
}
