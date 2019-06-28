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

use Shopware\Bundle\MailBundle\Service\LogEntryBuilder;
use Shopware\Components\Model\ModelManager;
use Shopware\Models\Mail\Mail;
use Shopware\Models\Shop\Shop;

/**
 * Shopware TemplateMail Component
 */
class Shopware_Components_TemplateMail
{
    /**
     * @var Shop
     */
    protected $shop;

    /**
     * @var ModelManager
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
     * @var array
     */
    protected $themeVariables = [
        'mobileLogo' => true,
        'tabletLogo' => true,
        'tabletLandscapeLogo' => true,
        'desktopLogo' => true,
        'appleTouchIcon' => true,
        'brand-primary' => true,
        'brand-primary-light' => true,
        'brand-secondary' => true,
        'brand-secondary-dark' => true,
        'text-color' => true,
        'text-color-dark' => true,
        'body-bg' => true,
        'link-color' => true,
        'link-hover-color' => true,
        'font-size-h1' => true,
        'font-size-h2' => true,
        'font-size-h4' => true,
        'font-size-h5' => true,
        'font-size-h6' => true,
    ];

    /**
     * @return \Shopware_Components_TemplateMail
     */
    public function setModelManager(ModelManager $modelManager)
    {
        $this->modelManager = $modelManager;

        return $this;
    }

    /**
     * @return ModelManager
     */
    public function getModelManager()
    {
        return $this->modelManager;
    }

    /**
     * @param Shop $shop
     *
     * @return \Shopware_Components_TemplateMail
     */
    public function setShop($shop)
    {
        $this->shop = $shop;

        return $this;
    }

    /**
     * @return Shop|null
     */
    public function getShop()
    {
        return $this->shop;
    }

    /**
     * @throws \Exception
     *
     * @return \Shopware_Components_Translation
     */
    public function getTranslationReader()
    {
        if ($this->translationReader === null) {
            $this->translationReader = Shopware()->Container()->get('translation');
        }

        return $this->translationReader;
    }

    /**
     * @param \Shopware_Components_Translation $translationReader
     *
     * @return \Shopware_Components_TemplateMail
     */
    public function setTranslationReader($translationReader)
    {
        $this->translationReader = $translationReader;

        return $this;
    }

    /**
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
     * @param string|Mail $mailModel
     * @param array       $context
     * @param Shop        $shop
     * @param array       $overrideConfig
     *
     * @throws \Enlight_Exception
     *
     * @return \Enlight_Components_Mail
     */
    public function createMail($mailModel, $context = [], $shop = null, $overrideConfig = [])
    {
        if ($shop !== null) {
            $this->setShop($shop);
        }

        if (!($mailModel instanceof Mail)) {
            $modelName = $mailModel;
            /** @var Mail|null $mailModel */
            $mailModel = $this->getModelManager()->getRepository(Mail::class)->findOneBy(
                ['name' => $modelName]
            );
            if (!$mailModel) {
                throw new \Enlight_Exception(sprintf('Mail-Template with name "%s" could not be found.', $modelName));
            }
        }

        $config = Shopware()->Config();
        $inheritance = Shopware()->Container()->get('theme_inheritance');
        $eventManager = Shopware()->Container()->get('events');

        if ($this->getShop() !== null) {
            $defaultContext = [
                'sConfig' => $config,
                'sShop' => $config->get('shopName'),
                'sShopURL' => ($this->getShop()->getSecure() ? 'https://' : 'http://') . $this->getShop()->getHost() . $this->getShop()->getBaseUrl(),
            ];

            // Add theme to the context if given shop (or its main shop) has a template.
            $theme = null;
            if ($this->getShop()->getTemplate()) {
                $theme = $inheritance->buildConfig($this->getShop()->getTemplate(), $this->getShop(), false);
            } elseif ($this->getShop()->getMain() && $this->getShop()->getMain()->getTemplate()) {
                $theme = $inheritance->buildConfig($this->getShop()->getMain()->getTemplate(), $this->getShop(), false);
            }

            if ($theme) {
                $keys = $eventManager->filter(
                    'TemplateMail_CreateMail_Available_Theme_Config',
                    $this->themeVariables,
                    ['theme' => $theme]
                );

                $theme = array_intersect_key($theme, $keys);
                $defaultContext['theme'] = $theme;
            }

            $isoCode = $this->getShop()->getId();
            $translationReader = $this->getTranslationReader();

            if ($fallback = $this->getShop()->getFallback()) {
                $translation = $translationReader->readWithFallback($isoCode, $fallback->getId(), 'config_mails', $mailModel->getId());
            } else {
                $translation = $translationReader->read($isoCode, 'config_mails', $mailModel->getId());
            }

            $mailModel->setTranslation($translation);
        } else {
            $defaultContext = [
                'sConfig' => $config,
            ];
        }

        // Save current context to mail model
        $mailContext = json_decode(json_encode($context), true);

        $mailContext = $eventManager->filter(
            'TemplateMail_CreateMail_MailContext',
            $mailContext,
            [
                'mailModel' => $mailModel,
            ]
        );

        $mailModel->setContext($mailContext);
        $this->getModelManager()->flush($mailModel);

        $this->getStringCompiler()->setContext(array_merge($defaultContext, $context));

        $mail = clone Shopware()->Container()->get('mail');

        return $this->loadValues($mail, $mailModel, $overrideConfig);
    }

    /**
     * Loads values from MailModel into Mail
     *
     * @param array $overrideConfig
     *
     * @throws \Enlight_Exception
     *
     * @return \Enlight_Components_Mail
     */
    public function loadValues(\Enlight_Components_Mail $mail, Mail $mailModel, $overrideConfig = [])
    {
        $stringCompiler = $this->getStringCompiler();

        $subject = $stringCompiler->compileString($mailModel->getSubject());
        if (!empty($subject)) {
            $mail->setSubject($subject);
        }

        if (!empty($overrideConfig['fromMail'])) {
            $fromMail = $overrideConfig['fromMail'];
        } else {
            $fromMail = $stringCompiler->compileString($mailModel->getFromMail());
        }

        if (!empty($overrideConfig['fromName'])) {
            $fromName = $overrideConfig['fromName'];
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

        /** @var \Shopware\Models\Mail\Attachment $attachment */
        foreach ($mailModel->getAttachments() as $attachment) {
            if ($attachment->getShopId() !== null
                && ($this->getShop() === null || $attachment->getShopId() !== $this->getShop()->getId())) {
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

        $mail->setTemplateName($mailModel->getName());

        if ($this->getShop() !== null) {
            $mail->setAssociation(LogEntryBuilder::SHOP_ID_ASSOCIATION, $this->getShop()->getId());
        }

        return $mail;
    }
}
