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

namespace Shopware\Controllers\Backend;

use DateTime;
use Enlight_Controller_Action;
use Exception;
use Shopware\Components\Model\ModelManager;
use Shopware\Components\Privacy\IpAnonymizerInterface;
use Shopware\Models\Log\Log;
use Symfony\Component\HttpFoundation\Request;

class Logger extends Enlight_Controller_Action
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
            $logModel->setDate(new DateTime('now'));
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
