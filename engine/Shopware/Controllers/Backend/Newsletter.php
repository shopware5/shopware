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

use Shopware\Bundle\MailBundle\Service\Filter\NewsletterMailFilter;
use Shopware\Components\CSRFWhitelistAware;
use Symfony\Component\HttpFoundation\Response;

class Shopware_Controllers_Backend_Newsletter extends Enlight_Controller_Action implements CSRFWhitelistAware
{
    /**
     * Init controller method
     *
     * Disables the authorization-checking and template renderer.
     */
    public function init()
    {
        Shopware()->Plugins()->Backend()->Auth()->setNoAuth();
        Shopware()->Plugins()->Controller()->ViewRenderer()->setNoRender();
    }

    /**
     * {@inheritdoc}
     */
    public function getWhitelistedCSRFActions()
    {
        return [
            'view',
            'index',
            'cron',
            'log',
        ];
    }

    /**
     * Index action method
     *
     * Forwards the request to the proper action.
     */
    public function indexAction()
    {
        if ($this->Request()->getParam('id')) {
            if ($this->Request()->getParam('testmail')) {
                return $this->forward('mail');
            }

            return $this->forward('view');
        } elseif ($this->Request()->getParam('campaign')) {
            return $this->forward('view');
        }

        return $this->forward('cron');
    }

    /**
     * View action method
     *
     * Shows a complete preview of the newsletter.
     */
    public function viewAction()
    {
        $hash = null;

        if ($this->Request()->getParam('id')) {
            $mailingID = (int) $this->Request()->getParam('id');
            if (!Shopware()->Container()->get('auth')->hasIdentity()) {
                $hash = $this->createHash($mailingID);
                if ($hash !== $this->Request()->getParam('hash')) {
                    return;
                }
            }
        } else {
            $mailingID = (int) $this->Request()->getParam('campaign');
            $mailaddressID = (int) $this->Request()->getParam('mailaddress');
            $hash = $this->createHash($mailaddressID, $mailingID);
            if ($hash !== $this->Request()->getParam('hash')) {
                return;
            }
        }

        $mailing = $this->initMailing($mailingID);
        $template = $this->initTemplate($mailing);

        if (!empty($mailaddressID)) {
            $sql = 'SELECT email FROM s_campaigns_mailaddresses WHERE id=?';
            $email = Shopware()->Db()->fetchOne($sql, [$mailaddressID]);
            $user = $this->getMailingUserByEmail($email);
            $template->assign('sUser', $user, true);
            $template->assign('sCampaignHash', $hash, true);
            $template->assign('sRecommendations', $this->getMailingSuggest($mailing['id'], $user['userID']), true);
        }

        $body = $template->fetch('newsletter/index/' . $mailing['template'], $template);

        if (!$this->Request()->getParam('id')) {
            $body = $this->trackFilter($body, $mailing['id']);
        }

        if (empty($mailing['plaintext'])) {
            $body = $template->fetch('newsletter/index/' . $mailing['template'], $template);
        } else {
            $body = $template->fetch('newsletter/alt/' . $mailing['template'], $template);
        }

        if (empty($mailing['plaintext'])) {
            if (!$this->Request()->getParam('id')) {
                $body = $this->trackFilter($body, $mailing['id']);
            }
        } else {
            $this->Response()->headers->set('content-type', 'text/plain');
            $body = $this->altFilter($body);
        }

        echo $body;
    }

