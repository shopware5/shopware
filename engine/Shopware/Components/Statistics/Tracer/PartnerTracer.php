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

namespace Shopware\Components\Statistics\Tracer;

use Doctrine\DBAL\Connection;
use Enlight_Controller_Request_Request as Request;
use Shopware\Bundle\StoreFrontBundle\Struct\ShopContextInterface;
use Shopware\Components\DependencyInjection\Container;
use Shopware\Components\Statistics\StatisticTracerInterface;

class PartnerTracer implements StatisticTracerInterface
{
    /**
     * @var Connection
     */
    private $connection;

    /**
     * @var Container
     */
    private $container;

    /**
     * @param Connection $connection
     * @param Container  $container
     */
    public function __construct(Connection $connection, Container $container)
    {
        $this->connection = $connection;
        $this->container = $container;
    }

    public function trace(Request $request, ShopContextInterface $context)
    {
        $partner = $request->getParam('partner', $request->getParam('sPartner'));

        if ($campaignID = $this->getCampaign($partner)) {
            $this->setSession('sCampaign' . $campaignID);
            $this->connection->executeUpdate(
                'UPDATE s_campaigns_mailings SET clicked = clicked + 1 WHERE id = ?',
                [$campaignID]
            );

            return;
        }

        if ($partner !== null && strpos($partner, 'sCampaign') !== 0) {
            $row = $this->connection->fetchAssoc(
                'SELECT * FROM s_emarketing_partner WHERE active = 1 AND idcode = ?',
                [$partner]
            );

            if (!empty($row)) {
                $this->setPartnerCookie(
                    $row['idcode'],
                    $row['cookielifetime']
                );
            }
            $this->setSession($partner);

            return;
        }

        if ($request->getCookie('partner') !== null) {
            $partner = $this->connection->fetchColumn(
                'SELECT idcode FROM s_emarketing_partner WHERE active = 1 AND idcode = ?',
                [$request->getCookie('partner')]
            );

            $this->setSession($partner);
        }
    }

    private function setSession($partner)
    {
        if (!$this->container->initialized('session')) {
            return;
        }

        $session = $this->container->get('session');

        if (empty($partner)) {
            $session->offsetUnset('sPartner');

            return;
        }

        $session->offsetSet('sPartner', $partner);
    }

    /**
     * @param string|null $partner
     *
     * @return string|null
     */
    private function getCampaign(?string $partner): ?string
    {
        if ($partner === null) {
            return null;
        }

        if (strpos($partner, 'sCampaign') !== 0) {
            return null;
        }

        return (int) str_replace('sCampaign', '', $partner);
    }

    /**
     * @param string   $idCode
     * @param int|null $lifeTime
     */
    private function setPartnerCookie($idCode, $lifeTime): void
    {
        if (!$this->container->initialized('front')) {
            return;
        }

        $valid = 0;
        if ($lifeTime) {
            $valid = time() + $lifeTime;
        }

        $front = $this->container->get('front');
        $front->Response()->setCookie('partner', $idCode, $valid, '/');
    }
}
