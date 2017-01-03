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

    /**
     * @param \Shopware_Components_Config $config
     * @param \Enlight_Template_Manager $templateManager
     */
    public function __construct(
        \Shopware_Components_Config $config,
        \Enlight_Template_Manager $templateManager
    ) {
        $this->config = $config;
        $this->templateManager = $templateManager;
    }

    /**
     * {@inheritDoc}
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
     * {@inheritDoc}
     */
    public function getTemplateData()
    {
        $string = $this->createCaptchaString();
        $imgResource = $this->getImageResource($string);

        ob_start();
        imagepng($imgResource, null, 9);
        $img = ob_get_clean();
        imagedestroy($imgResource);
        $img = base64_encode($img);

        return [
            'img' => $img,
            'sRand' => $string,
        ];
    }

    /**
     * Generates the captcha challenge image from a given string
     *
     * @param string $string
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

        $black = imagecolorallocate($im, $colors[0], $colors[1], $colors[2]);

        $string = implode(' ', str_split($string));

        if (!empty($font)) {
            for ($i = 0; $i <= strlen($string); $i++) {
                $rand1 = rand(35, 40);
                $rand2 = rand(15, 20);
                $rand3 = rand(60, 70);
                imagettftext($im, $rand1, $rand2, ($i + 1) * 15, $rand3, $black, $font, substr($string, $i, 1));
                imagettftext($im, $rand1, $rand2, (($i + 1) * 15) + 2, $rand3 + 2, $black, $font, substr($string, $i, 1));
            }
            for ($i = 0; $i < 8; $i++) {
                imageline($im, mt_rand(30, 70), mt_rand(0, 50), mt_rand(100, 150), mt_rand(20, 100), $black);
                imageline($im, mt_rand(30, 70), mt_rand(0, 50), mt_rand(100, 150), mt_rand(20, 100), $black);
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
     * @return null|string
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

    /**
     * @return string
     */
    private function createCaptchaString()
    {
        $alphabetRangeLow = range('a', 'z');
        $alphabetRangeUpp = range('A', 'Z');

        $exclude = ['C', 'c', 'I', 'l', 'O', 'o', 's', 'S', 'U', 'u', 'v', 'V', 'W', 'w', 'X', 'x', 'Z', 'z',];

        $alphabet = array_merge($alphabetRangeLow, $alphabetRangeUpp);
        $alphabet = array_diff($alphabet, $exclude);

        $numericRange = range(1, 9);

        $charlist = implode($alphabet) . implode($numericRange);

        return Random::getString(5, $charlist);
    }

    /**
     * {@inheritDoc}
     */
    public function getName()
    {
        return self::CAPTCHA_METHOD;
    }
}
