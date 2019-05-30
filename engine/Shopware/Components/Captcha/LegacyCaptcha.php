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
use Shopware\Components\Random;

class LegacyCaptcha implements CaptchaInterface
{
    const CAPTCHA_METHOD = 'legacy';

    /**
     * @var \Shopware_Components_Config
     */
    private $config;

    /**
     * @var \Enlight_Template_Manager
     */
    private $templateManager;

    public function __construct(
        \Shopware_Components_Config $config,
        \Enlight_Template_Manager $templateManager
    ) {
        $this->config = $config;
        $this->templateManager = $templateManager;
    }

    /**
     * {@inheritdoc}
     */
    public function validate(Enlight_Controller_Request_Request $request)
    {
        if (!empty($this->config->get('CaptchaColor'))) {
            $captchaString = $request->get('sCaptcha');
            $captcha = str_replace(' ', '', strtolower($captchaString));
            $rand = $request->get('sRand');
            if (empty($rand) || $captcha != substr(md5($rand), 0, 5)) {
                return false;
            }
        }

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function getTemplateData()
    {
        $rand = Random::getAlphanumericString(32);

        $string = md5($rand);
        $string = substr($string, 0, 5);

        $imgResource = $this->getImageResource($string);

        ob_start();
        imagepng($imgResource, null, 9);
        $img = ob_get_clean();
        imagedestroy($imgResource);
        $img = base64_encode($img);

        return [
            'img' => $img,
            'sRand' => $rand,
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return self::CAPTCHA_METHOD;
    }

    /**
     * Generates the captcha challenge image from a given string
     *
     * @param string $string
     *
     * @return resource
     */
    private function getImageResource($string)
    {
        $captcha = $this->getCaptchaFile('frontend/_public/src/img/bg--captcha.jpg');
        $font = $this->getCaptchaFile('frontend/_public/src/fonts/captcha.ttf');

        if (empty($captcha)) {
            $captcha = $this->getCaptchaFile('frontend/_resources/images/captcha/background.jpg');
        }

        if (empty($font)) {
            $font = $this->getCaptchaFile('frontend/_resources/images/captcha/font.ttf');
        }

        if (!empty($captcha)) {
            $im = imagecreatefromjpeg($captcha);
        } else {
            $im = imagecreatetruecolor(162, 87);
        }

        if (!empty($this->config->get('CaptchaColor'))) {
            $colors = explode(',', $this->config->get('CaptchaColor'));
        } else {
            $colors = explode(',', '255,0,0');
        }

        $black = imagecolorallocate($im, (int) $colors[0], (int) $colors[1], (int) $colors[2]);

        $string = implode(' ', str_split($string));

        if (!empty($font)) {
            for ($i = 0; $i <= strlen($string); ++$i) {
                $rand1 = Random::getInteger(35, 40);
                $rand2 = Random::getInteger(15, 20);
                $rand3 = Random::getInteger(60, 70);
                imagettftext($im, $rand1, $rand2, ($i + 1) * 15, $rand3, $black, $font, substr($string, $i, 1));
                imagettftext($im, $rand1, $rand2, (($i + 1) * 15) + 2, $rand3 + 2, $black, $font, substr($string, $i, 1));
            }
            for ($i = 0; $i < 8; ++$i) {
                imageline($im, Random::getInteger(30, 70), Random::getInteger(0, 50), Random::getInteger(100, 150), Random::getInteger(20, 100), $black);
                imageline($im, Random::getInteger(30, 70), Random::getInteger(0, 50), Random::getInteger(100, 150), Random::getInteger(20, 100), $black);
            }
        } else {
            $white = imagecolorallocate($im, 255, 255, 255);
            imagestring($im, 5, 40, 35, $string, $white);
            imagestring($im, 3, 40, 70, 'missing font', $white);
        }

        return $im;
    }

    /**
     * Helper function that checks if the file exists in any of the template directories
     * If the file exists, the full file path will be returned
     *
     * @param string $fileName
     *
     * @return string|null
     */
    private function getCaptchaFile($fileName)
    {
        /** @var array $templateDirs */
        $templateDirs = $this->templateManager->getTemplateDir();

        foreach ($templateDirs as $templateDir) {
            if (file_exists($templateDir . $fileName)) {
                return $templateDir . $fileName;
            }
        }

        return null;
    }
}
