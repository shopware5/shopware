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

use Shopware\Components\Routing\Context;

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
     * @static
     */
    public function onPostDispatch(Enlight_Event_EventArgs $args)
    {
        $request = $args->getSubject()->Request();
        $response = $args->getSubject()->Response();

        if (!$request->isDispatched() || $response->isException() || $request->getModuleName() != 'frontend') {
            return;
        }

        $id = (int) $args->getSubject()->Request()->sArticle;
        $view = $args->getSubject()->View();

        $notificationVariants = [];

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
     *
     * @static
     *
     * @return
     */
    public function onNotifyAction(Enlight_Event_EventArgs $args)
    {
        $args->setProcessed(true);

        $action = $args->getSubject();

        $id = (int) $action->Request()->sArticle;
        $email = $action->Request()->sNotificationEmail;

        $sError = false;
        $action->View()->NotifyEmailError = false;
        $notifyOrderNumber = $action->Request()->notifyOrdernumber;
        if (!empty($notifyOrderNumber)) {
            $validator = $this->get('validator.email');
            if (empty($email) || !$validator->isValid($email)) {
                $sError = true;
                $action->View()->NotifyEmailError = true;
            } else {
                if (!empty(Shopware()->Session()->sNotificatedArticles)) {
                    if (in_array($notifyOrderNumber, Shopware()->Session()->sNotificatedArticles)) {
                        $sError = true;
                        $action->View()->ShowNotification = false;
                        $action->View()->NotifyAlreadyRegistered = true;
                    } else {
                        Shopware()->Session()->sNotificatedArticles[] = $notifyOrderNumber;
                    }
                } else {
                    Shopware()->Session()->sNotificatedArticles = [$notifyOrderNumber];
                }
            }

            if (!$sError) {
                $AlreadyNotified = Shopware()->Db()->fetchRow('
                    SELECT *  FROM `s_articles_notification`
                    WHERE `ordernumber`=?
                    AND `mail` = ?
                    AND send = 0
                ', [$notifyOrderNumber, $email]);

                if (empty($AlreadyNotified)) {
                    $action->View()->NotifyAlreadyRegistered = false;

                    $hash = \Shopware\Components\Random::getAlphanumericString(32);
                    $link = $action->Front()->Router()->assemble([
                        'sViewport' => 'detail',
                        'sArticle' => $id,
                        'sNotificationConfirmation' => $hash,
                        'sNotify' => '1',
                        'action' => 'notifyConfirm',
                        'number' => $notifyOrderNumber,
                    ]);

                    $name = Shopware()->Modules()->Articles()->sGetArticleNameByOrderNumber($notifyOrderNumber);

                    $basePath = $action->Front()->Router()->assemble(['sViewport' => 'index']);
                    Shopware()->System()->_POST['sLanguage'] = Shopware()->Shop()->getId();
                    Shopware()->System()->_POST['sShopPath'] = $basePath . Shopware()->Config()->sBASEFILE;

                    $sql = '
                        INSERT INTO s_core_optin (datum, hash, data, type)
                        VALUES (NOW(), ?, ?, "swNotification")
                    ';
                    Shopware()->Db()->query($sql, [$hash, serialize(Shopware()->System()->_POST->toArray())]);

                    $context = [
                        'sConfirmLink' => $link,
                        'sArticleName' => $name,
                    ];

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
     *
     * @static
     *
     * @return
     */
    public function onNotifyConfirmAction(Enlight_Event_EventArgs $args)
    {
        $args->setProcessed(true);

        $action = $args->getSubject();

        $db = $this->get('db');

        $action->View()->NotifyValid = false;
        $action->View()->NotifyInvalid = false;

        if (!empty($action->Request()->sNotificationConfirmation) && !empty($action->Request()->sNotify)) {
            $getConfirmation = Shopware()->Db()->fetchRow('
            SELECT * FROM s_core_optin WHERE hash = ?
            ', [$action->Request()->sNotificationConfirmation]);

            $notificationConfirmed = false;
            $json_data = [];
            if (!empty($getConfirmation['hash'])) {
                $notificationConfirmed = true;
                $json_data = unserialize($getConfirmation['data'], ['allowed_classes' => false]);
                $db->query('DELETE FROM s_core_optin WHERE hash=?', [$action->Request()->sNotificationConfirmation]);
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
                $db->query($sql, [
                    $json_data['notifyOrdernumber'],
                    $json_data['sNotificationEmail'],
                    $json_data['sLanguage'],
                    $json_data['sShopPath'],
                ]);

                $insertId = $db->lastInsertId();

                /** @var Enlight_Event_EventManager $eventManager */
                $eventManager = $this->get('events');

                $params = [
                    'notificationID' => $insertId,
                ];

                $params = $eventManager->filter('Shopware_Notification_Notification_FilterParams', $params, [
                    'subject' => $this,
                    'id' => $insertId,
                    'data' => $json_data,
                ]);

                $db->insert(
                    's_articles_notification_attributes',
                    $params
                );

                $eventManager->notify('Shopware_Notification_Notification_Saved', [
                    'subject' => $this,
                    'id' => $insertId,
                    'data' => $json_data,
                ]);

                $action->View()->NotifyValid = true;
                $this->get('session')->sNotifcationArticleWaitingForOptInApprovement[$json_data['notifyOrdernumber']] = false;
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
     *
     * @static
     */
    public function onRunCronJob(Shopware_Components_Cron_CronJob $job)
    {
        $modelManager = $this->get('models');

        $conn = $this->get('dbal_connection');

        $notifications = $conn->createQueryBuilder()
            ->select(
                'n.id',
                'n.ordernumber',
                'n.mail',
                'n.language'
            )
            ->from('s_articles_notification n')
            ->where('send=:send')
            ->setParameter('send', 0)
            ->execute()
            ->fetchAll();

        foreach ($notifications as $notify) {
            $queryBuilder = $conn->createQueryBuilder();

            $queryBuilder
                ->select(
                    'a.id AS articleID',
                    'a.active',
                    'a.notification',
                    'd.ordernumber',
                    'd.minpurchase',
                    'd.instock',
                    'd.laststock'
                )
                ->from('s_articles_details', 'd')
                ->innerJoin('d', 's_articles', 'a', 'd.articleID = a.id')
                ->where('d.ordernumber = :number')
                ->andWhere('d.instock > 0')
                ->andWhere('d.minpurchase <= d.instock')
                ->setParameter('number', $notify['ordernumber']);

            $this->get('events')->notify(
                'Shopware_CronJob_Notification_Product_QueryBuilder',
                [
                    'queryBuilder' => $queryBuilder,
                ]
            );

            $product = $queryBuilder->execute()->fetch(\PDO::FETCH_ASSOC);

            if (
                empty($product)   // No product associated with the specified order number (empty result set)
                || empty($product['articleID']) // or empty articleID
                || empty($product['notification']) // or notification disabled on product
                || empty($product['active']) // or product is not active
            ) {
                continue;
            }

            /** @var Shopware\Bundle\AttributeBundle\Service\DataLoader $attributeLoader */
            $attributeLoader = $this->get('shopware_attribute.data_loader');
            $notify['attribute'] = $attributeLoader->load('s_articles_notification_attributes', $notify['id']);

            /* @var \Shopware\Models\Shop\Shop $shop */
            $shop = $modelManager->getRepository(\Shopware\Models\Shop\Shop::class)->getActiveById($notify['language']);

            // Continue if shop is inactive or deleted
            if ($shop === null) {
                continue;
            }

            $this->get('shopware.components.shop_registration_service')->registerShop($shop);

            $shopContext = Context::createFromShop($shop, $this->get('config'));
            $this->get('router')->setContext($shopContext);
            $sContext = $this->get('shopware_storefront.context_service')->createShopContext($notify['language']);

            $productInformation = $this->get('shopware_storefront.list_product_service')->get($notify['ordernumber'], $sContext);

            if (empty($productInformation)) {
                continue;
            }

            $productInformation = $this->get('legacy_struct_converter')->convertListProductStruct($productInformation);

            $link = Shopware()->Front()->Router()->assemble([
                'sViewport' => 'detail',
                'sArticle' => $product['articleID'],
                'number' => $product['ordernumber'],
            ]);

            $context = [
                'sNotifyData' => $notify,
                'sArticleLink' => $link,
                'sOrdernumber' => $notify['ordernumber'],
                'sData' => $job['data'],
                'product' => $productInformation,
            ];

            $mail = Shopware()->TemplateMail()->createMail('sARTICLEAVAILABLE', $context);
            $mail->addTo($notify['mail']);
            $mail->send();

            // Set notification to already sent
            $conn->update(
                's_articles_notification',
                ['send' => 1],
                ['orderNumber' => $notify['ordernumber']]
            );
        }
    }
}
