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
use Shopware\Models\Shop\Shop;

/**
 * Shopware Notification Plugin
 *
 * Allows customers to register to any product in shop that is not on stock.
 * Informing this customers automatically via email as soon as the product becomes available
 * Includes a backend module that display statistics about the usage of this feature
 */
class Shopware_Plugins_Frontend_Notification_Bootstrap extends Shopware_Components_Plugin_Bootstrap
{
    /**
     * Installation of plugin
     * Create-Events to include custom code on product detail page
     * Creates new cronjob "notification"
     * @return bool
     */
    public function install()
    {
        $this->subscribeEvent(
            'Enlight_Controller_Action_PostDispatch_Frontend_Detail',
            'onPostDispatch'
        );
        $this->subscribeEvent(
            'Enlight_Controller_Action_Frontend_Detail_Notify',
            'onNotifyAction'
        );
        $this->subscribeEvent(
            'Enlight_Controller_Action_Frontend_Detail_NotifyConfirm',
            'onNotifyConfirmAction'
        );
        $this->subscribeEvent(
            'Shopware_CronJob_Notification',
            'onRunCronJob'
        );
        return true;
    }

    /**
     * Check if product is available (instock is greater then zero)
     * If not available display possibility to register for status updates
     * @static
     * @param Enlight_Event_EventArgs $args
     * @return
     */
    public static function onPostDispatch(Enlight_Event_EventArgs $args)
    {
        $request = $args->getSubject()->Request();
        $response = $args->getSubject()->Response();

        if (!$request->isDispatched() || $response->isException() || $request->getModuleName() != 'frontend') {
            return;
        }

        $id = (int) $args->getSubject()->Request()->sArticle;
        $view = $args->getSubject()->View();

        $notificationVariants = array();

        if (!empty(Shopware()->Session()->sNotificatedArticles)) {
            $sql = 'SELECT `ordernumber` FROM `s_articles_details` WHERE `articleID`=?';
            $ordernumbers = Shopware()->Db()->fetchCol($sql, $id);

            if (!empty($ordernumbers)) {
                foreach ($ordernumbers as $ordernumber) {
                    if (in_array($ordernumber, Shopware()->Session()->sNotificatedArticles)) {
                        $notificationVariants[] = $ordernumber;
                        if ($ordernumber === $view->sArticle['ordernumber']) {
                            $view->NotifyAlreadyRegistered = true;
                        }
                    }
                }
            }
        }

        $view->NotifyHideBasket = Shopware()->Config()->sDEACTIVATEBASKETONNOTIFICATION;

        $view->NotificationVariants = $notificationVariants;
        $view->ShowNotification = true;
        $view->WaitingForOptInApprovement = Shopware()->Session()->sNotifcationArticleWaitingForOptInApprovement[$view->sArticle['ordernumber']];
    }

