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

namespace Shopware\Components\Captcha;

use IteratorAggregate;
use Shopware\Components\Captcha\Exception\CaptchaNotFoundException;
use Symfony\Component\DependencyInjection\ContainerInterface;

class CaptchaRepository
{
    /**
     * @var \Shopware_Components_Config
     */
    private $config;

    /**
     * @var CaptchaInterface[]
     */
    private $captchas;

    /**
     * @var ContainerInterface
     */
    private $container;

    public function __construct(
        IteratorAggregate $captchas,
        \Shopware_Components_Config $config,
        ContainerInterface $container
    ) {
        $this->captchas = iterator_to_array($captchas, false);
        $this->config = $config;
        $this->container = $container;
    }

    /**
     * Returns an object of the currently configured Captcha
     *
     * @throws \Exception
     *
     * @return CaptchaInterface
     */
    public function getConfiguredCaptcha()
    {
        $captchaMethod = strtolower($this->config->get('captchaMethod'));

        if ($this->isCaptchaDisabled()) {
            $captchaMethod = NoCaptcha::CAPTCHA_METHOD;
        }

        if (empty($captchaMethod)) {
            $captchaMethod = DefaultCaptcha::CAPTCHA_METHOD;
        }

        return $this->getCaptchaByName($captchaMethod);
    }

    /**
     * @return CaptchaInterface[]
     */
    public function getList()
    {
        return $this->captchas;
    }

    /**
     * Find and returns the captcha with the passed name
     *
     * @param string $captchaName
     *
     * @throws CaptchaNotFoundException
     *
     * @return CaptchaInterface
     */
    public function getCaptchaByName($captchaName)
    {
        foreach ($this->captchas as $captcha) {
            if ($captcha->getName() == $captchaName) {
                return $captcha;
            }
        }

        throw new CaptchaNotFoundException(
            sprintf("The captcha with id '%s' is configured, but could not be found", $captchaName)
        );
    }

    /**
     * @return bool
     */
    private function isCaptchaDisabled()
    {
        $userIsLoggedIn = !empty($this->container->get('session')->get('sUserId'));

        if ($this->config->get('noCaptchaAfterLogin') && $userIsLoggedIn) {
            return true;
        }

        // legacy way to disable the captcha
        $captchaColor = $this->config->get('CaptchaColor');
        if (empty($captchaColor)) {
            return true;
        }

        return false;
    }
}
