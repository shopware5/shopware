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

use Shopware\Models\Mail\Mail;
use Shopware\Models\Mail\Attachment;

/**
 * Backend Controller for the mail backend module
 */
class Shopware_Controllers_Backend_Mail extends Shopware_Controllers_Backend_ExtJs
{
    /**
     * @var \Shopware\Models\Mail\Repository
     */
    protected $repository = null;

    /**
     * Internal helper function to get access to the mail repository.
     * @return null|Shopware\Models\Mail\Repository
     */
    private function getRepository()
    {
        if ($this->repository === null) {
            $this->repository = Shopware()->Models()->getRepository('Shopware\Models\Mail\Mail');
        }
        return $this->repository;
    }

    /**
     * Method to define acl dependencies in backend controllers
     */
    protected function initAcl()
    {
        $this->addAclPermission('index', 'update');

        $this->addAclPermission('index', 'read');
        $this->addAclPermission('getMails', 'read');
        $this->addAclPermission('createMail', 'create');
        $this->addAclPermission('removeMail', 'delete');
        $this->addAclPermission('updateMail', 'update');
        $this->addAclPermission('copyMail', 'create');

        $this->addAclPermission('getAttachments', 'read');
        $this->addAclPermission('addAttachment', 'update');
        $this->addAclPermission('removeAttachment', 'update');
        $this->addAclPermission('updateAttachment', 'update');
    }

    /**
     * Returns available mails
     */
    public function getMailsAction()
    {
        /** @var $namespace Enlight_Components_Snippet_Namespace */
        $snippet = Shopware()->Snippets()->getNamespace('backend/mail/view/navigation');

        // if id is provided return a single mail instead of a collection
        $id = $this->Request()->getParam('id');
        if (!empty($id) && is_numeric($id)) {
            $this->getSingleMail($id);
            return;
        }

        $mails = $this->getRepository()->findAll();

        $nodes = array();

        $statusNodes = array(
            'name' => $snippet->get('mails_status', 'Status emails'),
            'leaf' => false,
            'data' => array(),
        );

        $paymentNodes = array(
            'name'     => $snippet->get('mails_status_payment', 'Payment status'),
            'leaf'     => false,
            'data'     => array()
        );

        $orderNodes = array(
            'name' => $snippet->get('mails_status_order', 'Order status'),
            'leaf' => false,
            'data' => array()
        );

        $systemNodes = array(
            'name' => $snippet->get('mails_system', 'System emails'),
            'leaf' => false,
            'data' => array()
        );

        $userNodes = array(
            'name' => $snippet->get('mails_user', 'User emails'),
            'leaf' => false,
            'data' => array()
        );

        /* @var $mail Mail */
        foreach ($mails as $mail) {
            $node = array(
                'leaf' => true,
                'name' => $mail->getName(),
                'id'   => $mail->getId(),
                'iconCls' => 'sprite-mail',
            );

            if ($mail->isOrderStateMail()) {
                $orderStatus = $mail->getStatus();
                $node['name'] = $this->get('snippets')
                        ->getNamespace('backend/static/order_status')
                        ->get($orderStatus->getName(), $orderStatus->getDescription());
                $orderNodes['data'][] = $node;
            } elseif ($mail->isPaymentStateMail()) {
                $paymentStatus = $mail->getStatus();
                $node['name'] = $this->get('snippets')
                    ->getNamespace('backend/static/payment_status')
                    ->get($paymentStatus->getName(), $paymentStatus->getDescription());
                $paymentNodes['data'][] = $node;
            } elseif ($mail->isSystemMail()) {
                $systemNodes['data'][] = $node;
            } elseif ($mail->isUserMail()) {
                $node['checked'] = false;
                $userNodes['data'][] = $node;
            }
        }

        $statusNodes['data'][] = $paymentNodes;
        $statusNodes['data'][] = $orderNodes;

        $nodes[] = $statusNodes;
        $nodes[] = $systemNodes;
        $nodes[] = $userNodes;

        $this->View()->assign(array('success' => true, 'data' => $nodes));
    }

    /**
     * Gets a single mail
     *
     * @param $id
     * @return void
     */
    protected function getSingleMail($id)
    {
        $data = $this->getMail($id);
        $mail = $data['mail'];
        $data = $data['data'];

        /**@var $mail \Shopware\Models\Mail\Mail*/
        if (!$mail instanceof \Shopware\Models\Mail\Mail) {
            $this->View()->assign(array('success' => false, 'message' => 'Mail not found'));
            return;
        }

        /** @var $shop \Shopware\Models\Shop\Shop **/
        $shop = Shopware()->Models()->getRepository('Shopware\Models\Shop\Shop')->getActiveDefault();
        $shop->registerResources(Shopware()->Bootstrap());

        $defaultContext = array(
            'sShop'    => Shopware()->Config()->get('ShopName'),
            'sShopURL' => 'http://'. $shop->getHost() . $shop->getBasePath(),
            'sConfig'  => Shopware()->Config(),
        );

        $context = $mail->getContext();
        if (empty($context)) {
            $context = array();
        }
        $data['contextPath'] = $mail->arrayGetPath(array_merge($defaultContext, $context));

        $this->View()->assign(array('success' => true, 'data' => $data, 'total' => 1));
    }

