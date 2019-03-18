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
 */
class Manager
{
    /**
     * @var array
     */
    protected $encoder = [];

    /**
     * @var \Shopware_Components_Config
     */
    protected $config;

    public function __construct(\Shopware_Components_Config $config)
    {
        $this->config = $config;
    }

    /**
     * @throws \Exception
     */
    public function addEncoder(Encoder\PasswordEncoderInterface $encoder)
    {
        $name = strtolower(trim($encoder->getName()));

        if (isset($this->encoder[$name])) {
            throw new \Exception(sprintf('Encoder by name %s already registered', $name));
        }

        $this->encoder[$name] = $encoder;
    }

    /**
     * @param string $name
     *
     * @throws \Exception
     *
     * @return Encoder\PasswordEncoderInterface
     */
    public function getEncoderByName($name)
    {
        $name = strtolower(trim($name));

        if (!isset($this->encoder[$name])) {
            throw new \Exception(sprintf('Encoder by name %s not found', $name));
        }

        $encoder = $this->encoder[$name];

        if (method_exists($encoder, 'isCompatible') && !$encoder->isCompatible()) {
            throw new \Exception(sprintf('Encoder by name %s is not compatible with your system', $name));
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

        if (empty($encoderName) || $encoderName === 'auto') {
            $bryptEncoder = $this->encoder['bcrypt'];
            if ($bryptEncoder->isCompatible()) {
                $encoderName = 'bcrypt';
            } else {
                $encoderName = 'sha256';
            }
        }

        return $encoderName;
    }

    /**
     * @param string $password
     * @param string $hash
     * @param string $encoderName
     *
     * @return bool
     */
    public function isPasswordValid($password, $hash, $encoderName)
    {
        $encoder = $this->getEncoderByName($encoderName);

        if ($encoder->isPasswordValid($password, $hash)) {
            return true;
        }

        return $encoder->isPasswordValid(strip_tags($password), $hash);
    }

    /**
     * @param string $password
     * @param string $encoderName
     *
     * @return string
     */
    public function encodePassword($password, $encoderName)
    {
        $encoder = $this->getEncoderByName($encoderName);

        return $encoder->encodePassword($password);
    }

    /**
     * @param string $password
     * @param string $hash
     * @param string $encoderName
     *
     * @return string
     */
    public function reencodePassword($password, $hash, $encoderName)
    {
        $encoder = $this->getEncoderByName($encoderName);

        $truncated = $password !== strip_tags($password);
        if (!$truncated && !$encoder->isReencodeNeeded($hash)) {
            return $hash;
        }

        return $encoder->encodePassword($password);
    }
}
