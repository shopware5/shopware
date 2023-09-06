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

use Doctrine\ORM\AbstractQuery;
use Shopware\Bundle\MailBundle\AutoCompleteResolver;
use Shopware\Bundle\MailBundle\Service\Filter\AdministrativeMailFilter;
use Shopware\Components\Model\ModelManager;
use Shopware\Components\ShopRegistrationServiceInterface;
use Shopware\Models\Document\Document;
use Shopware\Models\Mail\Attachment;
use Shopware\Models\Mail\Mail;
use Shopware\Models\Mail\Repository;
use Shopware\Models\Media\Media;
use Shopware\Models\Shop\Shop;

class Shopware_Controllers_Backend_Mail extends Shopware_Controllers_Backend_ExtJs
{
    /**
     * @var Repository
     */
    protected $repository;

    /**
     * Returns available mails
     */
    public function getMailsAction()
    {
        $snippet = Shopware()->Snippets()->getNamespace('backend/mail/view/navigation');

        // If id is provided return a single mail instead of a collection
        $id = (int) $this->Request()->getParam('id');
        if ($id !== 0) {
            $this->getSingleMail($id);

            return;
        }

        /** @var Mail[] $mails */
        $mails = $this->getRepository()->findAll();

        $nodes = [];

        $statusNodes = [
            'name' => $snippet->get('mails_status', 'Status emails'),
            'leaf' => false,
            'data' => [],
        ];

        $paymentNodes = [
            'name' => $snippet->get('mails_status_payment', 'Payment status'),
            'leaf' => false,
            'data' => [],
        ];

        $orderNodes = [
            'name' => $snippet->get('mails_status_order', 'Order status'),
            'leaf' => false,
            'data' => [],
        ];

        $systemNodes = [
            'name' => $snippet->get('mails_system', 'System emails'),
            'leaf' => false,
            'data' => [],
        ];

        $userNodes = [
            'name' => $snippet->get('mails_user', 'User emails'),
            'leaf' => false,
            'data' => [],
        ];

        $documentNodes = [
            'name' => $snippet->get('mails_documents', 'Document emails'),
            'leaf' => false,
            'data' => [],
        ];

        foreach ($mails as $mail) {
            $node = [
                'leaf' => true,
                'name' => $mail->getName(),
                'id' => $mail->getId(),
                'iconCls' => 'sprite-mail',
            ];

            if ($mail->isOrderStateMail()) {
                $orderStatus = $mail->getStatus();
                if ($orderStatus !== null) {
                    $node['name'] = $this->get('snippets')
                        ->getNamespace('backend/static/order_status')
                        ->get($orderStatus->getName());
                }
                $orderNodes['data'][] = $node;
            } elseif ($mail->isPaymentStateMail()) {
                $paymentStatus = $mail->getStatus();
                if ($paymentStatus !== null) {
                    $node['name'] = $this->get('snippets')
                        ->getNamespace('backend/static/payment_status')
                        ->get($paymentStatus->getName());
                }
                $paymentNodes['data'][] = $node;
            } elseif ($mail->isSystemMail()) {
                $systemNodes['data'][] = $node;
            } elseif ($mail->isUserMail()) {
                $node['checked'] = false;
                $userNodes['data'][] = $node;
            } elseif ($mail->isDocumentMail()) {
                $node['name'] = $this->getFriendlyNameOfDocumentEmail($node['name']);
                $documentNodes['data'][] = $node;
            }
        }

        $statusNodes['data'][] = $paymentNodes;
        $statusNodes['data'][] = $orderNodes;

        $nodes[] = $statusNodes;
        $nodes[] = $systemNodes;
        $nodes[] = $userNodes;
        $nodes[] = $documentNodes;

        $this->View()->assign(['success' => true, 'data' => $nodes]);
    }

    /**
     * Creates new mail
     */
    public function createMailAction()
    {
        $params = $this->Request()->getParams();

        $mail = new Mail();
        $params['dirty'] = 1;
        $mail->fromArray($params);

        try {
            $this->get('models')->persist($mail);
            $this->get('models')->flush();
        } catch (Exception $e) {
            $this->View()->assign(['success' => false, 'message' => $e->getMessage()]);

            return;
        }

        $data = $this->getMail($mail->getId());
        if ($data === false) {
            $this->View()->assign(['success' => false, 'message' => 'Could not get mail data']);

            return;
        }

        $data = $data['data'];

        $this->View()->assign(['success' => true, 'data' => $data]);
    }

