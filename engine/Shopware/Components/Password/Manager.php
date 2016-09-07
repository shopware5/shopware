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

namespace Shopware\Components\Password;

/**
 * Password Manager
 *
 * @category  Shopware
 * @package   Shopware\Components\Password
 * @copyright Copyright (c) shopware AG (http://www.shopware.de)
 */
class Manager
{
    /**
     * @var array
     */
    protected $encoder = array();

    /**
     * @var \Shopware_Components_Config
     */
    protected $config;

    /**
     * @var \Enlight_Event_EventManager $eventManager
     */
    protected $eventManager;

    /**
     * @param \Shopware_Components_Config $config
     * @param \Enlight_Event_EventManager $eventManager
     */
    public function __construct(
        \Shopware_Components_Config $config,
        \Enlight_Event_EventManager $eventManager
    )
    {
        $this->config = $config;
        $this->eventManager = $eventManager;
    }

    /**
     * @param Encoder\PasswordEncoderInterface $encoder
     * @throws \Exception
     */
    public function addEncoder(Encoder\PasswordEncoderInterface $encoder)
    {
        $name = trim(strtolower($encoder->getName()));

        if (isset($this->encoder[$name])) {
            throw new \Exception("Encoder by name {$name} already registered");
        }

        $this->encoder[$name] = $encoder;
    }

    /**
     * @param  string  $name
     * @throws \Exception
     * @return Encoder\PasswordEncoderInterface
     */
    public function getEncoderByName($name)
    {
        $name = trim(strtolower($name));

        if (!isset($this->encoder[$name])) {
            throw new \Exception("Encoder by name {$name} not found");
        }

        $encoder = $this->encoder[$name];

        if (method_exists($encoder, 'isCompatible') && !$encoder->isCompatible()) {
            throw new \Exception("Encoder by name {$name} is not compatible with your system");
        }

        return $encoder;
    }

    /**
     * @return array
     */
    public function getCompatibleEncoders()
    {
        return array_filter($this->encoder, function ($encoder) {
            return !method_exists($encoder, 'isCompatible') || $encoder->isCompatible();
        });
    }

    /**
     * Convenience method which returns the name of the formatted default password encoder
     *
     * @return string
     */
    public function getDefaultPasswordEncoderName()
    {
        $encoderName = strtolower($this->config->defaultPasswordEncoder);

        if (empty($encoderName) || $encoderName == 'auto') {
            $bryptEncoder = $this->encoder['bcrypt'];
            if ($bryptEncoder->isCompatible()) {
                $encoderName = 'bcrypt';
            } else {
                $encoderName = 'sha256';
            }
        }

        $encoderName = $this->eventManager->filter(
            'Shopware_Components_Password_Manager_getDefaultPasswordEncoderName',
            $encoderName,
            array(
                'subject'=> $this
            )
        );

        return $encoderName;
    }

    /**
     * @param  string $password
     * @param  string $hash
     * @param  string $encoderName
     * @return bool
     */
    public function isPasswordValid($password, $hash, $encoderName)
    {
        $encoder = $this->getEncoderByName($encoderName);

        return $encoder->isPasswordValid($password, $hash);
    }

    /**
     * @param  string $password
     * @param  string $encoderName
     * @return string
     */
    public function encodePassword($password, $encoderName)
    {
        $encoder = $this->getEncoderByName($encoderName);

        return $encoder->encodePassword($password);
    }

    /**
     * @param  string $password
     * @param  string $hash
     * @param  string $encoderName
     * @return string
     */
    public function reencodePassword($password, $hash, $encoderName)
    {
        $encoder = $this->getEncoderByName($encoderName);

        if (!$encoder->isReencodeNeeded($hash)) {
            return $hash;
        }

        return $encoder->encodePassword($password);
    }
}