    /**
     * Called on register for status updates
     * Check user email address and send double optin to confirm the email
     * @static
     * @param Enlight_Event_EventArgs $args
     * @return
     */
    public static function onNotifyAction(Enlight_Event_EventArgs $args)
    {
        $args->setProcessed(true);

        $action = $args->getSubject();

        $id = (int) $action->Request()->sArticle;
        $email = $action->Request()->sNotificationEmail;

        $sError = false;
        $action->View()->NotifyEmailError = false;
        $notifyOrderNumber = $action->Request()->notifyOrdernumber;
        if (!empty($notifyOrderNumber)) {
            $validator = Shopware()->Container()->get('validator.email');
            if (empty($email) || !$validator->isValid($email)) {
                $sError = true;
                $action->View()->NotifyEmailError = true;
            } elseif (!empty($notifyOrderNumber)) {
                if (!empty(Shopware()->Session()->sNotificatedArticles)) {
                    if (in_array($notifyOrderNumber, Shopware()->Session()->sNotificatedArticles)) {
                        $sError = true;
                        $action->View()->ShowNotification = false;
                        $action->View()->NotifyAlreadyRegistered = true;
                    } else {
                        Shopware()->Session()->sNotificatedArticles[] = $notifyOrderNumber;
                    }
                } else {
                    Shopware()->Session()->sNotificatedArticles = array($notifyOrderNumber);
                }
            } else {
                $sError = true;
            }

            if (!$sError) {
                $AlreadyNotified = Shopware()->Db()->fetchRow('
                    SELECT *  FROM `s_articles_notification`
                    WHERE `ordernumber`=?
                    AND `mail` = ?
                    AND send = 0
                ', array($notifyOrderNumber, $email));

                if (empty($AlreadyNotified)) {
                    $action->View()->NotifyAlreadyRegistered = false;

                    $hash = \Shopware\Components\Random::getAlphanumericString(32);
                    $link = $action->Front()->Router()->assemble(array(
                        'sViewport' => 'detail',
                        'sArticle' => $id,
                        'sNotificationConfirmation' => $hash,
                        'sNotify' => '1',
                        'action' => 'notifyConfirm',
                        'number' => $notifyOrderNumber
                    ));

                    $name = Shopware()->Modules()->Articles()->sGetArticleNameByOrderNumber($notifyOrderNumber);

                    $basePath = $action->Front()->Router()->assemble(array('sViewport' => 'index'));
                    Shopware()->System()->_POST['sLanguage'] = Shopware()->Shop()->getId();
                    Shopware()->System()->_POST['sShopPath'] = $basePath . Shopware()->Config()->sBASEFILE;

                    $sql = '
                        INSERT INTO s_core_optin (datum, hash, data)
                        VALUES (NOW(), ?, ?)
                    ';
                    Shopware()->Db()->query($sql, array($hash, serialize(Shopware()->System()->_POST->toArray())));

                    $context = array(
                        'sConfirmLink' => $link,
                        'sArticleName' => $name,
                    );

                    $mail = Shopware()->TemplateMail()->createMail('sACCEPTNOTIFICATION', $context);
                    $mail->addTo($email);
                    $mail->send();
                    Shopware()->Session()->sNotifcationArticleWaitingForOptInApprovement[$notifyOrderNumber] = true;
                } else {
                    $action->View()->NotifyAlreadyRegistered = true;
                }
            }
        }

        return $action->forward('index');
    }

    /**
     * If confirmation link in email was clicked
     * Make entry in s_articles_notification table
     * @static
     * @param Enlight_Event_EventArgs $args
     * @return
     */
    public static function onNotifyConfirmAction(Enlight_Event_EventArgs $args)
    {
        $args->setProcessed(true);

        $action = $args->getSubject();

        $action->View()->NotifyValid = false;
        $action->View()->NotifyInvalid = false;

        if (!empty($action->Request()->sNotificationConfirmation) && !empty($action->Request()->sNotify)) {
            $getConfirmation = Shopware()->Db()->fetchRow('
            SELECT * FROM s_core_optin WHERE hash = ?
            ', array($action->Request()->sNotificationConfirmation));

            $notificationConfirmed = false;
            if (!empty($getConfirmation['hash'])) {
                $notificationConfirmed = true;
                $json_data = unserialize($getConfirmation['data']);
                Shopware()->Db()->query('DELETE FROM s_core_optin WHERE hash=?', array($action->Request()->sNotificationConfirmation));
            }
            if ($notificationConfirmed) {
                $sql = '
                    INSERT INTO `s_articles_notification` (
                        `ordernumber` ,
                        `date` ,
                        `mail` ,
                        `language` ,
                        `shopLink` ,
                        `send`
                    )
                    VALUES (
                        ?, NOW(), ?, ?, ?, 0
                    );
                ';
                Shopware()->Db()->query($sql, array(
                    $json_data['notifyOrdernumber'],
                    $json_data['sNotificationEmail'],
                    $json_data['sLanguage'],
                    $json_data['sShopPath']
                ));
                $action->View()->NotifyValid = true;
                Shopware()->Session()->sNotifcationArticleWaitingForOptInApprovement[$json_data['notifyOrdernumber']] = false;
            } else {
                $action->View()->NotifyInvalid = true;
            }
        }
        return $action->forward('index');
    }

    /**
     * Cronjob method
     * Check all products from s_articles_notification
     * Inform customer if any status update available
     * @static
     * @param Shopware_Components_Cron_CronJob $job
     * @return void
     */
    public function onRunCronJob(Shopware_Components_Cron_CronJob $job)
    {
        $sql = "SELECT * FROM `s_articles_notification` WHERE send = 0";

        $getNotifications = Shopware()->Db()->fetchAll($sql);

        Shopware()->Models()->getRepository(Shop::class)->getActiveDefault()->registerResources();
        $context = $this->get('shopware_storefront.context_service')->getShopContext();

        foreach ($getNotifications as $data) {
            $ordernumber = $data["ordernumber"];

            $sArticle = Shopware()->Db()->fetchRow("SELECT a.id as articleID, d.ordernumber, d.instock, a.active FROM s_articles_details d, s_articles a WHERE d.articleID=a.id AND d.ordernumber=?", array($ordernumber));

            $sArticleID = $sArticle["articleID"];
            if (empty($sArticleID)) {
                continue;
            }

            $instock = $sArticle["instock"];

            $sql = "SELECT notification from s_articles WHERE ID = ?";
            $notificationActive = Shopware()->Db()->fetchOne($sql, array($sArticleID));

            if (intval($instock) > 0 && $notificationActive == true && !empty($sArticle["active"])) {
                $article = $this->get('shopware_storefront.product_service')->get($ordernumber, $context);

                if ($article) {
                    $article = $this->get('legacy_struct_converter')->convertProductStruct($article);
                }

                $context = [
                    'sArticleLink' => $data["shopLink"] . "?sViewport=detail&sArticle=$sArticleID",
                    'sOrdernumber' => $ordernumber,
                    'sData' => $job["data"],
                    'sArticle' => $article
                ];

                $mail = Shopware()->TemplateMail()->createMail('sARTICLEAVAILABLE', $context);
                $mail->addTo($data["mail"]);
                $mail->send();

                //set notification to already send
                $sql = "UPDATE `s_articles_notification` SET `send` = '1' WHERE `ordernumber` =?";
                Shopware()->Db()->query($sql, array($data["ordernumber"]));
                // doing update on s_articles_notification
            }
        }
    }
}