    /**
     * Mail action method
     *
     * Sends one or more newsletter emails.
     */
    public function mailAction()
    {
        $mailingID = (int) $this->Request()->getParam('id');

        if (!empty($mailingID) && !Shopware()->Container()->get('auth')->hasIdentity()) {
            return;
        }

        // Default cron operation - will automatically get a newsletter which needs to be send
        if (empty($mailingID)) {
            $mailing = $this->initMailing();
            if (empty($mailing)) {
                echo "Nothing to do...\n";

                return;
            }

            $subjectCurrentMailing = $mailing['subject'];

            // Check lock time. Add a buffer of 30 seconds to the lock time (default request time)
            if (!empty($mailing['locked']) && strtotime($mailing['locked']) > time() - 30) {
                echo "Current mail: '" . $subjectCurrentMailing . "'\n";
                echo 'Wait ' . (strtotime($mailing['locked']) + 30 - time()) . " seconds ...\n";

                return;
            }

            // When entering the mail dispatch, set lock time to 15 minutes in the future *if* the
            // last lock time is in the past
            $sql = 'UPDATE s_campaigns_mailings SET locked=? WHERE id=? AND (locked < ? OR locked IS NULL)';
            $result = Shopware()->Db()->query($sql, [
                    date('Y-m-d H:i:s', time() + 15 * 60),
                    $mailing['id'],
                    date('Y-m-d H:i:s'),
                ]
            );

            // If no rows were affected, exit
            if (!$result->rowCount()) {
                echo "Lock condition detected ...\n";

                return;
            }

            // Get mails for the current newsletter. If no mails are returned, set status=2 (completed)
            $emails = $this->getMailingEmails($mailing['id']);
            if (empty($emails)) {
                $sql = 'UPDATE s_campaigns_mailings SET status=2 WHERE id=?';
                Shopware()->Db()->query($sql, [$mailing['id']]);
                echo "Current mail: '" . $subjectCurrentMailing . "'\n";
                echo "Mailing completed\n";

                return;
            }

            // Set lock time to 15 minutes in the future
            // As the above getMailingEmails query might be quite slow, we need to lock the
            // dispatch of newsletters before and after this query
            $sql = 'UPDATE s_campaigns_mailings SET locked=? WHERE id=?';
            Shopware()->Db()->query($sql, [
                    date('Y-m-d H:i:s', time() + 15 * 60),
                    $mailing['id'],
                ]
            );

            echo "Current mail: '" . $subjectCurrentMailing . "'\n";
            echo count($emails) . " Recipients fetched\n";
        } else {
            $mailing = $this->initMailing($mailingID);
            $emails = [$this->Request()->getParam('testmail')];
        }

        $template = $this->initTemplate($mailing);

        $from = $template->fetch('string:' . $mailing['sendermail'], $template);
        $fromName = $template->fetch('string:' . $mailing['sendername'], $template);

        /** @var \Enlight_Components_Mail $mail */
        $mail = clone Shopware()->Container()->get('mail');
        $mail->setFrom($from, $fromName);

        $counter = 0;
        foreach ($emails as $email) {
            $user = $this->getMailingUserByEmail($email);
            $template->assign('sUser', $user, true);
            $hash = $this->createHash((int) $user['mailaddressID'], (int) $mailing['id']);
            $template->assign('sCampaignHash', $hash, true);
            $template->assign('sRecommendations', $this->getMailingSuggest($mailing['id'], $user['userID']), true);

            /** @var array $voucher */
            $voucher = $template->getTemplateVars('sVoucher');
            if (!empty($voucher['id'])) {
                $voucher['code'] = $this->getVoucherCode($voucher['id']);
                $template->assign('sVoucher', $voucher, true);
            }

            if (empty($mailing['plaintext'])) {
                $body = $template->fetch('newsletter/index/' . $mailing['template'], $template);
            }
            $bodyText = $template->fetch('newsletter/alt/' . $mailing['template'], $template);

            if (!empty($body)) {
                $body = $this->trackFilter($body, $mailing['id']);
                $mail->setBodyHtml($body);
            }
            if (!empty($bodyText)) {
                $bodyText = $this->altFilter($bodyText);
                $mail->setBodyText($bodyText);
            }

            $subject = $template->fetch('string:' . $mailing['subject'], $template);

            $mail->clearSubject();
            $mail->setSubject($subject);
            $mail->clearRecipients();
            $mail->addTo($user['email']);
            $mail->setAssociation(NewsletterMailFilter::NEWSLETTER_MAIL, true);
            $validator = $this->container->get('validator.email');
            if (!$validator->isValid($user['email'])) {
                echo "Skipped invalid email\n";
            // SW-4526
                // Don't `continue` with next iteration without setting user's lastmailing
                // else the mailing.status will never be set to 2
                // and sending the mail will block
            } else {
                try {
                    $mail->send();
                    ++$counter;
                } catch (Exception $e) {
                    echo $e->getMessage() . "\n";
                }
            }

            if (empty($mailingID)) {
                $sql = 'UPDATE s_campaigns_mailaddresses SET lastmailing=? WHERE email=?';
                Shopware()->Db()->query($sql, [$mailing['id'], $user['email']]);
            }
        }
        echo $counter . ' out of ' . count($emails) . ' Mails sent successfully';

        // In cronmode: Once we are done, release the lock (by setting it 15 seconds to future)
        if (empty($mailingID)) {
            $sql = 'UPDATE s_campaigns_mailings SET locked=? WHERE id=?';
            Shopware()->Db()->query($sql, [
                    date('Y-m-d H:i:s', time() + 15),
                    $mailing['id'],
                ]
            );
        }
    }

