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

use Shopware\Components\Model\ModelManager;
use Shopware\Components\Password\Manager;
use Shopware\Components\Random;
use Shopware\Components\Routing\Context;
use Shopware\Components\Routing\RouterInterface;
use Shopware\Models\Shop\Shop;
use Shopware\Models\User\Role;
use Shopware\Models\User\User;
use Shopware_Components_Config;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Throwable;

class AdminLoginCommand extends ShopwareCommand
{
    /**
     * @var Manager
     */
    private $passwordRegistry;

    /**
     * @var RouterInterface
     */
    private $router;

    /**
     * @var ModelManager
     */
    private $modelManager;

    /**
     * @var Shopware_Components_Config
     */
    private $config;

    public function __construct(
        Manager $passwordRegistry,
        RouterInterface $router,
        ModelManager $modelManager,
        Shopware_Components_Config $config
    ) {
        parent::__construct();

        $this->passwordRegistry = $passwordRegistry;
        $this->router = $router;
        $this->modelManager = $modelManager;
        $this->config = $config;
    }

    protected function configure()
    {
        $this->addArgument('username', InputArgument::OPTIONAL);
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $criteria = [
            'active' => true,
        ];

        if ($input->getArgument('username')) {
            // find user by username from input
            $criteria['username'] = $input->getArgument('username');
        } else {
            // fallback: find user by role "local_admins"
            $role = $this->modelManager->getRepository(Role::class)->findOneBy([
                'name' => 'local_admins',
            ]);

            if (!$role instanceof Role) {
                $output->writeln('No role called "local_admins" could be found.');

                return 1;
            }

            $criteria['roleId'] = $role->getId();
        }

        $user = $this->modelManager->getRepository(User::class)->findOneBy($criteria);

        if (!$user instanceof User) {
            $output->writeln('No matching user could be found.');

            return 1;
        }

        // generate a cryptographically secure one-time-password
        $otp = Random::getString(32);

        try {
            $encoder = $this->passwordRegistry->getEncoderByName($user->getEncoder());
        } catch (Throwable $exception) {
            $output->writeln(sprintf('Password encoder %s could not be found.', $user->getEncoder()));

            return 1;
        }

        // prepare the user for a successful authentication
        $user->setLockedUntil(null)
            ->setFailedLogins(0)
            ->setOtp($encoder->encodePassword($otp))
            ->setOtpActivation(date_create());

        $this->modelManager->flush();

        // a request context is needed to generate the url for the correct host
        $shop = $this->modelManager->getRepository(Shop::class)->getActiveDefault();
        $this->router->setContext(Context::createFromShop($shop, $this->config));

        $url = $this->router->assemble([
            'module' => 'backend',
            'controller' => 'login',
            'action' => 'login',
            'username' => $user->getUsername(),
            'password' => $otp,
            'fullPath' => true,
        ]);

        $output->writeln($url);

        return 0;
    }
}
