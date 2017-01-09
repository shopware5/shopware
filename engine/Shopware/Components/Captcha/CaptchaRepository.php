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

    /**
     * @param array $captchas
     * @param \Shopware_Components_Config $config
     * @param ContainerInterface $container
     */
    public function __construct(
        array $captchas,
        \Shopware_Components_Config $config,
        ContainerInterface $container
    ) {
        $this->captchas = $captchas;
        $this->config = $config;
        $this->container = $container;
    }

    /**
     * Returns an object of the currently configured Captcha
     *
     * @return CaptchaInterface
     * @throws \Exception
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

        return $this->getCaptcha($captchaMethod);
    }

    /**
     * @return CaptchaInterface[]
     */
    public function getList()
    {
        return $this->captchas;
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

    /**
     * {@inheritDoc}
     */
    private function getCaptcha($captchaMethod)
    {
        foreach ($this->captchas as $captcha) {
            if ($captcha->getName() == $captchaMethod) {
                return $captcha;
            }
        }

        throw new CaptchaNotFoundException(sprintf("The captcha with id '%s' is configured, but could not be found", $captchaMethod));
    }
}
