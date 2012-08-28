<?php
/**
 * Shopware 4.0
 * Copyright © 2012 shopware AG
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
 *
 * @category   Shopware
 * @package    Shopware_Plugins
 * @subpackage Statistics
 * @copyright  Copyright (c) 2012, shopware AG (http://www.shopware.de)
 * @version    $Id$
 * @author     Heiner Lohaus
 * @author     $Author$
 */

/**
 * Shopware Statistics Plugin
 *
 * todo@all: Documentation
 */
class Shopware_Plugins_Frontend_Statistics_Bootstrap extends Shopware_Components_Plugin_Bootstrap
{
    /**
     * Install plugin method
     *
     * @return bool
     */
    public function install()
    {
        $this->subscribeEvent(
            'Enlight_Controller_Front_DispatchLoopShutdown',
            'onDispatchLoopShutdown'
        );

        $form = $this->Form();

        $parent = $this->Forms()->findOneBy(array('name' => 'Core'));
        $form->setParent($parent);

        $form->setElement('text', 'blockIp', array(
            'label' => 'IP von Statistiken ausschließen', 'value' => null,
            'scope' => \Shopware\Models\Config\Element::SCOPE_SHOP
        ));
        return true;
    }

    /**
     * @return array
     */
    public function getInfo()
    {
        return array(
            'label' => 'Statistiken'
        );
    }

    /**
     * Event listener method
     *
     * @param Enlight_Controller_EventArgs $args
     */
    public function onDispatchLoopShutdown(Enlight_Controller_EventArgs $args)
    {
        $request = $args->getRequest();
        $response = $args->getResponse();

        if ($response->isException()
            || $request->isXmlHttpRequest()
            || $request->getModuleName() != 'frontend'
            || $request->getControllerName() == 'captcha'
        ) {
            return;
        }

        if (!Shopware()->Shop()->get('esi')) {
            $this->updateLog($request, $response);
        }
    }

    /**
     * Check user is bot
     *
     * @param string $userAgent
     * @return bool
     */
    public function checkIsBot($userAgent)
    {
        $userAgent = preg_replace('/[^a-z]/', '', strtolower($userAgent));
        if (empty($userAgent)) {
            return false;
        }
        $result = false;
        $bots = preg_replace('/[^a-z;]/', '', strtolower(Shopware()->Config()->botBlackList));
        $bots = explode(';', $bots);
        if (!empty($userAgent) && str_replace($bots, '', $userAgent) != $userAgent) {
            $result = true;
        }
        return $result;
    }

    /**
     * @param Enlight_Controller_Request_RequestHttp $request
     * @param $response
     */
    public function updateLog($request, $response)
    {
        if (Shopware()->Session()->Bot === null) {
            Shopware()->Session()->Bot = $this->checkIsBot($request->getHeader('USER_AGENT'));
        }

        if ($this->shouldRefreshLog($request)) {
            $this->cleanupStatistic();
            $this->refreshBasket($request);
            $this->refreshLog($request);
            $this->refreshReferer($request);
            $this->refreshCurrentUsers($request);
            $this->refreshPartner($request, $response);
        }
    }

    /**
     * @param Enlight_Controller_Request_RequestHttp $request
     */
    public function refreshBasket($request)
    {
        $currentController = $request->getParam('requestController', $request->getControllerName());
        $sessionId = (string) Enlight_Components_Session::getId();

        if (!empty($currentController) && !empty($sessionId)) {
            $userId = (int) Shopware()->Session()->sUserId;
            $userAgent = (string) $request->getServer("HTTP_USER_AGENT");
            $sql = "
                UPDATE s_order_basket
                SET lastviewport = ?,
                    useragent = ?,
                    userID = ?
                WHERE sessionID=?
            ";
            Shopware()->Db()->query($sql, array(
                $currentController, $userAgent,
                $userId, $sessionId
            ));
        }
    }

    /**
     * @param $request
     * @return bool
     */
    public function shouldRefreshLog($request)
    {
        if ($request->getClientIp(false) === null
            || !empty(Shopware()->Session()->Bot)
        ) {
            return false;
        }
        if (!empty(Shopware()->Config()->blockIp)
            && strpos(Shopware()->Config()->blockIp, $request->getClientIp(false)) !== false
        ) {
            return false;
        }
        return true;
    }

    /**
     * Cleanup statistic
     */
    public function cleanupStatistic()
    {
        if ((rand() % 10) == 0) {
            $sql = 'DELETE FROM s_statistics_currentusers WHERE time < DATE_SUB(NOW(), INTERVAL 3 MINUTE)';
            Shopware()->Db()->query($sql);
            $sql = 'DELETE FROM s_statistics_pool WHERE datum!=CURDATE()';
            Shopware()->Db()->query($sql);
        }
    }

