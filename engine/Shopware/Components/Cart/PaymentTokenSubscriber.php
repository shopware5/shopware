<?php
/**
 * Shopware 5
 * Copyright (c) shopware AG
 *
 * According to our licensing model, this program can be used
 * under the terms of the GNU Affero General Public License, version 3.
 *
 * The texts of the GNU Affero General Public License with an additional
 * permission can be found at and in the LICENSE file you have received
 * along with this program.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 * See the GNU Affero General Public License for more details.
 *
 * "Shopware" is a registered trademark of shopware AG.
 * The licensing of the program under the AGPLv3 does not imply a
 * trademark license. Therefore, any rights, title and interest in
 * our trademarks remain entirely with the shopware AG.
 */

namespace Shopware\Components\Cart;

use Enlight\Event\SubscriberInterface;
use Enlight_Components_Session_Namespace as Session;
use Enlight_Controller_Action;
use Enlight_Event_EventArgs;
use Symfony\Component\HttpFoundation\Cookie;

class PaymentTokenSubscriber implements SubscriberInterface
{
    /**
     * @var PaymentTokenService
     */
    private $paymentTokenService;

    /**
     * @var Session
     */
    private $session;

    public function __construct(PaymentTokenService $paymentTokenService, Session $session)
    {
        $this->paymentTokenService = $paymentTokenService;
        $this->session = $session;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents(): array
    {
        return [
            'Enlight_Controller_Action_PreDispatch_Frontend' => 'onPreDispatchFrontend',
        ];
    }

    public function onPreDispatchFrontend(Enlight_Event_EventArgs $args): void
    {
        /** @var Enlight_Controller_Action $controller */
        $controller = $args->get('subject');
        $request = $controller->Request();

        $token = $request->getParam(PaymentTokenService::TYPE_PAYMENT_TOKEN);

        if (!$token) {
            return;
        }

        $restoreData = $this->paymentTokenService->restore($token);

        // Don't restore, we have already a session
        if ($this->session->get('sUserId')) {
            return;
        }

        if ($restoreData) {
            $controller->Response()->headers->setCookie(
                new Cookie(
                    $restoreData->getSessionName(),
                    $restoreData->getValue(),
                    0,
                    \ini_get('session.cookie_path'),
                    null,
                    $request->isSecure()
                )
            );
        }

        $params = $request->getParams();
        unset($params[PaymentTokenService::TYPE_PAYMENT_TOKEN]);

        $controller->redirect($params);
    }
}
