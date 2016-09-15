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
use Symfony\Component\DependencyInjection\ContainerInterface;

class DefaultCaptcha implements CaptchaInterface
{

    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * @var \Shopware_Components_Config
     */
    private $config;

    /**
     * @var \Enlight_Template_Manager
     */
    private $templateManager;

    /**
     * DefaultCaptcha constructor.
     * @param ContainerInterface $container
     * @param \Shopware_Components_Config $config
     * @param \Enlight_Template_Manager $templateManager
     */
    public function __construct(
        ContainerInterface $container,
        \Shopware_Components_Config $config,
        \Enlight_Template_Manager $templateManager
    ) {
        $this->container = $container;
        $this->config = $config;
        $this->templateManager = $templateManager;
    }

    /**
     * {@inheritDoc}
     */
    public function validate(Enlight_Controller_Request_Request $request)
    {
        $captchaArray = $this->container->get('session')->get('sRand');

        if (!is_array($captchaArray)) {
            $this->container->get('session')->offsetSet('sRand', []);
            return false;
        }

        $keys = array_keys($captchaArray, $request->get('sCaptcha'), true);

        if (count($keys) === 0) {
            return false;
        }

        unset($captchaArray[$keys[0]]);
        $this->container->get('session')->offsetSet('sRand', $captchaArray);

        return true;
    }

    /**
     * {@inheritDoc}
     */
    public function getTemplateData()
    {
        $alphabetRangeLow = range('a', 'z');
        $alphabetRangeUpp = range('A', 'Z');

        $exclude = ['C', 'c', 'I', 'l', 'O', 'o', 's', 'S', 'U', 'u', 'v', 'V', 'W', 'w', 'X', 'x', 'Z', 'z',];

        $alphabet = array_merge($alphabetRangeLow, $alphabetRangeUpp);
        $alphabet = array_diff($alphabet, $exclude);

        $numericRange = range(1, 9);

        $charlist = implode($alphabet) . implode($numericRange);

        $string = Random::getString(5, $charlist);

        $imgResource = $this->getImageResource($string);

        ob_start();
        imagepng($imgResource, null, 9);
        $img = ob_get_clean();
        imagedestroy($imgResource);
        $img = base64_encode($img);

        $sRandArray = $this->container->get('session')->get('sRand');
        if (!is_array($sRandArray)) {
            $sRandArray = [];
        }

        // make array fixed size & rolling
        $threshold = 51;
        if (count($sRandArray) > $threshold) {
            $sRandArray = array_slice($sRandArray, -$threshold);
        }

        $sRandArray[] = $string;

        $this->container->get('session')->offsetSet('sRand', $sRandArray);

        return ["img" => $img];
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
                imagettftext($im, $rand1, $rand2, (($i + 1) * 15) + 2, $rand3 + 2, $black, $font,
                    substr($string, $i, 1));
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
     * Helper function that checks if a given file exists in any template directory.
     * If the file exists, the full file path will be returned, otherwise null.
     *
     * @param $fileName
     * @return null|string
     */
    private function getCaptchaFile($fileName)
    {
        $templateDirs = $this->templateManager->getTemplateDir();

        foreach ($templateDirs as $templateDir) {
            if (file_exists($templateDir . $fileName)) {
                return $templateDir . $fileName;
            }
        }

        return null;
    }

    /**
     * {@inheritDoc}
     */
    public function getName()
    {
        return 'default';
    }
}