    /**
     * Cron action method
     *
     * Sends the newsletter emails as a cronjob.
     */
    public function cronAction()
    {
        /** @var Shopware_Plugins_Core_Cron_Bootstrap|null $cronBootstrap */
        $cronBootstrap = $this->getPluginBootstrap('Cron');
        if ($cronBootstrap && !$cronBootstrap->authorizeCronAction($this->Request())) {
            $this->Response()
                ->clearHeaders()
                ->setStatusCode(Response::HTTP_FORBIDDEN)
                ->appendBody('Forbidden');

            return;
        }

        $this->Response()->headers->set('content-type', 'text/plain');
        $this->mailAction();
    }

    /**
     * Log action method
     *
     * Logs read the email newsletter.
     */
    public function logAction()
    {
        $mailing = (int) $this->Request()->getParam('mailing');
        $mail = (int) $this->Request()->getParam('mailaddress');

        if (empty($mailing) || empty($mail)) {
            return;
        }

        $sql = 'SELECT email FROM s_campaigns_mailaddresses WHERE id=?';
        $email = Shopware()->Db()->fetchOne($sql, [$mail]);

        if (empty($email)) {
            return;
        }

        $sql = '
            UPDATE s_campaigns_mailaddresses
            SET lastread=lastmailing
            WHERE lastmailing=?
            AND email=?
        ';
        $stm = Shopware()->Db()->query($sql, [$mailing, $email]);

        if ($stm->rowCount()) {
            $sql = 'UPDATE s_campaigns_mailings SET `read`=`read`+1 WHERE id=?';
            Shopware()->Db()->query($sql, [$mailing]);
        }

        $this->Response()->headers->set('content-type', 'image/gif');
        $bild = imagecreate(1, 1);
        $white = imagecolorallocate($bild, 255, 255, 255);
        imagefill($bild, 1, 1, $white);
        imagegif($bild);
        imagedestroy($bild);
    }

    /**
     * @deprecated in 5.6, will be private in 5.8
     *
     * Init mailing method
     *
     * Initializes the mailing using the mailing id.
     *
     * @param int|null $mailingID
     *
     * @return array|null
     */
    public function initMailing($mailingID = null)
    {
        trigger_error(sprintf('%s:%s is deprecated since Shopware 5.6 and will be private with 5.8.', __CLASS__, __METHOD__), E_USER_DEPRECATED);

        $mailing = $this->getMailing($mailingID);
        if (empty($mailing)) {
            return null;
        }
        $repository = Shopware()->Models()->getRepository('Shopware\Models\Shop\Shop');
        $shop = $repository->getActiveById($mailing['languageID']);

        $this->Request()
            ->setHttpHost($shop->getHost())
            ->setBasePath($shop->getBasePath())
            ->setBaseUrl($shop->getBasePath());

        $this->get('shopware.components.shop_registration_service')->registerShop($shop);

        Shopware()->Session()->sUserGroup = $mailing['customergroup'];
        $sql = 'SELECT * FROM s_core_customergroups WHERE groupkey=?';
        Shopware()->Session()->sUserGroupData = Shopware()->Db()->fetchRow($sql, [$mailing['customergroup']]);

        Shopware()->Container()->get('router')->setGlobalParam('module', 'frontend');
        Shopware()->Config()->DontAttachSession = true;
        Shopware()->Container()->get('shopware_storefront.context_service')->initializeShopContext();

        return $mailing;
    }

