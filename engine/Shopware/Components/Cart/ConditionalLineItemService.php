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

use Doctrine\DBAL\Connection;
use Enlight_Components_Session_Namespace as Session;
use Shopware\Components\Cart\Struct\DiscountContext;
use Shopware_Components_Config as Config;
use sSystem as System;

class ConditionalLineItemService implements ConditionalLineItemServiceInterface
{
    /**
     * @var System
     */
    private $system;

    /**
     * @var Session
     */
    private $session;

    /**
     * @var Config
     */
    private $config;

    /**
     * @var BasketHelperInterface
     */
    private $basketHelper;

    /**
     * @var Connection
     */
    private $connection;

    public function __construct(System $sSystem, Session $session, Config $config, BasketHelperInterface $basketHelper, Connection $connection)
    {
        $this->system = $sSystem;
        $this->session = $session;
        $this->config = $config;
        $this->basketHelper = $basketHelper;
        $this->connection = $connection;
    }

    public function addConditionalLineItem(string $name, string $orderNumber, float $price, float $tax, int $mode): void
    {
        $currencyFactor = empty($this->system->sCurrency['factor']) ? 1 : $this->system->sCurrency['factor'];
        $taxFree = empty($this->system->sUSERGROUPDATA['tax']) && !empty($this->system->sUSERGROUPDATA['id']);
        $sessionId = $this->session->get('sessionId');

        if ($taxFree) {
            $netPrice = $price;
        } else {
            $netPrice = round($price / (100 + $tax) * 100, 2);
        }

        if (!$taxFree && $this->config->get('proportionalTaxCalculation')) {
            $this->basketHelper->addProportionalDiscount(
                new DiscountContext(
                    $sessionId,
                    BasketHelperInterface::DISCOUNT_ABSOLUTE,
                    $price,
                    $name,
                    $orderNumber,
                    $mode,
                    $currencyFactor,
                    $taxFree
                )
            );
        } else {
            $this->connection->insert(
                's_order_basket',
                [
                    'sessionID' => $sessionId,
                    'articlename' => $name,
                    'articleID' => 0,
                    'ordernumber' => $orderNumber,
                    'quantity' => 1,
                    'price' => $price,
                    'netprice' => $netPrice,
                    'tax_rate' => $tax,
                    'datum' => date('Y-m-d H:i:s'),
                    'modus' => $mode,
                    'currencyFactor' => $currencyFactor,
                ]
            );
        }
    }
}
