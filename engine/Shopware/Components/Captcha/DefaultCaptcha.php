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

use Enlight_Controller_Request_Request;
use Enlight_Template_Manager;
use Shopware\Components\Random;
use Shopware_Components_Config;
use Symfony\Component\DependencyInjection\ContainerInterface;

class DefaultCaptcha extends AbstractCaptcha
{
    public const SESSION_KEY = __CLASS__ . '_sRandom';
    public const CAPTCHA_METHOD = 'default';

    /**
     * @var ContainerInterface
     */
    private $container;

    public function __construct(
        ContainerInterface $container,
        Shopware_Components_Config $config,
        Enlight_Template_Manager $templateManager
    ) {
        $this->container = $container;
        parent::__construct($config, $templateManager);
    }

    /**
     * {@inheritdoc}
     */
    public function validate(Enlight_Controller_Request_Request $request)
    {
        $captchaArray = $this->container->get('session')->get(self::SESSION_KEY, []);

        if (\count($captchaArray) === 0) {
            return false;
        }

        if (!\array_key_exists($request->get('sCaptcha'), $captchaArray)) {
            return false;
        }

        unset($captchaArray[$request->get('sCaptcha')]);

        $this->container->get('session')->offsetSet(self::SESSION_KEY, $captchaArray);

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function getTemplateData()
    {
        $string = $this->createCaptchaString();

        $imgResource = $this->getImageResource($string);

        ob_start();
        imagepng($imgResource, null, 9);
        $img = (string) ob_get_clean();
        imagedestroy($imgResource);
        $img = base64_encode($img);

        /** @var string[] $sRandArray */
        $sRandArray = $this->container->get('session')->get(self::SESSION_KEY, []);

        $threshold = 51;
        if (\count($sRandArray) > $threshold) {
            $sRandArray = \array_slice($sRandArray, -$threshold);
        }

        $sRandArray[$string] = true;

        $this->container->get('session')->offsetSet(self::SESSION_KEY, $sRandArray);

        return ['img' => $img];
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return self::CAPTCHA_METHOD;
    }

    private function createCaptchaString(): string
    {
        $alphabetRangeLow = range('a', 'z');
        $alphabetRangeUpp = range('A', 'Z');

        $exclude = ['C', 'c', 'I', 'l', 'O', 'o', 's', 'S', 'U', 'u', 'v', 'V', 'W', 'w', 'X', 'x', 'Z', 'z'];

        $alphabet = array_merge($alphabetRangeLow, $alphabetRangeUpp);
        $alphabet = array_diff($alphabet, $exclude);

        $numericRange = range(1, 9);

        $charlist = implode('', $alphabet) . implode('', $numericRange);

        return Random::getString(5, $charlist);
    }
}
