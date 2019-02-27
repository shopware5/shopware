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

namespace Shopware\Components\Cart;

use Enlight_Components_Session_Namespace as Session;
use Shopware\Components\Cart\Struct\PaymentTokenResult;
use Shopware\Components\OptinServiceInterface;

class PaymentTokenService
{
    public const TYPE_PAYMENT_TOKEN = 'swPaymentToken';

    /**
     * @var OptinServiceInterface
     */
    private $optinService;

    /**
     * @var Session
     */
    private $session;

    public function __construct(OptinServiceInterface $optinService, Session $session)
    {
        $this->optinService = $optinService;
        $this->session = $session;
    }

    public function generate(): string
    {
        $sessionId = $this->session->get('sessionId');

        return $this->optinService->add(self::TYPE_PAYMENT_TOKEN, (int) ini_get('session.gc_maxlifetime'), [
            'name' => session_name(),
            'value' => $sessionId,
        ]);
    }

    public function restore(string $hash): ?PaymentTokenResult
    {
        $value = $this->optinService->get(self::TYPE_PAYMENT_TOKEN, $hash);

        if (!$value) {
            return null;
        }

        $this->optinService->delete(self::TYPE_PAYMENT_TOKEN, $hash);

        return (new PaymentTokenResult())->setSessionName($value['name'])->setValue($value['value']);
    }
}
