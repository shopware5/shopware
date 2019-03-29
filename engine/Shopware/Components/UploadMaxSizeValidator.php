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

namespace Shopware\Components;

use Enlight\Event\SubscriberInterface;
use Enlight_Controller_EventArgs;
use Enlight_Controller_Request_Request;
use Enlight_Controller_Response_Response;

class UploadMaxSizeValidator implements SubscriberInterface
{
    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            'Enlight_Controller_Front_PreDispatch' => 'validateContentLength',
        ];
    }

    /**
     * @throws UploadMaxSizeException
     */
    public function validateContentLength(Enlight_Controller_EventArgs $args)
    {
        $checkRequest = $args->getRequest()->isPost() || $args->getRequest()->isPut();
        $exceptionAlreadyThrown = $this->hasUploadMaxSizeExceptions($args->getResponse());

        if (!$checkRequest || $exceptionAlreadyThrown) {
            return;
        }

        if (!$this->hasPostMaxSizeBeenExceeded($args->getRequest())) {
            return;
        }

        throw new UploadMaxSizeException();
    }

    /**
     * Returns true if the POST max size has been exceeded in the request.
     *
     * @return bool
     */
    public function hasPostMaxSizeBeenExceeded(Enlight_Controller_Request_Request $request)
    {
        $contentLength = $request->getServer('CONTENT_LENGTH');
        $maxContentLength = $this->getPostMaxSize();

        return $maxContentLength && $contentLength > $maxContentLength;
    }

    /**
     * Returns maximum post size in bytes.
     *
     * @return int|null The maximum post size in bytes
     */
    public function getPostMaxSize()
    {
        $iniMax = strtolower($this->getNormalizedIniPostMaxSize());

        if ($iniMax === '') {
            return null;
        }

        $max = ltrim($iniMax, '+');
        if (strpos($max, '0x') === 0) {
            $max = intval($max, 16);
        } elseif (strpos($max, '0') === 0) {
            $max = intval($max, 8);
        } else {
            $max = (int) $max;
        }

        switch (substr($iniMax, -1)) {
            case 't': $max *= 1024;
            // no break
            case 'g': $max *= 1024;
            // no break
            case 'm': $max *= 1024;
            // no break
            case 'k': $max *= 1024;
        }

        return $max;
    }

    /**
     * Returns the normalized "post_max_size" ini setting.
     *
     * @return string
     */
    public function getNormalizedIniPostMaxSize()
    {
        return strtoupper(trim(ini_get('post_max_size')));
    }

    /**
     * @return bool
     */
    private function hasUploadMaxSizeExceptions(Enlight_Controller_Response_Response $response)
    {
        foreach ($response->getException() as $exception) {
            if ($exception instanceof UploadMaxSizeException) {
                return true;
            }
        }

        return false;
    }
}
