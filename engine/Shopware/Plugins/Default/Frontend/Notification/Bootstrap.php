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

use Doctrine\DBAL\Connection;
use Shopware\Bundle\AttributeBundle\Service\DataLoaderInterface;
use Shopware\Bundle\StoreFrontBundle\Service\ContextServiceInterface;
use Shopware\Bundle\StoreFrontBundle\Service\ListProductServiceInterface;
use Shopware\Components\Captcha\CaptchaValidator;
use Shopware\Components\Captcha\NoCaptcha;
use Shopware\Components\Model\ModelManager;
use Shopware\Components\Random;
use Shopware\Components\Routing\Context;
use Shopware\Components\Routing\RouterInterface;
use Shopware\Components\ShopRegistrationServiceInterface;
use Shopware\Components\Validator\EmailValidator;
use Shopware\Models\Shop\Shop;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

/**
 * Shopware Notification Plugin
 *
 * Allows customers to register to any product in shop that is not on stock.
 * Informing this customers automatically via email as soon as the product becomes available
 * Includes a backend module that display statistics about the usage of this feature
 */
class Shopware_Plugins_Frontend_Notification_Bootstrap extends Shopware_Components_Plugin_Bootstrap
{
    private const CONFIG_NOTIFY_CAPTCHA = 'notificationCaptchaConfig';

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
     *
     * @return void
     */
    public function onPostDispatch(Enlight_Event_EventArgs $args)
    {
        /** @var Enlight_Controller_Action $subject */
        $subject = $args->getSubject();

        $request = $subject->Request();
        $response = $subject->Response();

        if (!$request->isDispatched() || $response->isException() || $request->getModuleName() !== 'frontend') {
            return;
        }

        $id = (int) $subject->Request()->getParam('sArticle');
        $view = $subject->View();
        /** @var SessionInterface $session */
        $session = $this->get('session');

        $sArticle = $view->getAssign('sArticle');

        $notificationVariants = [];

        $notificationProducts = $session->get('sNotificatedArticles', []);

        if (!empty($notificationProducts)) {
            $sql = 'SELECT `ordernumber` FROM `s_articles_details` WHERE `articleID`=?';
            $ordernumbers = $this->get(Connection::class)->fetchColumn($sql, [$id]);

            if (!empty($ordernumbers)) {
                foreach ($ordernumbers as $ordernumber) {
                    if (\in_array($ordernumber, $notificationProducts)) {
                        $notificationVariants[] = $ordernumber;
                        if ($ordernumber === $sArticle) {
                            $view->assign('NotifyAlreadyRegistered', true);
                        }
                    }
                }
            }
        }

        $view->assign('NotifyHideBasket', $this->get('config')->get('sDEACTIVATEBASKETONNOTIFICATION'));
        $view->assign('NotificationVariants', $notificationVariants);
        $view->assign('ShowNotification', true);

        $sNotificationArticleWaitingForOptInApprovement = $session->get('sNotifcationArticleWaitingForOptInApprovement', []);
        $view->assign('WaitingForOptInApprovement', $sNotificationArticleWaitingForOptInApprovement[$sArticle['ordernumber']] ?? null);
    }