    /**
     * @deprecated in 5.6, will be private in 5.8
     *
     * Init template method
     *
     * Initializes the template using the mailing data.
     *
     * @param array $mailing
     *
     * @return Enlight_Template_Manager
     */
    public function initTemplate($mailing)
    {
        trigger_error(sprintf('%s:%s is deprecated since Shopware 5.6 and will be private with 5.8.', __CLASS__, __METHOD__), E_USER_DEPRECATED);

        $template = clone Shopware()->Template();
        $shop = Shopware()->Shop();
        $inheritance = Shopware()->Container()->get('theme_inheritance');

        $config = $inheritance->buildConfig(
            $shop->getTemplate(),
            $shop,
            false
        );

        $user = $this->getMailingUserByEmail(Shopware()->Config()->Mail);
        $template->assign('sUser', $user, true);
        $hash = $this->createHash((int) $user['mailaddressID'], (int) $mailing['id']);
        $template->assign('sCampaignHash', $hash, true);
        $template->assign('sRecommendations', $this->getMailingSuggest($mailing['id'], $user['id']), true);
        $template->assign('sVoucher', $this->getMailingVoucher($mailing['id']), true);
        $template->assign('sCampaign', $this->getMailingDetails($mailing['id']), true);
        $template->assign('sConfig', Shopware()->Config());
        $template->assign('sBasefile', Shopware()->Config()->BaseFile);
        $template->assign('theme', $config);

        $templatePath = 'newsletter/index/' . $mailing['template'];

        if (!empty($mailing['plaintext'])) {
            $templatePath = 'newsletter/alt/' . $mailing['template'];
        }

        if (!$template->isCached($templatePath)) {
            $template->assign('sMailing', $mailing);
            $template->assign('sStart', ($shop->getSecure() ? 'https://' : 'http://') . $shop->getHost() . $shop->getBaseUrl());
            $template->assign('sUserGroup', Shopware()->System()->sUSERGROUP);
            $template->assign('sUserGroupData', Shopware()->System()->sUSERGROUPDATA);
            $template->assign('sMainCategories', Shopware()->Modules()->Categories()->sGetMainCategories());
        }

        return $template;
    }

    /**
     * @deprecated in 5.6, will be private in 5.8
     *
     * Returns mailing data using the mailing id.
     *
     * @param int $id
     *
     * @return array
     */
    public function getMailing($id = null)
    {
        trigger_error(sprintf('%s:%s is deprecated since Shopware 5.6 and will be private with 5.8.', __CLASS__, __METHOD__), E_USER_DEPRECATED);

        if (!empty($id)) {
            $where = Shopware()->Db()->quoteInto('cm.id=?', $id);
        } else {
            $where = 'cm.status=1 ';
        }
        $sql = 'SELECT cm.*, ct.path as template
        FROM s_campaigns_mailings cm, s_campaigns_templates ct
        WHERE ct.id=cm.templateID
        AND ' . $where . '
        AND (`timed_delivery` <= NOW()
        OR `timed_delivery` IS NULL)';

        return Shopware()->Db()->fetchRow($sql);
    }

    /**
     * @deprecated in 5.6, will be private in 5.8
     *
     * Returns mailing details by mailing id.
     *
     * @param int $id
     *
     * @return array
     */
    public function getMailingDetails($id)
    {
        trigger_error(sprintf('%s:%s is deprecated since Shopware 5.6 and will be private with 5.8.', __CLASS__, __METHOD__), E_USER_DEPRECATED);

        $details = Shopware()->Modules()->Marketing()->sMailCampaignsGetDetail((int) $id);

        foreach ($details['containers'] as $key => $container) {
            if ($container['type'] === 'ctVoucher') {
                if (!empty($container['value'])) {
                    $details['voucher'] = $container['value'];
                }
                $details['containers'][$key]['type'] = 'ctText';
            }
            if ($container['type'] === 'ctSuggest') {
                $details['suggest'] = true;
            }
        }

        return $details;
    }

    /**
     * Returns the article suggests based on the customer.
     *
     * @param int $id
     * @param int $userID
     *
     * @return array
     */
    public function getMailingSuggest($id, $userID)
    {
        return [];
    }

