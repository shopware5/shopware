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

namespace Shopware\Controllers\Backend;

use Exception;
use Shopware\Components\Model\ModelManager;
use Shopware\Components\Privacy\IpAnonymizerInterface;
use Shopware\Models\Log\Log;
use Shopware\Models\Shop\Shop;
use Symfony\Component\HttpFoundation\Request;

class Logger extends \Enlight_Controller_Action
{
    /**
     * @var IpAnonymizerInterface
     */
    private $ipAnonymizer;

    /**
     * @var ModelManager
     */
    private $em;

    public function __construct(ModelManager $em, IpAnonymizerInterface $ipAnonymizer)
    {
        $this->em = $em;
        $this->ipAnonymizer = $ipAnonymizer;
    }

    public function preDispatch()
    {
        parent::preDispatch();
        $this->get('shopware.components.shop_registration_service')->registerShop($this->getModelManager()->getRepository(Shop::class)->getActiveDefault());
    }

    /**
     * This method is called when a new log is made automatically.
     * It sets the different values and saves the log into `s_core_log`
     */
    public function createLogAction(Request $request)
    {
        $this->front->Plugins()->Json()->setRenderer(true);

        try {
            $params = $request->request->all();
            $params['key'] = html_entity_decode($params['key']);

            $ip = $this->ipAnonymizer->anonymize($request->getClientIp());

            $logModel = new Log();
            $logModel->fromArray($params);
            $logModel->setDate(new \DateTime('now'));
            $logModel->setIpAddress($ip);
            $logModel->setUserAgent($request->server->get('HTTP_USER_AGENT', 'Unknown'));

            $this->em->persist($logModel);
            $this->em->flush();

            $data = $this->em->toArray($logModel);

            $this->View()->assign(['success' => true, 'data' => $data]);
        } catch (Exception $e) {
            $this->View()->assign(['success' => false, 'errorMsg' => $e->getMessage()]);
        }
    }
}
