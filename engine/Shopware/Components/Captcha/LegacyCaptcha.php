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

class LegacyCaptcha extends AbstractCaptcha
{
    public const CAPTCHA_METHOD = 'legacy';

    /**
     * {@inheritdoc}
     */
    public function validate(Enlight_Controller_Request_Request $request)
    {
        if (!empty($this->config->get('CaptchaColor'))) {
            $captchaString = $request->get('sCaptcha');
            $captcha = str_replace(' ', '', strtolower($captchaString));
            $rand = $request->get('sRand');
            if (empty($rand) || !str_starts_with(md5($rand), $captcha)) {
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
        $img = (string) ob_get_clean();
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
}