    /**
     * Refresh current users
     *
     * @param \Enlight_Controller_Request_RequestHttp $request
     */
    public function refreshCurrentUsers($request)
    {
        $sql = 'INSERT INTO s_statistics_currentusers (`remoteaddr`, `page`, `time`, `userID`) VALUES (?, ?, NOW(), ?)';
        Shopware()->Db()->query($sql, array(
            $request->getClientIp(false),
            $request->getParam('requestPage', $request->getRequestUri()),
            empty(Shopware()->Session()->sUserId) ? 0 : (int)Shopware()->Session()->sUserId
        ));
    }

    /**
     * Refresh visitor log
     *
     * @param Enlight_Controller_Request_RequestHttp $request
     */
    public function refreshLog($request)
    {
        $ip = $request->getClientIp(false);

        $shopId = Shopware()->Shop()->getId();

        $sql = 'SELECT id FROM s_statistics_visitors WHERE datum=CURDATE() AND shopID = ?';
        $result = Shopware()->Db()->fetchOne($sql, array($shopId));
        if (empty($result)) {
            $sql = 'INSERT INTO s_statistics_visitors (`datum`,`shopID`, `pageimpressions`, `uniquevisits`) VALUES(NOW(),?, 1, 1)';
            Shopware()->Db()->query($sql, array($shopId));
            return;
        }

        $sql = 'SELECT id FROM s_statistics_pool WHERE datum=CURDATE() AND remoteaddr=?';
        $result = Shopware()->Db()->fetchOne($sql, array($ip));
        if (empty($result)) {
            $sql = 'INSERT INTO s_statistics_pool (`remoteaddr`, `datum`) VALUES (?, NOW())';
            Shopware()->Db()->query($sql, array($ip));
            $sql = 'UPDATE s_statistics_visitors SET pageimpressions=pageimpressions+1, uniquevisits=uniquevisits+1 WHERE datum=CURDATE() AND shopID = ?';
            Shopware()->Db()->query($sql, array($shopId));
        } else {
            $sql = 'UPDATE s_statistics_visitors SET pageimpressions=pageimpressions+1 WHERE datum=CURDATE() AND shopID = ?';
            Shopware()->Db()->query($sql, array($shopId));
        }
    }

    /**
     * Refresh referrer log
     *
     * @param   \Enlight_Controller_Request_RequestHttp $request
     */
    public function refreshReferer($request)
    {
        $referer = $request->getHeader('Referer', $request->getParam('referer'));
        $partner = $request->getParam('partner', $request->getParam('sPartner'));

        if (empty($referer)
            || strpos($referer, 'http') !== 0
            || strpos($referer, $request->getHttpHost()) !== false
        ) {
            return;
        }

        Shopware()->Session()->sReferer = $referer;

        if ($partner !== null) {
            $referer .= '$' . $partner;
        }

        $sql = 'INSERT INTO s_statistics_referer (datum, referer) VALUES (NOW(), ?)';
        Shopware()->Db()->query($sql, array($referer));
    }

    /**
     * Refresh partner log
     *
     * @param   \Enlight_Controller_Request_RequestHttp $request
     * @param   \Enlight_Controller_Response_ResponseHttp $response
     */
    public function refreshPartner($request, $response)
    {
        $partner = $request->getParam('partner', $request->getParam('sPartner'));
        if ($partner !== null) {
            if (strpos($partner, 'sCampaign') === 0) {
                $campaignID = (int) str_replace('sCampaign', '', $partner);
                if (!empty($campaignID)) {
                    Shopware()->Session()->sPartner = 'sCampaign' . $campaignID;
                    $sql = '
						UPDATE s_campaigns_mailings
						SET clicked = clicked + 1
						WHERE id = ?
					';
                    Shopware()->Db()->query($sql, array($campaignID));
                }
            } else {
                $sql = 'SELECT * FROM s_emarketing_partner WHERE active=1 AND idcode=?';
                $row = Shopware()->Db()->fetchRow($sql, array($partner));
                if (!empty($row)) {
                    if ($row['cookielifetime']) {
                        $valid = time() + $row['cookielifetime'];
                    } else {
                        $valid = 0;
                    }
                    $response->setCookie('partner', $row['idcode'], $valid, '/');
                }
                Shopware()->Session()->sPartner = $partner;
            }
        } elseif ($request->getCookie('partner') !== null) {
            $sql = 'SELECT idcode FROM s_emarketing_partner WHERE active=1 AND idcode=?';
            $partner = Shopware()->Db()->fetchOne($sql, array($request->getCookie('partner')));
            if (empty($partner)) {
                unset(Shopware()->Session()->sPartner);
            } else {
                Shopware()->Session()->sPartner = $partner;
            }
        }
    }
}