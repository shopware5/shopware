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
use Enlight_Components_Session_Namespace as Session;
use Enlight_Controller_Request_Request as Request;
use Enlight_Controller_ActionEventArgs as ActionEventArgs;
use Shopware\Components\DependencyInjection\Container;

/**
 * Class CSRFTokenValidator
 * @package Shopware\Components
 */
class CSRFTokenValidator implements SubscriberInterface
{
    /**
     * @var Container
     */
    private $container;

    /**
     * @var string
     */
    private $tokenName = 'X-CSRF-Token';

    /**
     * CSRFTokenValidator constructor.
     * @param Container $container
     */
    public function __construct(Container $container)
    {
        $this->container = $container;
    }

    /**
     * @inheritdoc
     */
    public static function getSubscribedEvents()
    {
        return [
            'Enlight_Controller_Action_PreDispatch_Backend' => 'checkBackendTokenValidation'
        ];
    }

    /**
     * @param ActionEventArgs $args
     */
    public function checkBackendTokenValidation(ActionEventArgs $args)
    {
        $controller = $args->getSubject();

        if ($controller instanceof CSRFWhitelistAware) {
            $calledAction = strtolower($args->getRequest()->getActionName());
            $whitelistedActions = $controller->getWhitelistedCSRFActions();
            $whitelistedActions = array_map('strtolower', $whitelistedActions);

            if (in_array($calledAction, $whitelistedActions)) {
                return;
            }
        }

        $this->validateToken(
            $args->getRequest(),
            $this->container->get('BackendSession')
        );
    }

    /**
     * @param Request $request
     * @param Session $session
     * @throws CSRFTokenValidationException
     */
    private function validateToken(Request $request, Session $session)
    {
        $expected = $session->offsetGet($this->tokenName);
        $token = $request->getHeader($this->tokenName);

        if (!hash_equals($expected, $token)) {
            throw new CSRFTokenValidationException("The provided X-CSRF-Token header is invalid. If you're sure that the request should be valid, the called controller action needs to be whitelisted using the CSRFWhitelistAware interface.");
        }
    }
}
