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

use Shopware\Components\Password\Manager;

/*
 * @see rfc for argon2 in PHP -> https://wiki.php.net/rfc/argon2_password_hash
 */
if (!defined('PASSWORD_ARGON2_DEFAULT_MEMORY_COST')) {
    define('PASSWORD_ARGON2_DEFAULT_MEMORY_COST', 1024); // 1KiB
}

if (!defined('PASSWORD_ARGON2_DEFAULT_TIME_COST')) {
    define('PASSWORD_ARGON2_DEFAULT_TIME_COST', 2);
}

if (!defined('PASSWORD_ARGON2_DEFAULT_THREADS')) {
    define('PASSWORD_ARGON2_DEFAULT_THREADS', 2);
}

class Shopware_Plugins_Core_PasswordEncoder_Bootstrap extends Shopware_Components_Plugin_Bootstrap
{
    /**
     * @return string
     */
    public function getVersion()
    {
        return '1.0.0-dev';
    }

    /**
     * @return array
     */
    public function getCapabilities()
    {
        return [
            'install' => false,
            'enable' => false,
            'update' => true,
        ];
    }

    /**
     * Standard plugin install method to register all required components.
     *
     * @return bool success
     */
    public function install()
    {
        $this->subscribeEvents();
        $this->createForm();

        return true;
    }

    public function getArgon2Options(): array
    {
        $config = $this->Config();

        return [
            'memory_cost' => $config['argon2MemoryCost'],
            'time_cost' => $config['argon2TimeCost'],
            'threads' => $config['argon2Threads'],
        ];
    }

    public function getBcryptOptions(): array
    {
        $config = $this->Config();

        return [
            'cost' => $config['bcryptCost'],
        ];
    }

    public function getSha256Options(): array
    {
        $config = $this->Config();

        $options = [
            'iterations' => $config['sha256iterations'],
            'salt_len' => 22,
        ];

        return $options;
    }

    /**
     * @throws Enlight_Event_Exception
     */
    public function onInitResourcePasswordEncoder(Enlight_Event_EventArgs $args): Manager
    {
        // Get a list of all available hashes
        $availableHasher = Shopware()->Events()->filter(
            'Shopware_Components_Password_Manager_AddEncoder',
            [],
            ['subject' => $this]
        );

        $passwordManager = new Manager(
            $this->Application()->Config()
        );

        foreach ($availableHasher as $encoder) {
            $passwordManager->addEncoder($encoder);
        }

        return $passwordManager;
    }

    /**
     * This method registers shopware's default hash algorithm
     */
    public function onAddEncoder(Enlight_Event_EventArgs $args): array
    {
        $hashes = $args->getReturn();

        $hashes[] = new Shopware\Components\Password\Encoder\Argon2id($this->getArgon2Options());
        $hashes[] = new Shopware\Components\Password\Encoder\Argon2i($this->getArgon2Options());
        $hashes[] = new Shopware\Components\Password\Encoder\Bcrypt($this->getBcryptOptions());
        $hashes[] = new Shopware\Components\Password\Encoder\Sha256($this->getSha256Options());
        $hashes[] = new Shopware\Components\Password\Encoder\LegacyBackendMd5();
        $hashes[] = new Shopware\Components\Password\Encoder\Md5();
        $hashes[] = new Shopware\Components\Password\Encoder\PreHashed();

        return $hashes;
    }

    /**
     * Crate config form elements
     */
    protected function createForm()
    {
        $form = $this->Form();

        $form->setElement('combo', 'defaultPasswordEncoderName', [
            'label' => 'Passwörter verschlüsseln mit...',
            'editable' => false,
            'value' => 'Auto',
            'valueField' => 'id', 'displayField' => 'id',
            'triggerAction' => 'all',
            'store' => 'base.EncoderName',
        ]);

        $form->setElement('boolean', 'liveMigration', [
            'description' => 'Sollen vorhandene Benutzer-Passwörter mit anderen Passwort-Algorithmen beim nächsten Einloggen erneut gehasht werden? Das geschieht voll automatisch im Hintergrund, so dass die Passwörter sukzessiv auf einen neuen Algorithmus umgestellt werden können.',
            'label' => 'Live Migration',
            'value' => true,
        ]);

        $form->setElement('number', 'bcryptCost', [
            'description' => 'Je höher der Rechenaufwand, desto aufwändiger ist es für einen möglichen Angreifer, ein Klartext-Passwort für das verschlüsselte Passwort zu berechnen.',
            'label' => 'Bcrypt-Rechenaufwand',
            'minValue' => 4,
            'maxValue' => 31,
            'required' => true,
            'value' => 10,
        ]);

        $form->setElement('number', 'sha256iterations', [
            'description' => 'Je höher der Rechenaufwand, desto aufwändiger ist es für einen möglichen Angreifer, ein Klartext-Passwort für das verschlüsselte Passwort zu berechnen.',
            'label' => 'Sha256 Iterationen',
            'minValue' => 1,
            'maxValue' => 1000000,
            'required' => true,
            'value' => 100000,
        ]);

        $form->setElement('number', 'argon2MemoryCost', [
            'description' => 'Ein höherer Speicherverbrauch macht es einem möglichen Angreifer schwerer, ein passendes Klartext-Passwort zu erzeugen.',
            'label' => 'Argon2-Speicher',
            'minValue' => 1 << 20,
            'maxValue' => 1 << 62,
            'required' => true,
            'value' => PASSWORD_ARGON2_DEFAULT_MEMORY_COST,
        ]);

        $form->setElement('number', 'argon2TimeCost', [
            'description' => 'Ein höherer Zeitaufwand macht es einem möglichen Angreifer schwerer, ein passendes Klartext-Passwort zu erzeugen.',
            'label' => 'Argon2-Dauer',
            'minValue' => 1,
            'maxValue' => 30,
            'required' => true,
            'value' => PASSWORD_ARGON2_DEFAULT_TIME_COST,
        ]);

        $form->setElement('number', 'argon2Threads', [
            'description' => 'Anzahl paralleler Threads zur Erzeugung nutzen.',
            'label' => 'Argon2-Threads',
            'minValue' => 1,
            'maxValue' => 32,
            'required' => true,
            'value' => PASSWORD_ARGON2_DEFAULT_THREADS,
        ]);

        $form->setLabel('Passwörter');
        $form->setParent(
            $this->Forms()->findOneBy(['name' => 'Core'])
        );
    }

    /**
     * Registers all necessary events and hooks.
     */
    private function subscribeEvents(): void
    {
        $this->subscribeEvent(
            'Enlight_Bootstrap_InitResource_PasswordEncoder',
            'onInitResourcePasswordEncoder'
        );

        $this->subscribeEvent(
            'Shopware_Components_Password_Manager_AddEncoder',
            'onAddEncoder'
        );
    }
}