    /**
     * Returns an array with the converted mail data and a mail object for the passed mail id.
     * @param $id
     * @return array
     */
    private function getMail($id)
    {
        $query = $this->getRepository()->getMailQuery($id);
        $mail = $query->getOneOrNullResult(\Doctrine\ORM\AbstractQuery::HYDRATE_OBJECT);

        if (!$mail instanceof \Shopware\Models\Mail\Mail) {
            $this->View()->assign(array('success' => false, 'message' => 'Mail not found'));
            return false;
        }

        if ($mail->isOrderStateMail()) {
            $type = 'orderState';
        } elseif ($mail->isPaymentStateMail()) {
            $type = 'paymentState';
        } elseif ($mail->isSystemMail()) {
            $type = 'systemMail';
        } else {
            $type = 'userMail';
        }

        $data = $query->getOneOrNullResult(\Doctrine\ORM\AbstractQuery::HYDRATE_ARRAY);
        $data['type'] = $type;
        return array(
            'data' => $data,
            'mail' => $mail
        );
    }

    /**
     * Creates new mail
     */
    public function createMailAction()
    {
        $params = $this->Request()->getParams();

        $mail = new Mail();
        $params['attribute'] = $params['attribute'][0];
        $params['dirty'] = 1;
        $mail->fromArray($params);

        try {
            Shopware()->Models()->persist($mail);
            Shopware()->Models()->flush();
        } catch (Exception $e) {
            $this->View()->assign(array('success' => false, 'message' => $e->getMessage()));
            return;
        }

        $data = $this->getMail($mail->getId());
        $data = $data['data'];

        $this->View()->assign(array('success' => true, 'data' => $data));
    }

    /**
     * Updates mail
     *
     * @return bool
     */
    public function updateMailAction()
    {
        if (!($id = $this->Request()->getParam('id'))) {
            $this->View()->assign(array('success' => false, 'message' => 'mail id found'));
            return;
        }

        /* @var $mail Mail */
        $mail = Shopware()->Models()->getRepository('Shopware\Models\Mail\Mail')->find($id);
        if (!$mail) {
            $this->View()->assign(array('success' => false, 'message' => 'Mail not found'));
            return;
        }

        $params = $this->Request()->getParams();
        $params['attribute'] = $params['attribute'][0];
        $params['dirty'] = 1;

        $mail->fromArray($params);

        try {
            Shopware()->Models()->persist($mail);
            Shopware()->Models()->flush();
        } catch (Exception $e) {
            $this->View()->assign(array('success' => false, 'message' => $e->getMessage()));
            return;
        }

        $data = $this->getMail($mail->getId());
        $data = $data['data'];

        $this->View()->assign(array('success' => true, 'data' => $data));
    }

    /**
     * Remove mail action
     */
    public function removeMailAction()
    {
        if (!($id = $this->Request()->getParam('id'))) {
            $this->View()->assign(array('success' => false, 'message' => 'mail id found'));
            return;
        }

        /* @var $mail Mail */
        $mail = $this->getRepository()->find($id);
        if (!$mail) {
            $this->View()->assign(array('success' => false, 'message' => 'Mail not found'));
            return;
        }

        if (!$mail->isUserMail()) {
            $this->View()->assign(array('success' => false, 'message' => 'Only Usermails may be removed'));
            return;
        }

        Shopware()->Models()->remove($mail);
        Shopware()->Models()->flush();

        $this->View()->assign(array('success' => true));
    }

    /**
     * Copy mail action
     */
    public function copyMailAction()
    {
        if (!($id = $this->Request()->getParam('id'))) {
            $this->View()->assign(array('success' => false, 'message' => 'mail id found'));
            return;
        }

        /* @var $mail Mail */
        $mail = $this->getRepository()->find($id);
        if (!$mail) {
            $this->View()->assign(array('success' => false, 'message' => 'Mail not found'));
            return;
        }

        if (!$mail->isUserMail()) {
            $this->View()->assign(array('success' => false, 'message' => 'Only Usermails may be cloned'));
            return;
        }

        $clonedMail = clone $mail;
        $clonedMail->setName('Copy of ' . $mail->getName());

        Shopware()->Models()->persist($clonedMail);
        Shopware()->Models()->flush();

        $this->View()->assign(array('success' => true));
    }