    /**
     * @deprecated in 5.6, will be private in 5.8
     *
     * Returns mailing voucher using the voucher id.
     *
     * @param int $id
     *
     * @return array|bool
     */
    public function getMailingVoucher($id)
    {
        trigger_error(sprintf('%s:%s is deprecated since Shopware 5.6 and will be private with 5.8.', __CLASS__, __METHOD__), E_USER_DEPRECATED);

        $sql = 'SELECT value FROM s_campaigns_containers WHERE type=? AND promotionID=?';
        $voucherID = Shopware()->Db()->fetchOne($sql, ['ctVoucher', $id]);
        if (empty($voucherID)) {
            return false;
        }
        $sql = "
            SELECT ev.*, 'VOUCHER123' as code
            FROM s_emarketing_vouchers ev
            WHERE  ev.modus = 1 AND (ev.valid_to >= CURDATE() OR ev.valid_to IS NULL)
            AND (ev.valid_from <= CURDATE() OR ev.valid_from IS NULL)
            AND ev.id=?
        ";

        return Shopware()->Db()->fetchRow($sql, [$voucherID]);
    }

    /**
     * @deprecated in 5.6, will be private in 5.8
     *
     * Returns the mailing email addresses based on the mailing id.
     *
     * @param int $id
     *
     * @return array|bool
     */
    public function getMailingEmails($id)
    {
        trigger_error(sprintf('%s:%s is deprecated since Shopware 5.6 and will be private with 5.8.', __CLASS__, __METHOD__), E_USER_DEPRECATED);

        $sql = 'SELECT `groups`, languageID FROM s_campaigns_mailings WHERE id=?';
        $mailing = Shopware()->Db()->fetchRow($sql, [$id]);

        if (empty($mailing)) {
            return false;
        }

        $customerGroups = null;
        $recipientGroups = null;
        $mailing['groups'] = unserialize($mailing['groups'], ['allowed_classes' => false]);

        // The first element holds the selected customer groups for the current newsletter
        foreach ($mailing['groups'][0] as $customerGroupKey => $customerGroupValue) {
            $customerGroups[] = Shopware()->Db()->quoteInto('su.customergroup=?', $customerGroupKey);
        }
        $customerGroups = implode(' OR ', $customerGroups);

        // The second element holds the selected *newsletter* groups for the current newsletter
        foreach ($mailing['groups'][1] as $customerGroupKey => $customerGroupValue) {
            $recipientGroups[] = Shopware()->Db()->quoteInto('sc.groupID=?', $customerGroupKey);
        }
        $recipientGroups = implode(' OR ', $recipientGroups);

        // If no customer/recipient group was selected, force the condition to be false
        if (empty($recipientGroups)) {
            $recipientGroups = '1=2';
        }
        if (empty($customerGroups)) {
            $customerGroups = '1=2';
        }

        $limit = !empty(Shopware()->Config()->MailCampaignsPerCall) ? (int) Shopware()->Config()->MailCampaignsPerCall : 1000;
        $limit = max(1, $limit);

        $customerStreams = '1=2';
        $ids = array_keys($mailing['groups'][2]);
        if (!empty($ids)) {
            $ids = array_map(function ($id) {
                return (int) $id;
            }, $ids);
            $customerStreams = 'mapping.stream_id IN (' . implode(',', $ids) . ')';
        }

        /**
         * Get mails belonging to selected customergroups of the selected subshop
         * -OR- belonging to the selected newsletter groups
         */
        $sql = "
            SELECT sc.email

            FROM s_campaigns_mailaddresses sc

            LEFT JOIN s_user su
            ON sc.email=su.email
            
            LEFT JOIN s_customer_streams_mapping mapping
              ON mapping.customer_id = su.id

            WHERE sc.lastmailing != ?
            AND (
              (su.language = ? AND ($customerGroups))
              OR ($recipientGroups)
              OR ($customerStreams)
            )
            GROUP BY sc.email
        ";
        $sql = Shopware()->Db()->limit($sql, $limit);

        return Shopware()->Db()->fetchCol($sql, [$id, $mailing['languageID']]);
    }

