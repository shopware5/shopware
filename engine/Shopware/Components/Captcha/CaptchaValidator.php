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

class CaptchaValidator
{
    /**
     * @var CaptchaRepository
     */
    private $repository;

    public function __construct(CaptchaRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Validates a Request using the currently configured captcha
     *
     * @return bool
     */
    public function validate(Enlight_Controller_Request_Request $request)
    {
        return $this->validateByName(
            $this->repository->getConfiguredCaptcha()->getName(),
            $request
        );
    }

    /**
     * Validates a custom captcha by the template name which has passed in the request
     *
     * @param string $name
     *
     * @return bool
     */
    public function validateByName($name, Enlight_Controller_Request_Request $request)
    {
        $captcha = $this->repository->getCaptchaByName($name);

        return $captcha->validate($request);
    }
}
