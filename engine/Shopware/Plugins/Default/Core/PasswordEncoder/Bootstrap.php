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

/**
 * @category  Shopware
 * @package   Shopware\Plugins\CorePasswordManager
 * @copyright Copyright (c) shopware AG (http://www.shopware.de)
 */
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
        return array(
            'install' => false,
            'enable'  => false,
            'update'  => true
        );
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

    /**
     * Registers all necessary events and hooks.
     */
     private function subscribeEvents()
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

    /**
     * Crate config form elements
     */
    protected function createForm()
    {
        $form = $this->Form();

        $form->setElement('combo', 'defaultPasswordEncoderName', array(
            'label' => 'Passwörter verschlüsseln mit...',
            'editable' => false,
            'value' => 'Auto',
            'valueField' => 'id', 'displayField'=>'id',
            'triggerAction' => 'all',
            'store' => 'base.EncoderName'
        ));

        $form->setElement('boolean', 'liveMigration', array(
            'description' => 'Sollen vorhandene Benutzer-Passwörter mit anderen Passwort-Algorithmen beim nächsten Einloggen erneut gehasht werden? Das geschieht voll automatisch im Hintergrund, so dass die Passwörter sukzessiv auf einen neuen Algorithmus umgestellt werden können.',
            'label' => 'Live Migration',
            'value' => true,
        ));

        $form->setElement('number', 'Bcrypt-Rechenaufwand', array(
            'description' => 'Je höher der Rechenaufwand, desto aufwändiger ist es für einen möglichen Angreifer, ein Klartext-Passwort für das verschlüsselte Passwort zu berechnen.',
            'label'    => 'bcrypt Cost',
            'minValue' => 4,
            'maxValue' => 31,
            'required' => true,
            'value'    => 10
        ));

        $form->setElement('number', 'sha256iterations', array(
            'description' => 'Je höher der Rechenaufwand, desto aufwändiger ist es für einen möglichen Angreifer, ein Klartext-Passwort für das verschlüsselte Passwort zu berechnen.',
            'label'    => 'Sha256 Iterationen',
            'minValue' => 1,
            'maxValue' => 1000000,
            'required' => true,
            'value'    => 100000
        ));

        $form->setLabel('Passwörter');
        $form->setParent(
            $this->Forms()->findOneBy(array('name' => 'Core'))
        );
    }

    /**
     * @return array
     */
    public function getBcryptOptions()
    {
        $config = $this->Config();

        $options = array(
            'cost' => $config['bcryptCost']
        );

        return $options;
    }

    /**
     * @return array
     */
    public function getSha256Options()
    {
        $config = $this->Config();

        $options = array(
            'iterations' => $config['sha256iterations'],
            'salt_len' => 22
        );

        return $options;
    }

    /**
     * @param Enlight_Event_EventArgs $args
     * @return \Shopware\Components\Password\Manager
     */
    public function onInitResourcePasswordEncoder(Enlight_Event_EventArgs $args)
    {
        // Get a list of all available hashes
        $availableHasher = Enlight()->Events()->filter(
            'Shopware_Components_Password_Manager_AddEncoder',
            array(),
            array('subject' => $this)
        );

        $passwordManager = new \Shopware\Components\Password\Manager(
            $this->Application()->Config()
        );

        foreach ($availableHasher as $encoder) {
            $passwordManager->addEncoder($encoder);
        }

        return $passwordManager;
    }

    /**
     * This method registers shopware's default hash algorithm
     * @param Enlight_Event_EventArgs $args
     * @return array
     */
    public function onAddEncoder(\Enlight_Event_EventArgs $args)
    {
        $hashes = $args->getReturn();

        $hashes[] = new Shopware\Components\Password\Encoder\Bcrypt($this->getBcryptOptions());
        $hashes[] = new Shopware\Components\Password\Encoder\Sha256($this->getSha256Options());
        $hashes[] = new Shopware\Components\Password\Encoder\LegacyBackendMd5();
        $hashes[] = new Shopware\Components\Password\Encoder\Md5();
        $hashes[] = new Shopware\Components\Password\Encoder\PreHashed();

        return $hashes;
    }
}