    /**
     * Called on register for status updates
     * Check user email address and send double optin to confirm the email
     *
     * @static
     *
     * @throws Exception
     *
     * @return void
     */
    public function onNotifyAction(Enlight_Event_EventArgs $args)
    {
        $args->setProcessed(true);

        /** @var Enlight_Controller_Action $action */
        $action = $args->getSubject();

        $id = (int) $action->Request()->getParam('sArticle');
        $email = $action->Request()->getParam('sNotificationEmail');

        $sError = false;
        $action->View()->assign('NotifyEmailError', false);
        $notifyOrderNumber = $action->Request()->getParam('notifyOrdernumber');
        $connection = $this->get(Connection::class);

        $session = $this->get('session');

        $sNotificatedArticles = $session->get('sNotificatedArticles', []);

        if (!empty($notifyOrderNumber)) {
            $captchaValidator = $this->get(CaptchaValidator::class);
            $config = $this->get(Shopware_Components_Config::class);

            if ($config->get(self::CONFIG_NOTIFY_CAPTCHA) !== NoCaptcha::CAPTCHA_METHOD) {
                if (!empty($config->get('CaptchaColor'))) {
                    if (!$captchaValidator->validateByName($config->get(self::CONFIG_NOTIFY_CAPTCHA), $action->Request())) {
                        $sError = true;
                        $action->View()->assign('NotifyCaptchaError', true);
                        $action->View()->assign('ShowNotification', true);
                    }
                }
            }

            $validator = $this->get(EmailValidator::class);
            if (empty($email) || !$validator->isValid($email)) {
                $sError = true;
                $action->View()->assign('NotifyEmailError', true);
            } elseif (!empty($sNotificatedArticles)) {
                if (\in_array($notifyOrderNumber, $sNotificatedArticles)) {
                    $sError = true;
                    $action->View()->assign('ShowNotification', false);
                    $action->View()->assign('NotifyAlreadyRegistered', true);
                } else {
                    $sNotificatedArticles[] = $notifyOrderNumber;
                }
            } else {
                $sNotificatedArticles = [$notifyOrderNumber];
            }

            $session->set('sNotificatedArticles', $sNotificatedArticles);

            if (!$sError) {
                $AlreadyNotified = $connection->fetchAssoc('
                    SELECT *  FROM `s_articles_notification`
                    WHERE `ordernumber`=?
                    AND `mail` = ?
                    AND send = 0
                ', [$notifyOrderNumber, $email]);

                if (empty($AlreadyNotified)) {
                    $action->View()->assign('NotifyAlreadyRegistered', false);

                    $hash = Random::getAlphanumericString(32);
                    $link = $action->Front()->ensureRouter()->assemble([
                        'sViewport' => 'detail',
                        'sArticle' => $id,
                        'sNotificationConfirmation' => $hash,
                        'sNotify' => '1',
                        'action' => 'notifyConfirm',
                        'number' => $notifyOrderNumber,
                    ]);

                    /** @var Shopware_Components_Modules $modules */
                    $modules = $this->get('modules');

                    $name = $modules->Articles()->sGetArticleNameByOrderNumber($notifyOrderNumber);

                    $basePath = $action->Front()->ensureRouter()->assemble(['sViewport' => 'index']);
                    $modules->System()->_POST['sLanguage'] = Shopware()->Shop()->getId();
                    $modules->System()->_POST['sShopPath'] = $basePath . Shopware()->Config()->get('sBASEFILE');

                    $sql = '
                        INSERT INTO s_core_optin (datum, hash, data, type)
                        VALUES (NOW(), ?, ?, "swNotification")
                    ';
                    $connection->executeQuery($sql, [$hash, serialize(Shopware()->System()->_POST->toArray())]);

                    $context = [
                        'sConfirmLink' => $link,
                        'sArticleName' => $name,
                    ];

                    $mail = $this->get('templatemail')->createMail('sACCEPTNOTIFICATION', $context);
                    $mail->addTo($email);
                    $mail->send();

                    $sNotificationArticleWaitingForOptInApprovement = $session->get('sNotifcationArticleWaitingForOptInApprovement', []);
                    $sNotificationArticleWaitingForOptInApprovement[$notifyOrderNumber] = true;
                    $session->set('sNotifcationArticleWaitingForOptInApprovement', $sNotificationArticleWaitingForOptInApprovement);
                } else {
                    $action->View()->assign('NotifyAlreadyRegistered', true);
                }
            }
        }

        $action->forward('index');
    }

    /**
     * If confirmation link in email was clicked
     * Make entry in s_articles_notification table
     *
     * @static
     *
     * @return void
     */
    public function onNotifyConfirmAction(Enlight_Event_EventArgs $args)
    {
        $args->setProcessed(true);

        /** @var Shopware_Controllers_Frontend_Detail $action */
        $action = $args->getSubject();

        $db = $this->get(Connection::class);

        $action->View()->assign('NotifyValid', false);
        $action->View()->assign('NotifyInvalid', false);
        $sNotificationConfirmation = $action->Request()->getParam('sNotificationConfirmation');
        $sNotify = $action->Request()->getParam('sNotify');

        if (!empty($sNotificationConfirmation) && !empty($sNotify)) {
            $getConfirmation = $db->fetchAssociative('
            SELECT * FROM s_core_optin WHERE hash = ?
            ', [$sNotificationConfirmation]);

            $notificationConfirmed = false;
            $json_data = [];
            if (!empty($getConfirmation['hash'])) {
                $notificationConfirmed = true;
                $json_data = unserialize($getConfirmation['data'], ['allowed_classes' => false]);
                $db->executeQuery('DELETE FROM s_core_optin WHERE hash=?', [$sNotificationConfirmation]);
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
                $db->executeQuery($sql, [
                    $json_data['notifyOrdernumber'],
                    $json_data['sNotificationEmail'],
                    $json_data['sLanguage'],
                    $json_data['sShopPath'],
                ]);

                $insertId = $db->lastInsertId();

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

                $action->View()->assign('NotifyValid', true);

                $session = $this->get('session');
                $sNotificationArticleWaitingForOptInApprovement = $session->get('sNotifcationArticleWaitingForOptInApprovement', []);
                $sNotificationArticleWaitingForOptInApprovement[$json_data['notifyOrdernumber']] = true;
                $session->set('sNotifcationArticleWaitingForOptInApprovement', $sNotificationArticleWaitingForOptInApprovement);
            } else {
                $action->View()->assign('NotifyInvalid', true);
            }
        }

        $action->forward('index');
    }

    /**
     * Cronjob method
     * Check all products from s_articles_notification
     * Inform customer if any status update available
     *
     * @return void
     */
    public function onRunCronJob(Shopware_Components_Cron_CronJob $job)
    {
        $modelManager = $this->get(ModelManager::class);

        $conn = $this->get(Connection::class);

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

            $product = $queryBuilder->execute()->fetch(PDO::FETCH_ASSOC);

            if (
                empty($product)   // No product associated with the specified order number (empty result set)
                || empty($product['articleID']) // or empty articleID
                || empty($product['notification']) // or notification disabled on product
                || empty($product['active']) // or product is not active
            ) {
                continue;
            }

            $attributeLoader = $this->get(DataLoaderInterface::class);
            $notify['attribute'] = $attributeLoader->load('s_articles_notification_attributes', $notify['id']);

            $shop = $modelManager->getRepository(Shop::class)->getActiveById($notify['language']);

            // Continue if shop is inactive or deleted
            if ($shop === null) {
                continue;
            }

            $this->get(ShopRegistrationServiceInterface::class)->registerShop($shop);

            $shopContext = Context::createFromShop($shop, $this->get(Shopware_Components_Config::class));
            $this->get(RouterInterface::class)->setContext($shopContext);
            $sContext = $this->get(ContextServiceInterface::class)->createShopContext($notify['language']);

            $productInformation = $this->get(ListProductServiceInterface::class)->get($notify['ordernumber'], $sContext);

            if (empty($productInformation)) {
                continue;
            }

            $productInformation = $this->get('legacy_struct_converter')->convertListProductStruct($productInformation);

            $link = Shopware()->Front()->ensureRouter()->assemble([
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
