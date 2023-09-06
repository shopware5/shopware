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

namespace Shopware\Components\Password;

use DomainException;
use Exception;
use Shopware\Components\Password\Encoder\PasswordEncoderInterface;
use Shopware_Components_Config;

/**
 * Password Manager
 */
class Manager
{
    private const DEFAULT_ENCODER = 'bcrypt';
    private const FALLBACK_ENCODER = 'sha256';
    private const AUTO_ENCODING = 'auto';

    /**
     * @var array<string, PasswordEncoderInterface>
     */
    protected $encoder = [];

    /**
     * @var Shopware_Components_Config
     */
    protected $config;

    public function __construct(Shopware_Components_Config $config)
    {
        $this->config = $config;
    }

    /**
     * @throws Exception
     *
     * @return void
     */
    public function addEncoder(PasswordEncoderInterface $encoder)
    {
        $name = strtolower(trim($encoder->getName()));

        if (isset($this->encoder[$name])) {
            throw new Exception(sprintf('Encoder by name %s already registered', $name));
        }

        $this->encoder[$name] = $encoder;
    }

    /**
     * @param string $name
     *
     * @throws Exception
     *
     * @return PasswordEncoderInterface
     */
    public function getEncoderByName($name)
    {
        $name = strtolower(trim($name));

        if (!isset($this->encoder[$name])) {
            throw new DomainException(sprintf('Encoder by name %s not found', $name));
        }

        $encoder = $this->encoder[$name];

        if (method_exists($encoder, 'isCompatible') && !$encoder->isCompatible()) {
            throw new Exception(sprintf('Encoder by name %s is not compatible with your system', $name));
        }

        return $encoder;
    }

    /**
     * @return array<PasswordEncoderInterface>
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

        if (empty($encoderName) || $encoderName === self::AUTO_ENCODING) {
            $bryptEncoder = $this->encoder[self::DEFAULT_ENCODER];
            if ($bryptEncoder->isCompatible()) {
                $encoderName = self::DEFAULT_ENCODER;
            } else {
                $encoderName = self::FALLBACK_ENCODER;
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
        if (!\is_string($password) || empty($password)) {
            return false;
        }

        if (!\is_string($hash) || empty($hash)) {
            return false;
        }

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
        if (!\is_string($password) || empty($password)) {
            throw new DomainException('This Password can not be encoded');
        }

        $encoder = $this->getEncoderByName($encoderName);

        $encodedPassword = $encoder->encodePassword($password);

        if (!\is_string($encodedPassword)) {
            throw new DomainException(sprintf('The password could not be encoded by %s.', $encoderName));
        }

        return $encodedPassword;
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
        if (!\is_string($password) || empty($password)) {
            throw new DomainException('Password can not be reencoded');
        }

        if (!\is_string($hash) || empty($hash)) {
            throw new DomainException('The hash is not valid');
        }

        $encoder = $this->getEncoderByName($encoderName);

        $truncated = $password !== strip_tags($password);
        if (!$truncated && !$encoder->isReencodeNeeded($hash)) {
            return $hash;
        }

        $encodedPassword = $encoder->encodePassword($password);

        if (!\is_string($encodedPassword)) {
            throw new DomainException('The reencoding of the password was not successfull.');
        }

        return $encodedPassword;
    }
}