    /**
     * Updates mail
     */
    public function updateMailAction()
    {
        $id = (int) $this->Request()->getParam('id');
        if ($id === 0) {
            $this->View()->assign(['success' => false, 'message' => 'mail id not found']);

            return;
        }

        $mail = $this->getRepository()->find($id);
        if (!$mail instanceof Mail) {
            $this->View()->assign(['success' => false, 'message' => 'Mail not found']);

            return;
        }

        $params = $this->Request()->getParams();
        $params['dirty'] = 1;

        $mail->fromArray($params);

        try {
            $this->get('models')->persist($mail);
            $this->get('models')->flush();
        } catch (Exception $e) {
            $this->View()->assign(['success' => false, 'message' => $e->getMessage()]);

            return;
        }

        $data = $this->getMail($mail->getId());
        if ($data === false) {
            $this->View()->assign(['success' => false, 'message' => 'Could not get mail data']);

            return;
        }
        $data = $data['data'];

        $this->View()->assign(['success' => true, 'data' => $data]);
    }

    /**
     * Remove mail action
     */
    public function removeMailAction()
    {
        $id = (int) $this->Request()->getParam('id');
        if ($id === 0) {
            $this->View()->assign(['success' => false, 'message' => 'mail id not found']);

            return;
        }

        $mail = $this->getRepository()->find($id);
        if (!$mail instanceof Mail) {
            $this->View()->assign(['success' => false, 'message' => 'Mail not found']);

            return;
        }

        if (!$mail->isUserMail()) {
            $this->View()->assign(['success' => false, 'message' => 'Only Usermails may be removed']);

            return;
        }

        $this->get('models')->remove($mail);
        $this->get('models')->flush();

        $this->View()->assign(['success' => true]);
    }

    /**
     * Copy mail action
     */
    public function copyMailAction()
    {
        $id = (int) $this->Request()->getParam('id');
        if ($id === 0) {
            $this->View()->assign(['success' => false, 'message' => 'mail id not found']);

            return;
        }

        $mail = $this->getRepository()->find($id);
        if (!$mail instanceof Mail) {
            $this->View()->assign(['success' => false, 'message' => 'Mail not found']);

            return;
        }

        if (!$mail->isUserMail()) {
            $this->View()->assign(['success' => false, 'message' => 'Only Usermails may be cloned']);

            return;
        }

        $clonedMail = clone $mail;
        $clonedMail->setName('Copy of ' . $mail->getName());

        $this->get('models')->persist($clonedMail);
        $this->get('models')->flush();

        $this->View()->assign(['success' => true]);
    }

    /**
     * Validate name action
     *
     * Validates whether or not the provided value exists in the database
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
                     ->getResult(AbstractQuery::HYDRATE_OBJECT);

        if (!empty($mail)) {
            $this->View()->assign(['success' => false, 'message' => 'Mail found found']);

            return;
        }

        echo 'true';
    }

    /**
     * Verify smarty Action
     *
     * Validates if the given value contains valid smartystring
     */
    public function sendTestmailAction()
    {
        $id = (int) $this->Request()->getParam('id');
        if ($id === 0) {
            $this->View()->assign(['success' => false, 'message' => 'mail id not found']);

            return;
        }

        $mail = $this->getRepository()->find($id);
        if (!$mail instanceof Mail) {
            $this->View()->assign(['success' => false, 'message' => 'Mail not found']);

            return;
        }

        if (!$this->Request()->getParam('value')) {
            $this->View()->assign(['success' => false, 'message' => 'Value not found']);
        }

        $recipient = Shopware()->Config()->get('mail');

        $shop = $this->get('models')->getRepository(Shop::class)->getActiveDefault();
        $this->get(ShopRegistrationServiceInterface::class)->registerShop($shop);

        try {
            $templateMail = Shopware()->TemplateMail()->createMail($mail, array_merge($this->getDefaultMailContext($shop), $mail->getContext()), $shop);
            $templateMail->addTo($recipient);
            $templateMail->setAssociation(AdministrativeMailFilter::ADMINISTRATIVE_MAIL, true);
            $templateMail->send();
        } catch (Exception $e) {
            $this->View()->assign(['success' => false, 'message' => $e->getMessage()]);

            return;
        }

        $this->View()->assign(['success' => true]);
    }

    /**
     * Verify smarty Action
     *
     * Validates if the given value contains valid smartystring
     */
    public function verifySmartyAction()
    {
        $id = (int) $this->Request()->getParam('id');
        if ($id === 0) {
            $this->View()->assign(['success' => false, 'message' => 'mail id not found']);

            return;
        }

        $mail = $this->getRepository()->find($id);
        if (!$mail instanceof Mail) {
            $this->View()->assign(['success' => false, 'message' => 'Mail not found']);

            return;
        }

        if (!($value = $this->Request()->getParam('value'))) {
            $this->View()->assign(['success' => false, 'message' => 'Value not found']);
        }

        $compiler = new Shopware_Components_StringCompiler($this->View()->Engine());

        $shop = $this->get('models')->getRepository(Shop::class)->getActiveDefault();
        $this->get(ShopRegistrationServiceInterface::class)->registerShop($shop);

        $compiler->setContext(array_merge($this->getDefaultMailContext($shop), $mail->getContext()));

        try {
            $template = $compiler->compileString($value);
        } catch (Enlight_Exception $e) {
            $this->View()->assign(['success' => false, 'message' => $e->getMessage()]);

            return;
        }

        $this->View()->assign(['success' => true, 'message' => $template]);
    }