    /**
     * @deprecated in 5.6, will be private in 5.8
     *
     * Returns a new voucher code using the voucher id.
     *
     * @param int $voucherID
     *
     * @return string|false
     */
    public function getVoucherCode($voucherID)
    {
        trigger_error(sprintf('%s:%s is deprecated since Shopware 5.6 and will be private with 5.8.', __CLASS__, __METHOD__), E_USER_DEPRECATED);

        $sql = '
            SELECT id, code
            FROM s_emarketing_voucher_codes evc
            WHERE evc.voucherID=? AND evc.userID IS NULL AND evc.cashed=0
            LIMIT 1
            FOR UPDATE
        ';
        $code = Shopware()->Db()->fetchRow($sql, [$voucherID]);
        if (empty($code)) {
            return false;
        }
        $sql = 'UPDATE `s_emarketing_voucher_codes` SET `cashed`=2 WHERE `id`=?';
        Shopware()->Db()->query($sql, [$code['id']]);

        return $code['code'];
    }

    /**
     * @deprecated in 5.6, will be private in 5.8
     *
     * Returns mailing user data by email.
     *
     * @param string $email
     *
     * @return array
     */
    public function getMailingUserByEmail($email)
    {
        trigger_error(sprintf('%s:%s is deprecated since Shopware 5.6 and will be private with 5.8.', __CLASS__, __METHOD__), E_USER_DEPRECATED);

        $select = '
            cm.email, cm.email as newsletter, cg.name as `group`,
            IFNULL(u.salutation, nd.salutation) as salutation,
            IFNULL(u.title, nd.title) as title,
            IFNULL(u.firstname, nd.firstname) as firstname,
            IFNULL(u.lastname, nd.lastname) as lastname,
            IFNULL(ub.street, nd.street) as street,
            IFNULL(ub.zipcode, nd.zipcode) as zipcode,
            IFNULL(ub.city, nd.city) as city,
            customer,
            lastmailing,
            lastread,
            cm.id as mailaddressID,
            u.id as userID
        ';

        $sql = '
            SELECT ' . $select . '
            FROM s_campaigns_mailaddresses cm
            LEFT JOIN s_campaigns_groups cg
            ON cg.id=cm.groupID
            LEFT JOIN s_campaigns_maildata nd
            ON nd.email=cm.email
            LEFT JOIN s_user u
            ON u.email=cm.email
            AND u.accountmode=0
            LEFT JOIN s_user_addresses ub
            ON u.default_billing_address_id=ub.id
            AND u.id = ub.user_id
            WHERE cm.email=?
        ';
        $user = Shopware()->Db()->fetchRow($sql, [$email]);

        if (empty($user)) {
            $sql = '
                SELECT ' . $select . '
                FROM s_campaigns_mailaddresses cm
                LEFT JOIN s_campaigns_groups cg
                ON cg.id=cm.groupID
                LEFT JOIN s_campaigns_maildata nd
                ON nd.email=cm.email
                LEFT JOIN s_user u
                ON u.email=cm.email
                AND u.accountmode=0
                LEFT JOIN s_user_addresses ub
                ON u.default_billing_address_id=ub.id
                AND u.id = ub.user_id
                LIMIT 1
            ';
            $user = Shopware()->Db()->fetchRow($sql);
            $user['email'] = $user['newsletter'] = $email;
        }

        return $user;
    }

    /**
     * @deprecated in 5.6, will be private in 5.8
     *
     * Pre filter the old template source.
     *
     * @param string $source
     *
     * @return string
     */
    public function preFilter($source)
    {
        trigger_error(sprintf('%s:%s is deprecated since Shopware 5.6 and will be private with 5.8.', __CLASS__, __METHOD__), E_USER_DEPRECATED);

        $source = str_replace('<suggestions></suggestions>', '{include file="suggest`$sMailing.template`"}', $source);
        $source = str_replace('<weblog></weblog>', '<img src="{url module=backend controller=newsletter action=log mailing=$sMailing.id mailaddress=$sUser.mailaddressID fullPath}" style="width:1px;height:1px">', $source);
        $source = str_replace('@suggestions', '{include file="alt/suggest`$sMailing.template`"}', $source);
        $source = str_replace('http://intranet.shopware2.de/magneto/templates/0/de/media/', '../../media/', $source);
        $source = preg_replace('#{eval var=($sCampaignContainer.data.link)}#Umsi', '{include file="string:`$1`"}', $source);

        return $source;
    }