    /**
     * Validate name action
     *
     * Validates whether or not the provided value exists in the database
     *
     * @return void|string
     */
    public function validateNameAction()
    {
        $this->Front()->Plugins()->Json()->setRenderer(false);

        if (!($name = $this->Request()->getParam('value'))) {
            return;
        }

        $id = $this->Request()->getParam('param', false);

        $mail = $this->getRepository()
                     ->getValidateNameQuery($name, $id)
                     ->getResult(\Doctrine\ORM\AbstractQuery::HYDRATE_OBJECT);

        if (!empty($mail)) {
            $this->View()->assign(array('success' => false, 'message' => 'Mail found found'));
            return;
        }

        echo 'true';
        return;
    }

    /**
     * Verify smarty Action
     *
     * Validates if the given value contains valid smartystring
     *
     * @return void
     */
    public function sendTestmailAction()
    {
        if (!($id = $this->Request()->getParam('id'))) {
            $this->View()->assign(array('success' => false, 'message' => 'no mail id found'));
            return;
        }

        /* @var $mail Mail */
        $mail = $this->getRepository()->find($id);
        if (!$mail) {
            $this->View()->assign(array('success' => false, 'message' => 'Mail not found'));
            return;
        }

        if (!($value = $this->Request()->getParam('value'))) {
            $this->View()->assign(array('success' => false, 'message' => 'Value not found'));
        }

        $recipient = Shopware()->Config()->get('mail');

        $shop = Shopware()->Models()->getRepository('Shopware\Models\Shop\Shop')->getActiveDefault();
        $shop->registerResources(Shopware()->Bootstrap());

        $defaultContext = array(
            'sShop'    => Shopware()->Config()->get('ShopName'),
            'sShopURL' => 'http://' . $shop->getHost() . $shop->getBasePath(),
            'sConfig'  => Shopware()->Config(),
        );

        try {
            $templateMail = Shopware()->TemplateMail()->createMail($mail, array_merge($defaultContext, $mail->getContext()), $shop);
            $templateMail->addTo($recipient);
            $templateMail->send();
        } catch (\Exception $e) {
            $this->View()->assign(array('success' => false, 'message' => $e->getMessage()));
            return;
        }

        $this->View()->assign(array('success' => true));
    }

    /**
     * Verify smarty Action
     *
     * Validates if the given value contains valid smartystring
     *
     * @return void
     */
    public function verifySmartyAction()
    {
        if (!($id = $this->Request()->getParam('id'))) {
            $this->View()->assign(array('success' => false, 'message' => 'mail id found'));
            return;
        }

        /* @var $mail Mail */
        $mail = $this->getRepository()->find($id);
        if (!$mail) {
            $this->View()->assign(array('success' => false, 'message' => 'Mail not found'));
            return;
        }

        if (!($value = $this->Request()->getParam('value'))) {
            $this->View()->assign(array('success' => false, 'message' => 'Value not found'));
        }

        $compiler = new Shopware_Components_StringCompiler($this->View()->Engine());

        $shop = Shopware()->Models()->getRepository('Shopware\Models\Shop\Shop')->getActiveDefault();
        $shop->registerResources(Shopware()->Bootstrap());

        $defaultContext = array(
            'sShop'    => Shopware()->Config()->get('ShopName'),
            'sShopURL' => 'http://' . $shop->getHost() . $shop->getBasePath(),
            'sConfig'  => Shopware()->Config(),
        );

        $compiler->setContext(array_merge($defaultContext, $mail->getContext()));

        try {
            $template = $compiler->compileString($value);
        } catch (Enlight_Exception $e) {
            $this->View()->assign(array('success' => false, 'message' => $e->getMessage()));
            return;
        }

        $this->View()->assign(array('success' => true, 'message' => $template));
    }

    /**
     * Adds a attachment to given mail
     */
    public function addAttachmentAction()
    {
        if (!($mailId = $this->Request()->getParam('mailId'))) {
            $this->View()->assign(array('success' => false, 'message' => 'MailId not found'));
            return;
        }

        if (!($mediaId = $this->Request()->getParam('mediaId'))) {
            $this->View()->assign(array('success' => false, 'message' => 'MediaId not found'));
            return;
        }

        /* @var $mail \Shopware\Models\Mail\Mail */
        $mail = $this->getRepository()->find($mailId);
        if (!$mail) {
            $this->View()->assign(array('success' => false, 'message' => 'Mail not found'));
            return;
        }

        /* @var $media \Shopware\Models\Media\Media */
        $media = Shopware()->Models()->getRepository('\Shopware\Models\Media\Media')->find($mediaId);
        if (!$media) {
            $this->View()->assign(array('success' => false, 'message' => 'Media not found'));
            return;
        }

        $attachment = new Attachment($mail, $media);

        try {
            Shopware()->Models()->persist($attachment);
            Shopware()->Models()->flush();
        } catch (Exception $e) {
            $this->View()->assign(array('success' => false, 'message' => $e->getMessage()));
            return;
        }

        $data = Shopware()->Models()->toArray($attachment);

        $this->View()->assign(array('success' => true, 'data' => $data));
    }

