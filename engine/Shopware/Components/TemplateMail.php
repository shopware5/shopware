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
 * Shopware TemplateMail Component
 */
class Shopware_Components_TemplateMail
{
    /**
     * @var \Shopware\Models\Shop\Shop
     */
    protected $shop;

    /**
     * @var \Shopware\Components\Model\ModelManager
     */
    protected $modelManager;

    /**
     * @var \Shopware_Components_Translation
     */
    protected $translationReader;

    /**
     * @var \Shopware_Components_StringCompiler
     */
    protected $stringCompiler;

    /**
     * @param \Shopware\Components\Model\ModelManager $modelManager
     * @return \Shopware_Components_TemplateMail
     */
    public function setModelManager(\Shopware\Components\Model\ModelManager $modelManager)
    {
        $this->modelManager = $modelManager;

        return $this;
    }

    /**
     * @return \Shopware\Components\Model\ModelManager
     */
    public function getModelManager()
    {
        return $this->modelManager;
    }

    /**
     * @param $shop
     * @return \Shopware_Components_TemplateMail
     */
    public function setShop($shop)
    {
        $this->shop = $shop;

        return $this;
    }

    /**
     * @return \Shopware\Models\Shop\Shop
     */
    public function getShop()
    {
        return $this->shop;
    }

    /**
     * @return \Shopware_Components_Translation
     */
    public function getTranslationReader()
    {
        if (null === $this->translationReader) {
            $this->translationReader = new Shopware_Components_Translation();
        }

        return $this->translationReader;
    }

    /**
     * @param \Shopware_Components_Translation $translationReader
     * @return \Shopware_Components_TemplateMail
     */
    public function setTranslationReader($translationReader)
    {
        $this->translationReader = $translationReader;

        return $this;
    }

    /**
     * @param \Shopware_Components_StringCompiler $stringCompiler
     * @return \Shopware_Components_TemplateMail
     */
    public function setStringCompiler(\Shopware_Components_StringCompiler $stringCompiler)
    {
        $this->stringCompiler = $stringCompiler;

        return $this;
    }

    /**
     * @return \Shopware_Components_StringCompiler
     */
    public function getStringCompiler()
    {
        return $this->stringCompiler;
    }

    /**
     * @param string|\Shopware\Models\Mail\Mail $mailModel
     * @param array $context
     * @param \Shopware\Models\Shop\Shop $shop
     * @param array $overrideConfig
     * @return \Enlight_Components_Mail
     * @throws \Enlight_Exception
     */
    public function createMail($mailModel, $context = array(), $shop = null, $overrideConfig = array())
    {
        if (null !== $shop) {
            $this->setShop($shop);
        }

        if (!($mailModel instanceof \Shopware\Models\Mail\Mail)) {
            $modelName = $mailModel;
            /* @var $mailModel \Shopware\Models\Mail\Mail */
            $mailModel = $this->getModelManager()->getRepository('Shopware\Models\Mail\Mail')->findOneBy(
                array('name' => $modelName)
            );
            if (!$mailModel) {
                throw new \Enlight_Exception("Mail-Template with name '{$modelName}' could not be found.");
            }
        }

        //todo@all Add setter and getter like the shop
        $config = Shopware()->Config();

        if ($this->getShop() !== null) {
            $defaultContext = array(
                'sConfig' => $config,
                'sShop' => $config->get('shopName'),
                'sShopURL' => 'http://' . $config->basePath,
            );
            $isoCode = $this->getShop()->getId();
            $translationReader = $this->getTranslationReader();
            $translation = $translationReader->read($isoCode, 'config_mails', $mailModel->getId());
            $mailModel->setTranslation($translation);
        } else {
            $defaultContext = array(
                'sConfig' => $config,
            );
        }

        // save current context to mail model
        $mailContext = json_encode($context);
        $mailContext = json_decode($mailContext, true);
        $mailModel->setContext($mailContext);
        $this->getModelManager()->flush($mailModel);

        $this->getStringCompiler()->setContext(array_merge($defaultContext, $context));

        $mail = clone Shopware()->Container()->get('mail');

        return $this->loadValues($mail, $mailModel, $overrideConfig);
    }

    /**
     * Loads values from MailModel into Mail
     *
     * @param \Enlight_Components_Mail $mail
     * @param \Shopware\Models\Mail\Mail $mailModel
     * @param array $overrideConfig
     * @return \Enlight_Components_Mail
     * @throws \Enlight_Exception
     */
    public function loadValues(\Enlight_Components_Mail $mail, \Shopware\Models\Mail\Mail $mailModel, $overrideConfig = array())
    {
        $stringCompiler = $this->getStringCompiler();

        $subject = $stringCompiler->compileString($mailModel->getSubject());
        if (!empty($subject)) {
            $mail->setSubject($subject);
        }

        if (!empty($overrideConfig["fromMail"])) {
            $fromMail = $overrideConfig["fromMail"];
        } else {
            $fromMail = $stringCompiler->compileString($mailModel->getFromMail());
        }

        if (!empty($overrideConfig["fromName"])) {
            $fromName = $overrideConfig["fromName"];
        } else {
            $fromName = $stringCompiler->compileString($mailModel->getFromName());
        }

        if (!empty($fromMail) && !empty($fromName)) {
            $mail->setFrom($fromMail, $fromName);
        } elseif (!empty($fromMail)) {
            $mail->setFrom($fromMail);
        }

        $bodyText = $stringCompiler->compileString($mailModel->getContent());
        $mail->setBodyText($bodyText);

        if ($mailModel->isHtml()) {
            $mail->setBodyHtml($stringCompiler->compileString($mailModel->getContentHtml()));
        }

        /** @var $attachment \Shopware\Models\Mail\Attachment */
        foreach ($mailModel->getAttachments() as $attachment) {
            if ($attachment->getShopId() !== null
                && ($this->getShop() === null || $attachment->getShopId() != $this->getShop()->getId())) {
                continue;
            }

            $mediaService = Shopware()->Container()->get('shopware_media.media_service');
            if (!$mediaService->has($attachment->getPath())) {
                Shopware()->Container()->get('corelogger')->error('Could not load file: ' . $attachment->getPath());
            } else {
                $fileAttachment = $mail->createAttachment($mediaService->read($attachment->getPath()));
                $fileAttachment->filename = $attachment->getFileName();
            }
        }

        return $mail;
    }
}