    /**
     * Adds a attachment to given mail
     */
    public function addAttachmentAction()
    {
        if (!($mailId = $this->Request()->getParam('mailId'))) {
            $this->View()->assign(['success' => false, 'message' => 'MailId not found']);

            return;
        }

        if (!($mediaId = $this->Request()->getParam('mediaId'))) {
            $this->View()->assign(['success' => false, 'message' => 'MediaId not found']);

            return;
        }

        /** @var Mail|null $mail */
        $mail = $this->getRepository()->find($mailId);
        if (!$mail) {
            $this->View()->assign(['success' => false, 'message' => 'Mail not found']);

            return;
        }

        /** @var Media|null $media */
        $media = $this->get('models')->getRepository(Media::class)->find($mediaId);
        if (!$media) {
            $this->View()->assign(['success' => false, 'message' => 'Media not found']);

            return;
        }

        $attachment = new Attachment($mail, $media);

        try {
            $this->get('models')->persist($attachment);
            $this->get('models')->flush();
        } catch (Exception $e) {
            $this->View()->assign(['success' => false, 'message' => $e->getMessage()]);

            return;
        }

        $data = $this->get('models')->toArray($attachment);

        $this->View()->assign(['success' => true, 'data' => $data]);
    }

    /**
     * Remove attachment action
     */
    public function removeAttachmentAction()
    {
        if (!($attachmentId = $this->Request()->getParam('id'))) {
            $this->View()->assign(['success' => false, 'message' => 'Id not found']);
        }

        $attachment = $this->get('models')->getRepository(Attachment::class)->find($attachmentId);
        if (!$attachment) {
            $this->View()->assign(['success' => false, 'message' => 'Attachment not found']);

            return;
        }

        try {
            $this->get('models')->remove($attachment);
            $this->get('models')->flush();
        } catch (Exception $e) {
            $this->View()->assign(['success' => false, 'message' => $e->getMessage()]);

            return;
        }

        $this->View()->assign(['success' => true]);
    }

    /**
     * Update attachment action
     */
    public function updateAttachmentAction()
    {
        if (!($attachmentId = $this->Request()->getParam('id'))) {
            $this->View()->assign(['success' => false, 'message' => 'Id not found']);

            return;
        }

        if (!($shopId = $this->Request()->getParam('destinationShopId'))) {
            $this->View()->assign(['success' => false, 'message' => 'destinationShopId not found']);

            return;
        }

        if ($shopId == 0) {
            $shop = null;
        } else {
            $shop = $this->get('models')->getRepository(Shop::class)->find($shopId);
            if (!$shop) {
                $this->View()->assign(['success' => false, 'message' => 'Shop not found']);

                return;
            }
        }

        $attachment = $this->get('models')->getRepository(Attachment::class)->find($attachmentId);
        if (!$attachment) {
            $this->View()->assign(['success' => false, 'message' => 'Attachment not found']);

            return;
        }

        $attachment->setShop($shop);

        try {
            $this->get('models')->flush();
        } catch (Exception $e) {
            $this->View()->assign(['success' => false, 'message' => $e->getMessage()]);

            return;
        }

        $this->View()->assign(['success' => true]);
    }

    /**
     * Get attachment action
     */
    public function getAttachmentsAction()
    {
        $mailId = $this->Request()->getParam('mailId', false);
        $attachments = [];

        if ($mailId) {
            $mail = $this->getRepository()->find($mailId);
            if ($mail instanceof Mail) {
                $attachments = $mail->getAttachments();
            }
        }

        $nodes = [];

        /** @var Shop[] $shops */
        $shops = $this->get('models')->getRepository(Shop::class)->findAll();
        foreach ($shops as $shop) {
            $shopNode = [
                'allowDrag' => false,
                'leaf' => false,
                'filename' => $shop->getName(),
                'expanded' => true,
                'data' => [],
                'shopId' => $shop->getId(),
            ];

            $childNodes = [];
            foreach ($attachments as $attachment) {
                if ($shop->getId() !== $attachment->getShopId()) {
                    continue;
                }

                $childNodes[] = [
                    'leaf' => true,
                    'checked' => false,
                    'id' => $attachment->getId(),
                    'filename' => $attachment->getFileName(),
                    'size' => $attachment->getFormattedFileSize(),
                    'shopId' => $attachment->getShopId(),
                ];
            }

            $shopNode['data'] = $childNodes;

            $nodes[] = $shopNode;
        }

        $childNodes = [];
        foreach ($attachments as $attachment) {
            if ($attachment->getShopId() !== null) {
                continue;
            }

            $childNodes[] = [
                'leaf' => true,
                'checked' => false,
                'id' => $attachment->getId(),
                'filename' => $attachment->getFileName(),
                'size' => $attachment->getFormattedFileSize(),
            ];
        }

        $all = [
            'allowDrag' => false,
            'leaf' => false,
            'filename' => 'Global',
            'expanded' => true,
            'data' => $childNodes,
        ];

        $nodes[] = $all;

        $this->View()->assign(['success' => true, 'data' => $nodes]);
    }