    /**
     * @deprecated in 5.6, will be private in 5.8
     *
     * Replaces the relative pictures links with absolute links.
     *
     * @param string $source
     *
     * @return string
     */
    public function outputFilter($source)
    {
        trigger_error(sprintf('%s:%s is deprecated since Shopware 5.6 and will be private with 5.8.', __CLASS__, __METHOD__), E_USER_DEPRECATED);

        $source = preg_replace('#(src|background)="([^:"./][^:"]+)"#Umsi', '$1="../../campaigns/$2"', $source);
        $callback = [Shopware()->Plugins()->Core()->PostFilter(), 'rewriteSrc'];

        return preg_replace_callback('#<(link|img|script|input|a|form|iframe|td)[^<>]*(href|src|action|background)="([^"]*)".*>#Umsi', $callback, $source);
    }

    /**
     * @deprecated in 5.6, will be private in 5.8
     *
     * Removes the unneeded metadata in the alternative view.
     *
     * @param string $source
     *
     * @return string
     */
    public function altFilter($source)
    {
        trigger_error(sprintf('%s:%s is deprecated since Shopware 5.6 and will be private with 5.8.', __CLASS__, __METHOD__), E_USER_DEPRECATED);

        $source = preg_replace('#<a.+href="(.*)".*>#Umsi', '$1', $source);
        $source = str_replace(['<br />', '</p>', '&nbsp;'], ["\n", "\n", ' '], $source);
        $source = trim(strip_tags(preg_replace('/<(head|title|style|script)[^>]*>.*?<\/\\1>/s', '', $source)));

        return html_entity_decode($source);
    }

    /**
     * @deprecated in 5.6, will be private in 5.8
     *
     * Adds a parameter to the internal tracking urls.
     *
     * @param string $source
     * @param int    $mailingID
     *
     * @return string
     */
    public function trackFilter($source, $mailingID)
    {
        trigger_error(sprintf('%s:%s is deprecated since Shopware 5.6 and will be private with 5.8.', __CLASS__, __METHOD__), E_USER_DEPRECATED);

        $track = 'sPartner=sCampaign' . (int) $mailingID;
        $host = preg_quote(Shopware()->Config()->BasePath, '#');
        $pattern = '#href="(https?://' . $host . '[^<]*[?][^<]+)"#Umsi';
        $source = preg_replace($pattern, 'href="$1&' . $track . '"', $source);
        $pattern = '#href="(https?://' . $host . '[^?<]*)"#Umsi';

        return preg_replace($pattern, 'href="$1?' . $track . '"', $source);
    }

    /**
     * @deprecated in 5.6, will be private in 5.8
     *
     * Creates a hash based on the passed data.
     *
     * @return string
     */
    public function createHash()
    {
        trigger_error(sprintf('%s:%s is deprecated since Shopware 5.6 and will be private with 5.8.', __CLASS__, __METHOD__), E_USER_DEPRECATED);

        // todo@all Create new method to get same secret hashes for values
        $license = '';
        $parts = func_get_args();
        $parts[] = $license;

        return md5(implode('|', $parts));
    }

    /**
     * Returns plugin bootstrap if plugin exits, is enabled, and active.
     * Otherwise return null.
     *
     * @param string $pluginName
     *
     * @return Enlight_Plugin_Bootstrap|null
     */
    private function getPluginBootstrap($pluginName)
    {
        /** @var Shopware_Components_Plugin_Namespace $namespace */
        $namespace = Shopware()->Plugins()->Core();
        $pluginBootstrap = $namespace->get($pluginName);

        if (!$pluginBootstrap instanceof Enlight_Plugin_Bootstrap) {
            return null;
        }

        /** @var \Shopware\Models\Plugin\Plugin|null $plugin */
        $plugin = Shopware()->Models()->find(\Shopware\Models\Plugin\Plugin::class, $pluginBootstrap->getId());
        if (!$plugin) {
            return null;
        }

        if (!$plugin->getActive() || !$plugin->getInstalled()) {
            return null;
        }

        return $pluginBootstrap;
    }
}
