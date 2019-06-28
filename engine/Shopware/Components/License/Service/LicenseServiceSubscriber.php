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

namespace Shopware\Components\License\Service;

use Enlight\Event\SubscriberInterface;
use Enlight_Event_EventArgs;
use Enlight_View_Default;
use Shopware\Components\License\Struct\LicenseInformation;
use Shopware\Components\License\Struct\LicenseUnpackRequest;
use Shopware\Components\License\Struct\ShopwareEdition;
use Symfony\Component\DependencyInjection\ContainerInterface;

class LicenseServiceSubscriber implements SubscriberInterface
{
    /**
     * @var ContainerInterface
     */
    private $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            'Enlight_Controller_Action_PostDispatchSecure_Backend_Index' => 'onPostDispatchBackendIndex',
        ];
    }

    public function onPostDispatchBackendIndex(Enlight_Event_EventArgs $args)
    {
        /** @var Enlight_View_Default $view */
        $view = $args->getSubject()->View();
        $view->assign('product', ShopwareEdition::CE);

        $sql = 'SELECT license FROM s_core_licenses WHERE active=1 AND module = "SwagCommercial"';
        $license = $this->container
            ->get('dbal_connection')
            ->query($sql)
            ->fetchColumn()
        ;
        if (!$license) {
            return;
        }

        $repository = $this->container->get('models')
            ->getRepository('Shopware\Models\Shop\Shop')
        ;
        $host = $repository->getActiveDefault()->getHost();
        $request = new LicenseUnpackRequest($license, $host);

        try {
            /** @var LicenseInformation $licenseInformation */
            $licenseInformation = $this->container
                ->get('shopware_core.local_license_unpack_service')
                ->evaluateLicense($request)
            ;
        } catch (\RuntimeException $e) {
            return;
        }

        $view->assign('product', $licenseInformation->edition);
    }
}
