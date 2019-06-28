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

namespace Shopware\Bundle\StaticContentBundle\Service\Core;

use Shopware\Bundle\StaticContentBundle\Gateway\EsdGatewayInterface;
use Shopware\Bundle\StaticContentBundle\Service\EsdServiceInterface;
use Shopware\Bundle\StoreFrontBundle\Struct\Product\Esd;
use Shopware_Components_Config;

class EsdService implements EsdServiceInterface
{
    /**
     * @var Shopware_Components_Config
     */
    private $config;

    /**
     * @var EsdGatewayInterface
     */
    private $esdGateway;

    public function __construct(
        Shopware_Components_Config $config,
        EsdGatewayInterface $esdGateway
    ) {
        $this->config = $config;
        $this->esdGateway = $esdGateway;
    }

    /**
     * {@inheritdoc}
     */
    public function loadEsdOfCustomer(int $customerId, int $esdId): Esd
    {
        return $this->esdGateway->loadEsdOfCustomer($customerId, $esdId);
    }

    /**
     * {@inheritdoc}
     */
    public function getLocation(Esd $esd): string
    {
        return $this->config->offsetGet('esdKey') . '/' . $esd->getFile();
    }
}
