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
        $user->setLockedUntil(new \DateTime('2010-01-01 00:00:00'));
        $this->setPassword($user, $input->getOption('password'));

        $this->persistUser($user);

        $io->success(sprintf('Adminuser "%s" was successfully created.', $user->getUsername()));
    }

    /**
     * @param string $field
     * @param string $label
     */
    private function fillOption($field, $label, InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);

        $input->setOption(
            $field,
            $io->ask($label, $input->getOption($field))
        );
    }

    /**
     * @return Role
     */
    private function getAdminRole()
    {
        /** @var ModelManager $em */
        $em = $this->container->get('models');

        /** @var Role $return */
        $return = $em->getRepository(\Shopware\Models\User\Role::class)
            ->findOneBy(['name' => 'local_admins']);

        return $return;
    }

    /**
     * @param string $locale
     *
     * @return int
     */
    private function getLocaleIdFromLocale($locale)
    {
        $locales = [
            'de_de' => 1,
            'en_gb' => 2,
        ];

        $locale = strtolower($locale);

        if (isset($locales[$locale])) {
            return $locales[$locale];
        }

        throw new \RuntimeException(sprintf('Backend Locale "%s" not supported', $locale));
    }

    /**
     * @param string $plainPassword
     */
    private function setPassword(User $user, $plainPassword)
    {
        /** @var \Shopware\Components\Password\Manager $passworEncoderRegistry */
        $passworEncoderRegistry = $this->getContainer()->get('passwordencoder');
        $defaultEncoderName = $passworEncoderRegistry->getDefaultPasswordEncoderName();
        $encoder = $passworEncoderRegistry->getEncoderByName($defaultEncoderName);

        $user->setPassword($encoder->encodePassword($plainPassword));
        $user->setEncoder($encoder->getName());
    }

    /**
     * @throws \Exception
     */
    private function persistUser(User $user)
    {
        /** @var ModelManager $em */
        $em = $this->container->get('models');
        $em->persist($user);
        $em->flush($user);
    }

    /**
     * @return User
     */
    private function createAdminUser()
    {
        $adminRole = $this->getAdminRole();

        $user = new User();
        $user->setRoleId($adminRole->getId());
        $user->setRole($adminRole);

        return $user;
    }

    private function validateInput(InputInterface $input)
    {
        $option = $input->getOption('email');
        if (empty($option)) {
            throw new \InvalidArgumentException('Email is required');
        }

        $option = $input->getOption('name');
        if (empty($option)) {
            throw new \InvalidArgumentException('Name is required');
        }

        $option = $input->getOption('username');
        if (empty($option)) {
            throw new \InvalidArgumentException('Username is required');
        }

        $option = $input->getOption('locale');
        if (empty($option)) {
            throw new \InvalidArgumentException('Locale is required');
        }

        $option = $input->getOption('password');
        if (empty($option)) {
            throw new \InvalidArgumentException('Password is required');
        }
    }
}