    /**
     * Remove attachment action
     */
    public function removeAttachmentAction()
    {
        if (!($attachmentId = $this->Request()->getParam('id'))) {
            $this->View()->assign(array('success' => false, 'message' => 'Id not found'));
        }

        /* @var $attachment Attachment */
        $attachment = Shopware()->Models()->getRepository('\Shopware\Models\Mail\Attachment')->find($attachmentId);
        if (!$attachment) {
            $this->View()->assign(array('success' => false, 'message' => 'Attachment not found'));
            return;
        }

        try {
            Shopware()->Models()->remove($attachment);
            Shopware()->Models()->flush();
        } catch (Exception $e) {
            $this->View()->assign(array('success' => false, 'message' => $e->getMessage()));
            return;
        }

        $this->View()->assign(array('success' => true));
    }

    /**
     * Update attachment action
     */
    public function updateAttachmentAction()
    {
        if (!($attachmentId = $this->Request()->getParam('id'))) {
            $this->View()->assign(array('success' => false, 'message' => 'Id not found'));
            return;
        }

        if (!($shopId = $this->Request()->getParam('destinationShopId'))) {
            $this->View()->assign(array('success' => false, 'message' => 'destinationShopId not found'));
            return;
        }

        if ($shopId == 0) {
            $shop = null;
        } else {
            /* @var $shop \Shopware\Models\Shop\Shop */
            $shop = Shopware()->Models()->getRepository('\Shopware\Models\Shop\Shop')->find($shopId);
            if (!$shop) {
                $this->View()->assign(array('success' => false, 'message' => 'Shop not found'));
                return;
            }
        }

        /* @var $attachment Attachment */
        $attachment = Shopware()->Models()->getRepository('\Shopware\Models\Mail\Attachment')->find($attachmentId);
        if (!$attachment) {
            $this->View()->assign(array('success' => false, 'message' => 'Attachment not found'));
            return;
        }

        $attachment->setShop($shop);

        try {
            Shopware()->Models()->flush();
        } catch (Exception $e) {
            $this->View()->assign(array('success' => false, 'message' => $e->getMessage()));
            return;
        }

        $this->View()->assign(array('success' => true));
    }

    /**
     * Get attachment action
     */
    public function getAttachmentsAction()
    {
        $mailId = $this->Request()->getParam('mailId', false);
        $attachments = array();

        if ($mailId) {
            /* @var $mail \Shopware\Models\Mail\Mail */
            $mail = $this->getRepository()->find($mailId);
            if ($mail) {
                $attachments = $mail->getAttachments();
            }
        }

        $nodes = array();

        /** @var $shop \Shopware\Models\Shop\Shop */
        $shops = Shopware()->Models()->getRepository('Shopware\Models\Shop\Shop')->findAll();
        foreach ($shops as $shop) {
            $shopNode = array(
                'allowDrag' => false,
                'leaf'      => false,
                'filename'  => $shop->getName(),
                'expanded'  => true,
                'data'      => array(),
                'shopId'    => $shop->getId()
            );

            $childNodes = array();
            foreach ($attachments as $attachment) {
                if ($shop->getId() != $attachment->getShopId()) {
                    continue;
                }

                $childNodes[] = array(
                    'leaf'     => true,
                    'checked'  => false,
                    'id'       => $attachment->getId(),
                    'filename' => $attachment->getFileName(),
                    'size'     => $attachment->getFormattedFileSize(),
                    'shopId'   => $attachment->getShopId(),
                );
            }

            $shopNode['data'] = $childNodes;

            $nodes[] = $shopNode;
        }

        $childNodes = array();
        foreach ($attachments as $attachment) {
            if ($attachment->getShopId() !== null) {
                continue;
            }

            $childNodes[] = array(
                'leaf'     => true,
                'checked'  => false,
                'id'       => $attachment->getId(),
                'filename' => $attachment->getFileName(),
                'size'     => $attachment->getFormattedFileSize(),
            );
        }

        $all = array(
            'allowDrag' => false,
            'leaf'      => false,
            'filename'  => 'Global',
            'expanded'  => true,
            'data'      => $childNodes,
        );

        $nodes[] = $all;

        $this->View()->assign(array('success' => true, 'data' => $nodes));
    }
}
