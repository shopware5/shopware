<?php
/**
 * Shopware 5
 * Copyright (c) shopware AG
 *
 * According to our licensing model, this program can be used
 * under the terms of the GNU Affero General Public License, version 3.
 *
 * The texts of the GNU Affero General Public License with an additional
 * permission can be found at and in the LICENSE file you have received
 * along with this program.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 * See the GNU Affero General Public License for more details.
 *
 * "Shopware" is a registered trademark of shopware AG.
 * The licensing of the program under the AGPLv3 does not imply a
 * trademark license. Therefore, any rights, title and interest in
 * our trademarks remain entirely with the shopware AG.
 */

namespace Shopware\Commands;

use DateTime;
use Exception;
use InvalidArgumentException;
use RuntimeException;
use Shopware\Components\Model\Exception\ModelNotFoundException;
use Shopware\Components\Model\ModelManager;
use Shopware\Models\User\Role;
use Shopware\Models\User\User;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class AdminCreateCommand extends ShopwareCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('sw:admin:create')
            ->setDescription('Create a new administrator user')
            ->setHelp('The <info>sw:admin:create</info> command create a new administration user.')
            ->addOption(
                'email',
                null,
                InputOption::VALUE_REQUIRED,
                'Admin email'
            )
            ->addOption(
                'username',
                null,
                InputOption::VALUE_REQUIRED,
                'Admin login name'
            )
            ->addOption(
                'name',
                null,
                InputOption::VALUE_REQUIRED,
                'User full name'
            )

            ->addOption(
                'locale',
                null,
                InputOption::VALUE_REQUIRED,
                'Locale (default: en_GB)',
                'en_GB'
            )
            ->addOption(
                'password',
                null,
                InputOption::VALUE_REQUIRED,
                'Password'
            )
        ;
    }

    /**
     * {@inheritdoc}
     */
    protected function interact(InputInterface $input, OutputInterface $output)
    {
        $this->fillOption('email', 'Email', $input, $output);
        $this->fillOption('username', 'Username', $input, $output);
        $this->fillOption('name', 'Fullname', $input, $output);
        $this->fillOption('locale', 'Locale', $input, $output);
        $this->fillOption('password', 'Password', $input, $output);
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->validateInput($input);

        $io = new SymfonyStyle($input, $output);

        $user = $this->createAdminUser();

        $user->setLocaleId($this->getLocaleIdFromLocale($input->getOption('locale')));
        $user->setEmail($input->getOption('email'));
        $user->setUsername($input->getOption('username'));
        $user->setName($input->getOption('name'));
        $user->setLockedUntil(new DateTime('2010-01-01 00:00:00'));
        $this->setPassword($user, $input->getOption('password'));

        $this->persistUser($user);

        $io->success(sprintf('Adminuser "%s" was successfully created.', $user->getUsername()));

        return 0;
    }

    private function fillOption(string $field, string $label, InputInterface $input, OutputInterface $output): void
    {
        $io = new SymfonyStyle($input, $output);

        $input->setOption(
            $field,
            $io->ask($label, $input->getOption($field))
        );
    }

    private function getAdminRole(): Role
    {
        $role = $this->container->get(ModelManager::class)->getRepository(Role::class)->findOneBy(['name' => 'local_admins']);
        if (!$role instanceof Role) {
            throw new ModelNotFoundException(Role::class, 'local_admins', 'name');
        }

        return $role;
    }

    private function getLocaleIdFromLocale(string $locale): int
    {
        $locales = [
            'de_de' => 1,
            'en_gb' => 2,
        ];

        $locale = strtolower($locale);

        if (isset($locales[$locale])) {
            return $locales[$locale];
        }

        throw new RuntimeException(sprintf('Backend Locale "%s" not supported', $locale));
    }

    private function setPassword(User $user, string $plainPassword): void
    {
        $passwordEncoderRegistry = $this->getContainer()->get('passwordencoder');
        $defaultEncoderName = $passwordEncoderRegistry->getDefaultPasswordEncoderName();
        $encoder = $passwordEncoderRegistry->getEncoderByName($defaultEncoderName);

        $user->setPassword($encoder->encodePassword($plainPassword));
        $user->setEncoder($encoder->getName());
    }

    /**
     * @throws Exception
     */
    private function persistUser(User $user): void
    {
        $em = $this->container->get(ModelManager::class);
        $em->persist($user);
        $em->flush($user);
    }

    private function createAdminUser(): User
    {
        $adminRole = $this->getAdminRole();

        $user = new User();
        $user->setRoleId($adminRole->getId());
        $user->setRole($adminRole);

        return $user;
    }

    private function validateInput(InputInterface $input): void
    {
        $option = $input->getOption('email');
        if (empty($option)) {
            throw new InvalidArgumentException('Email is required');
        }

        $option = $input->getOption('name');
        if (empty($option)) {
            throw new InvalidArgumentException('Name is required');
        }

        $option = $input->getOption('username');
        if (empty($option)) {
            throw new InvalidArgumentException('Username is required');
        }

        $option = $input->getOption('locale');
        if (empty($option)) {
            throw new InvalidArgumentException('Locale is required');
        }

        $option = $input->getOption('password');
        if (empty($option)) {
            throw new InvalidArgumentException('Password is required');
        }
    }
}
