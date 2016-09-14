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
     *
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
     *
     * @param Enlight_Event_EventArgs $args
     */
    public function onPostDispatch(Enlight_Event_EventArgs $args)
    {
        /** @var Enlight_Controller_Action $controller */
        $controller = $args->get('subject');
        $request = $controller->Request();

        if (!$request->isDispatched() || $controller->Response()->isException() || $request->getModuleName() != 'frontend') {
            return;
        }

        $view = $controller->View();
        $sArticle = $view->getAssign('sArticle');

        $notificationVariants = array();

        /** @var Enlight_Components_Session_Namespace $session */
        $session = $this->get('session');
        $sNotificatedArticles = $session->get('sNotificatedArticles');

        if (!empty($sNotificatedArticles)) {
            $sql = 'SELECT `ordernumber`
                    FROM `s_articles_details`
                    WHERE `articleID` = ?';
            $ordernumbers = Shopware()->Db()->fetchCol($sql, array($sArticle['articleID']));

            foreach ($ordernumbers as $ordernumber) {
                if (in_array($ordernumber, $sNotificatedArticles)) {
                    $notificationVariants[] = $ordernumber;
                    if ($ordernumber === $sArticle['ordernumber']) {
                        $view->assign('NotifyAlreadyRegistered', true);
                    }
                }
            }
        }

        $view->assign('NotifyHideBasket', Shopware()->Config()->get('sDEACTIVATEBASKETONNOTIFICATION'));
        $view->assign('NotificationVariants', $notificationVariants);
        $view->assign('ShowNotification', true);
        $view->assign('WaitingForOptInApprovement', $session->sNotifcationArticleWaitingForOptInApprovement[$sArticle['ordernumber']]);
    }


    /**
     * Called on register for status updates
     * Check user email address and send double optin to confirm the email
     *
     * @param Enlight_Event_EventArgs $args
     *
     * @throws Enlight_Exception
     */
    public function onNotifyAction(Enlight_Event_EventArgs $args)
    {
        $args->setProcessed(true);

        /** @var Enlight_Controller_Action $controller */
        $controller = $args->get('subject');

        $view = $controller->View();
        $view->assign('NotifyEmailError', false);

        $sError = false;

        $request = $controller->Request();
        $notifyOrderNumber = $request->getParam('notifyOrdernumber');

        if (!empty($notifyOrderNumber)) {
            /** @var Enlight_Components_Session_Namespace $session */
            $session = $this->get('session');
            $email = $request->getParam('sNotificationEmail');

            if (empty($email) || !$this->get('validator.email')->isValid($email)) {
                $sError = true;
                $view->assign('NotifyEmailError', true);
            } else {
                $sNotificatedArticles = $session->get('sNotificatedArticles');
                if (!empty($sNotificatedArticles) && in_array($notifyOrderNumber, $sNotificatedArticles)) {
                    $sError = true;
                    $view->assign('ShowNotification', false);
                    $view->assign('NotifyAlreadyRegistered', true);
                } else {
                    $session->sNotificatedArticles[] = $notifyOrderNumber;
                }
            }

            if (!$sError) {
                $sql = 'SELECT id
                        FROM `s_articles_notification`
                        WHERE `ordernumber` = ?
                        AND `mail` = ?
                        AND send = 0';
                if (!empty(Shopware()->Db()->fetchOne($sql, array($notifyOrderNumber, $email)))) {
                    $view->assign('NotifyAlreadyRegistered', true);
                } else {
                    $view->assign('NotifyAlreadyRegistered', false);

                    $hash = \Shopware\Components\Random::getAlphanumericString(32);
                    $router = Shopware()->Front()->Router();
                    $articleID = (int)$request->getParam('sArticle');

                    $link = $router->assemble(array(
                        'sViewport' => 'detail',
                        'sArticle' => $articleID,
                        'sNotificationConfirmation' => $hash,
                        'sNotify' => '1',
                        'action' => 'notifyConfirm',
                        'number' => $notifyOrderNumber
                    ));

                    $request->setPost('sLanguage', Shopware()->Shop()->getId());
                    $request->setPost('sShopPath', $router->assemble(array(
                        'controller' => 'detail',
                        'sArticle' => $articleID
                    )));

                    $sql = 'INSERT INTO s_core_optin
                            SET 
                                datum = NOW(),
                                hash = ?,
                                `data` = ?';
                    $request->getPost();
                    Shopware()->Db()->query($sql, array($hash, serialize($request->getPost())));

                    $context = array(
                        'sConfirmLink' => $link,
                        'sArticleName' => Shopware()->Modules()->Articles()->sGetArticleNameByOrderNumber($notifyOrderNumber),
                    );

                    $mail = Shopware()->TemplateMail()->createMail('sACCEPTNOTIFICATION', $context);
                    $mail->addTo($email);
                    $mail->send();
                    $session->sNotifcationArticleWaitingForOptInApprovement[$notifyOrderNumber] = true;
                }
            }
        }

        return $controller->forward('index');
    }


    /**
     * If confirmation link in email was clicked
     * Make entry in s_articles_notification table
     *
     * @param Enlight_Event_EventArgs $args
     */
    public function onNotifyConfirmAction(Enlight_Event_EventArgs $args)
    {
        $args->setProcessed(true);

        /** @var Enlight_Controller_Action $controller */
        $controller = $args->get('subject');

        $view = $controller->View();
        $view->assign('NotifyValid', false);
        $view->assign('NotifyInvalid', false);

        $request = $controller->Request();
        $sNotificationConfirmation = $request->getParam('sNotificationConfirmation');

        if (!empty($sNotificationConfirmation) && !empty($request->getParam('sNotify'))) {
            $sql = 'SELECT `data`
                    FROM s_core_optin
                    WHERE hash = ?';
            $getOptInData = Shopware()->Db()->fetchOne($sql, array($sNotificationConfirmation));

            if (empty($getOptInData)) {
                $view->assign('NotifyInvalid', true);
            } else {
                $view->assign('NotifyValid', true);

                $sql = 'DELETE FROM s_core_optin
                        WHERE hash = ?';
                Shopware()->Db()->query($sql, array($sNotificationConfirmation));

                $json_data = unserialize($getOptInData);

                $sql = 'INSERT INTO s_articles_notification
                        SET 
                            ordernumber = ?,
                            `date` = NOW(),
                            mail = ?,
                            `language` = ?,
                            shopLink = ?,
                            send = 0';
                Shopware()->Db()->query($sql, array(
                    $json_data['notifyOrdernumber'],
                    $json_data['sNotificationEmail'],
                    $json_data['sLanguage'],
                    $json_data['sShopPath']
                ));

                Shopware()->Session()->sNotifcationArticleWaitingForOptInApprovement[$json_data['notifyOrdernumber']] = false;
            }
        }

        return $controller->forward('index');
    }


    /**
     * Cronjob method
     * Check all products from s_articles_notification
     * Inform customer if any status update available
     *
     * @param Shopware_Components_Cron_CronJob $job
     *
     * @return void
     */
    public function onRunCronJob(Shopware_Components_Cron_CronJob $job)
    {
        $sql = 'SELECT *
                FROM `s_articles_notification`
                WHERE send = 0';
        $getNotifications = Shopware()->Db()->fetchAll($sql);

        foreach ($getNotifications as $data) {
            $sql = 'SELECT a.id
                    FROM s_articles_details d
                    INNER JOIN s_articles a
                    ON 
                        a.id = d.articleID
                        AND
                        a.active = 1
                        AND
                        a.notification = 1
                    WHERE
                        d.ordernumber = ?
                        AND
                        d.instock > 0
                        AND 
                        d.active = 1';
            $articleID = (int)Shopware()->Db()->fetchOne($sql, array($data['ordernumber']));

            if ($articleID > 0) {
                $context = array(
                    'sArticleLink' => $data['shopLink'],
                    'sOrdernumber' => $data['ordernumber'],
                    'sData' => $job['data'],
                );


                $shop = Shopware()->Models()->getRepository('Shopware\Models\Shop\Shop')->findOneBy(array('id' => $data['language']));

                $mail = Shopware()->TemplateMail()->createMail('sARTICLEAVAILABLE', $context, $shop);
                $mail->addTo($data['mail']);
                $mail->send();

                $sql = 'UPDATE `s_articles_notification`
                        SET `send` = "1"
                        WHERE `ordernumber` = ?';
                Shopware()->Db()->query($sql, array($data['ordernumber']));
            }
        }
    }
}