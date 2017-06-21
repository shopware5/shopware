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

/**
 * Shopware Captcha Controller
 */
class Shopware_Controllers_Widgets_Captcha extends Enlight_Controller_Action
{
    /**
     * Pre dispatch action method
     *
     * Sets no render on some actions
     */
    public function preDispatch()
    {
        $this->Front()->Plugins()->ViewRenderer()->setNoRender();
    }

    public function refreshCaptchaAction()
    {
        $rand = \Shopware\Components\Random::getAlphanumericString(32);

        $string = md5($rand);
        $string = substr($string, 0, 5);

        $imgResource = $this->getImageResource($string);

        ob_start();
        imagepng($imgResource, null, 9);
        $img = ob_get_clean();
        imagedestroy($imgResource);
        $img = base64_encode($img);

        $body = '<img src="data:image/png;base64,' . $img . '" alt="Captcha" />';
        $body .= '<input type="hidden" name="sRand" value="' . $rand . '" />';

        $this->Response()->setBody($body);
    }

    /**
     * Index action method
     *
     * Creates the captcha images and delivers it as a PNG
     * with the proper HTTP header.
     */
    public function indexAction()
    {
        $random = $this->Request()->rand;

        $random = md5($random);
        $string = substr($random, 0, 5);

        $im = $this->getImageResource($string);

        $this->Response()->setHeader('Content-Type', 'image/png', true);
        imagepng($im, null, 9);
        imagedestroy($im);
    }

    /**
     * Please note that the method loops through the template inheritances
     * to terminate the used font and background.
     *
     * @param string $string
     *
     * @return resource
     */
    public function getImageResource($string)
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

        if (!empty($this->get('config')->CaptchaColor)) {
            $colors = explode(',', $this->get('config')->CaptchaColor);
        } else {
            $colors = explode(',', '255,0,0');
        }

        $black = imagecolorallocate($im, $colors[0], $colors[1], $colors[2]);

        $string = implode(' ', str_split($string));

        if (!empty($font)) {
            for ($i = 0; $i <= strlen($string); ++$i) {
                $rand1 = rand(35, 40);
                $rand2 = rand(15, 20);
                $rand3 = rand(60, 70);
                imagettftext($im, $rand1, $rand2, ($i + 1) * 15, $rand3, $black, $font, substr($string, $i, 1));
                imagettftext($im, $rand1, $rand2, (($i + 1) * 15) + 2, $rand3 + 2, $black, $font, substr($string, $i, 1));
            }
            for ($i = 0; $i < 8; ++$i) {
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
     * Helper function that checks if the file exists in the all available template directories
     * If the file exists, the full file path will be returned, otherwise null
     *
     * @param $fileName
     *
     * @return null|string
     */
    private function getCaptchaFile($fileName)
    {
        $templateDirs = $this->get('template')->getTemplateDir();

        foreach ($templateDirs as $templateDir) {
            if (file_exists($templateDir . $fileName)) {
                return $templateDir . $fileName;
            }
        }

        return null;
    }
}