    public function getMailVariablesAction()
    {
        $prefix = ltrim($this->Request()->getParam('prefix'), '$');

        /** @var Mail $mail */
        $mail = $this->getRepository()->find((int) $this->Request()->getParam('mailId'));
        $shop = $this->get(ModelManager::class)->getRepository(Shop::class)->getActiveDefault();
        $shop->registerResources();

        $context = array_merge($this->getDefaultMailContext($shop), $mail->getContext());

        $completer = $this->container->get(AutoCompleteResolver::class);

        $context = $completer->completer($context, $this->Request()->getParam('smartyCode'));
        $context = $mail->arrayGetPath($context);

        $result = [];

        foreach ($context as $key => $value) {
            if (strpos($key, $prefix) !== false || !$prefix) {
                $result[] = ['word' => '$' . $key, 'value' => \is_array($value) ? 'Array' : (string) $value];
            }
        }

        $this->View()->assign('data', $result);
        $this->View()->assign('success', true);
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
     * Gets a single mail
     *
     * @param int $id
     *
     * @return void
     */
    protected function getSingleMail($id)
    {
        $data = $this->getMail($id);
        if ($data === false) {
            $this->View()->assign(['success' => false, 'message' => 'Mail not found']);

            return;
        }

        $mail = $data['mail'];
        $data = $data['data'];

        $shop = $this->get('models')->getRepository(Shop::class)->getActiveDefault();
        $this->get(ShopRegistrationServiceInterface::class)->registerShop($shop);

        $context = $mail->getContext();
        if (empty($context)) {
            $context = [];
        }
        $data['contextPath'] = $mail->arrayGetPath(array_merge($this->getDefaultMailContext($shop), $context));

        $this->View()->assign(['success' => true, 'data' => $data, 'total' => 1]);
    }

    /**
     * Internal helper function to get access to the mail repository.
     *
     * @return Repository
     */
    private function getRepository()
    {
        if ($this->repository === null) {
            $this->repository = $this->get('models')->getRepository(Mail::class);
        }

        return $this->repository;
    }

    /**
     * Returns an array with the converted mail data and a mail object for the passed mail id.
     *
     * @return false|array{data: array, mail: Mail}
     */
    private function getMail(int $id)
    {
        $query = $this->getRepository()->getMailQuery($id);
        $mail = $query->getOneOrNullResult(AbstractQuery::HYDRATE_OBJECT);

        if (!$mail instanceof Mail) {
            $this->View()->assign(['success' => false, 'message' => 'Mail not found']);

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

        $data = $query->getOneOrNullResult(AbstractQuery::HYDRATE_ARRAY);
        $data['type'] = $type;

        return [
            'data' => $data,
            'mail' => $mail,
        ];
    }

    /**
     * @return array
     */
    private function getDefaultMailContext(Shop $shop)
    {
        return [
            'sShop' => $this->container->get(Shopware_Components_Config::class)->get('ShopName'),
            'sShopURL' => ($shop->getSecure() ? 'https://' : 'http://') . $shop->getHost() . $shop->getBaseUrl(),
            'sConfig' => $this->container->get(Shopware_Components_Config::class),
        ];
    }

    /**
     * Replace the name of the email template with a more human readable name. The names from the document types
     * are used for this.
     */
    private function getFriendlyNameOfDocumentEmail(string $mailName): string
    {
        if ($mailName === 'sORDERDOCUMENTS') {
            $namespace = Shopware()->Snippets()->getNamespace('backend/mail/view/navigation');

            return $namespace->get('mails_documents_default', 'Default template');
        }

        $documentEmailsNamePrefix = 'document_';
        if (mb_strpos($mailName, $documentEmailsNamePrefix) !== 0) {
            return $mailName;
        }
        $documentTypeKey = str_replace($documentEmailsNamePrefix, '', $mailName);
        /** @var Document|null $documentType */
        $documentType = $this->getModelManager()->getRepository(Document::class)->findOneBy([
            'key' => $documentTypeKey,
        ]);
        if (!$documentType) {
            return $mailName;
        }

        return $documentType->getName();
    }
}
