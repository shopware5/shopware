<?php

declare(strict_types=1);
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

use Enlight_Template_Manager;
use RuntimeException;
use Shopware\Components\Random;
use Shopware_Components_Config;

abstract class AbstractCaptcha implements CaptchaInterface
{
    private const PATH_CAPTCHA_IMAGE = 'frontend/_public/src/img/bg--captcha.jpg';
    private const PATH_CAPTCHA_IMAGE_FALLBACK = 'frontend/_resources/images/captcha/background.jpg';

    private const PATH_CAPTCHA_FONT = 'frontend/_public/src/fonts/captcha.ttf';
    private const PATH_CAPTCHA_FONT_FALLBACK = 'frontend/_resources/images/captcha/font.ttf';

    /**
     * @var Shopware_Components_Config
     */
    protected $config;

    /**
     * @var Enlight_Template_Manager
     */
    private $templateManager;

    public function __construct(
        Shopware_Components_Config $config,
        Enlight_Template_Manager $templateManager
    ) {
        $this->config = $config;
        $this->templateManager = $templateManager;
    }

    /**
     * Generates the captcha challenge image from a given string
     *
     * @param string $string
     *
     * @return resource
     */
    protected function getImageResource($string)
    {
        $captcha = $this->getCaptchaFile(self::PATH_CAPTCHA_IMAGE);
        $font = $this->getCaptchaFile(self::PATH_CAPTCHA_FONT);

        if (empty($captcha)) {
            $captcha = $this->getCaptchaFile(self::PATH_CAPTCHA_IMAGE_FALLBACK);
        }

        if (empty($font)) {
            $font = $this->getCaptchaFile(self::PATH_CAPTCHA_FONT_FALLBACK);
        }

        if (!empty($captcha)) {
            $im = imagecreatefromjpeg($captcha);
        } else {
            $im = imagecreatetruecolor(162, 87);
        }

        if ($im === false) {
            throw new RuntimeException('Could not create captcha image');
        }

        if (!empty($this->config->get('CaptchaColor'))) {
            $colors = explode(',', $this->config->get('CaptchaColor'));
        } else {
            $colors = explode(',', '255,0,0');
        }

        $black = (int) imagecolorallocate($im, (int) $colors[0], (int) $colors[1], (int) $colors[2]);

        $string = implode(' ', str_split($string));

        if (!empty($font)) {
            for ($i = 0, $iMax = \strlen($string); $i <= $iMax; ++$i) {
                $rand1 = Random::getInteger(35, 40);
                $rand2 = Random::getInteger(15, 20);
                $rand3 = Random::getInteger(60, 70);
                imagettftext($im, $rand1, $rand2, ($i + 1) * 15, $rand3, $black, $font, $string[$i]);
                imagettftext($im, $rand1, $rand2, (($i + 1) * 15) + 2, $rand3 + 2, $black, $font, $string[$i]);
            }
            for ($i = 0; $i < 8; ++$i) {
                imageline($im, Random::getInteger(30, 70), Random::getInteger(0, 50), Random::getInteger(100, 150),
                    Random::getInteger(20, 100), $black);
                imageline($im, Random::getInteger(30, 70), Random::getInteger(0, 50), Random::getInteger(100, 150),
                    Random::getInteger(20, 100), $black);
            }
        } else {
            $white = (int) imagecolorallocate($im, 255, 255, 255);
            imagestring($im, 5, 40, 35, $string, $white);
            imagestring($im, 3, 40, 70, 'missing font', $white);
        }

        return $im;
    }

    /**
     * Helper function that checks if the file exists in any of the template directories
     * If the file exists, the full file path will be returned
     */
    protected function getCaptchaFile(string $fileName): ?string
    {
        $templateDirs = $this->templateManager->getTemplateDir();
        if (!\is_array($templateDirs)) {
            return null;
        }

        foreach ($templateDirs as $templateDir) {
            if (file_exists($templateDir . $fileName)) {
                return $templateDir . $fileName;
            }
        }

        return null;
    }
}
