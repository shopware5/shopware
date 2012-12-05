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
 * @package    Shopware_Plugins_Backend_SwagNewsletter
 * @subpackage Result
 * @copyright  Copyright (c) 2012, shopware AG (http://www.shopware.de)
 * @version    $Id$
 * @author     Daniel Nögel
 * @author     $Author$
 */

/**
 * Plugin that extends the newsletter manager
 *
 * More features:
 * - Ability to manage bounced mails
 * - Add articles, links and banners to your mails
 * - Have a nice graphical designer for your newsletter
 */
class Shopware_Plugins_Backend_SwagNewsletter_Bootstrap extends Shopware_Components_Plugin_Bootstrap
{
    /**
     * Install plugin
     * @return bool
     * @throws Exception
     */
    public function install()
    {
        // Check if shopware version matches
        if (!$this->assertVersionGreaterThen('4.0.4')) {
            throw new Exception("This plugin requires Shopware 4.0.4 or a later version");
        }

        // Check license
        $this->checkLicense(true);

        $this->subscribeEvent(
            'Enlight_Controller_Action_Init_Backend_NewsletterManager',
            'onPreDispatch'
        );

        $this->subscribeEvent(
            'Enlight_Controller_Dispatcher_ControllerPath_Backend_SwagNewsletter',
            'onGetControllerPathBackend'
        );

        $this->subscribeEvent(
            'Enlight_Controller_Action_PostDispatch_Backend_NewsletterManager',
            'onPostDispatch'
        );

        $this->subscribeEvent(
                'Shopware_Controllers_Backend_Newsletter::getMailingSuggest::after',
                'getMailingSuggest'
        );

        $this->createTables();


        // set up the models
        $this->registerCustomModels();
        $tool = new \Doctrine\ORM\Tools\SchemaTool(Shopware()->Models());
        $classes = array(
          Shopware()->Models()->getClassMetadata('Shopware\CustomModels\SwagNewsletter\Component'),
          Shopware()->Models()->getClassMetadata('Shopware\CustomModels\SwagNewsletter\Field')
        );

//        $tool->createSchema($classes);

        return true;
    }

    /**
     * @param   bool $throwException
     * @throws  Exception
     * @return  bool
     */
    public function checkLicense($throwException = true)
    {
        static $r, $m = 'SwagNewsletter';
        if(!isset($r)) {
            $s = base64_decode('9LdB6N1xtF/Aj9K8HtRknXILP2g=');
            $c = base64_decode('nG0lFFg5llMhA2WOJz/4t5BIBtk=');
            $r = sha1(uniqid('', true), true);
            /** @var $l Shopware_Components_License */
            $l = $this->Application()->License();
            $i = $l->getLicense($m, $r);
            $t = $l->getCoreLicense();
            $u = strlen($t) === 20 ? sha1($t . $s . $t, true) : 0;
            $r = $i === sha1($c. $u . $r, true);
        }
        if(!$r && $throwException) {
            throw new Exception('License check for module "' . $m . '" has failed.');
        }
        return $r;
    }

    /**
     * Called after the getMailingSuggest method of the newsletter controller
     * Gets suggestions from the newsletter core class
     * @param $args
     * @return mixed
     */
    static function getMailingSuggest($args) {
        $args = $args->getArgs();
        $id = $args[0];
        $userId = $args[1];

        $newsletter = Shopware()->Modules()->Newsletter();
        $result = $newsletter->sCampaignsGetSuggestions($id, $userId);
        return $result;
    }

    /**
     * Uninstall method
     * @return bool
     */
    public function uninstall() {
        return true;
    }

    public function afterInit()
    {
        $this->registerCustomModels();
    }

    /**
     * Creates the required tables
     */
    private function createTables() {

        if(!$this->checkLicense(false)){
            return false;
        };

        $sql =  "CREATE TABLE IF NOT EXISTS `s_campaigns_component` (  `id` int( 11  )  NOT  NULL  AUTO_INCREMENT ,
         `name` varchar( 255  )  COLLATE utf8_unicode_ci NOT  NULL ,
         `x_type` varchar( 255  )  COLLATE utf8_unicode_ci NOT  NULL ,
         `convert_function` varchar( 255  )  COLLATE utf8_unicode_ci  DEFAULT NULL ,
         `description` text COLLATE utf8_unicode_ci NOT  NULL ,
         `template` varchar( 255  )  COLLATE utf8_unicode_ci NOT  NULL ,
         `cls` varchar( 255  )  COLLATE utf8_unicode_ci NOT  NULL ,
         `pluginID` int( 11  )  DEFAULT NULL ,
         PRIMARY  KEY (  `id`  )  ) ENGINE  = InnoDB  DEFAULT CHARSET  = utf8 COLLATE  = utf8_unicode_ci;

         CREATE TABLE IF NOT EXISTS `s_campaigns_component_field` (  `id` int( 11  )  NOT  NULL  AUTO_INCREMENT ,
         `componentID` int( 11  )  NOT  NULL ,
         `name` varchar( 255  )  COLLATE utf8_unicode_ci NOT  NULL ,
         `x_type` varchar( 255  )  COLLATE utf8_unicode_ci NOT  NULL ,
         `value_type` varchar( 255  )  COLLATE utf8_unicode_ci NOT  NULL ,
         `field_label` varchar( 255  )  COLLATE utf8_unicode_ci NOT  NULL ,
         `support_text` varchar( 255  )  COLLATE utf8_unicode_ci NOT  NULL ,
         `help_title` varchar( 255  )  COLLATE utf8_unicode_ci NOT  NULL ,
         `help_text` text COLLATE utf8_unicode_ci NOT  NULL ,
         `store` varchar( 255  )  COLLATE utf8_unicode_ci NOT  NULL ,
         `display_field` varchar( 255  )  COLLATE utf8_unicode_ci NOT  NULL ,
         `value_field` varchar( 255  )  COLLATE utf8_unicode_ci NOT  NULL ,
         `default_value` varchar( 255  )  COLLATE utf8_unicode_ci NOT  NULL ,
         `allow_blank` int( 1  )  NOT  NULL ,
         PRIMARY  KEY (  `id`  )  ) ENGINE  = InnoDB  DEFAULT CHARSET  = utf8 COLLATE  = utf8_unicode_ci;";

        Shopware()->Db()->query($sql);

        $sql = "INSERT IGNORE INTO `s_campaigns_component` (`id`, `name`, `x_type`, `convert_function`, `description`, `template`, `cls`, `pluginID`) VALUES
        (1, 'HTML-Element', 'newsletter-components-text', NULL, '', 'component_html', 'newsletter-html-text-element', NULL),
        (2, 'Banner', '', NULL, '', 'component_banner', 'newsletter-banner-element', NULL),
        (3, 'Artikel-Gruppe', 'newsletter-components-article', 'getArticle', '', 'component_article', 'newsletter-article-element', NULL),
        (4, 'Link', 'newsletter-components-links', 'getLinks', '', 'component_link', 'newsletter-link-element', NULL),
        (5, 'Gutschein', 'newsletter-components-text', 'getVoucher', '', 'component_voucher', 'newsletter-voucher-element', NULL),
        (6, 'Suggest', '', 'getSuggest', '', 'component_suggest', 'newsletter-suggest-element', NULL);

        INSERT IGNORE INTO `s_campaigns_component_field` (`id`, `componentID`, `name`, `x_type`, `value_type`, `field_label`, `support_text`, `help_title`, `help_text`, `store`, `display_field`, `value_field`, `default_value`, `allow_blank`) VALUES
        (1, 3, 'headline', 'textfield', '', 'Überschrift', 'Dieses Feld kann leer gelassen werden, wenn keine Überschrift erwünscht ist.', '', '', '', '', '', '', 1),
        (2, 3, 'article_data', 'hidden', 'json', '', '', '', '', '', '', '', '', 0),
        (3, 1, 'headline', 'textfield', '', 'Überschrift', '', '', '', '', '', '', '', 0),
        (4, 1, 'text', 'tinymce', '', 'Text', 'Anzuzeigender Text', 'HTML-Text', 'Geben Sie hier den Text ein, der in dem Element angezeigt werden soll.', '', '', '', '', 0),
        (5, 1, 'image', 'mediaselectionfield', '', 'Bild', '', '', '', '', '', '', '', 1),
        (6, 1, 'url', 'textfield', '', 'Direktlink', '', '', '', '', '', '', '', 1),
        (7, 2, 'description', 'textfield', '', 'Überschrift', '', '', '', '', '', '', '', 1),
        (8, 2, 'file', 'mediaselectionfield', '', 'Bild', '', '', '', '', '', '', '', 0),
        (9, 2, 'link', 'textfield', '', 'Link', '', '', '', '', '', '', '', 0),
        (10, 2, 'target_selection', 'newsletter-components-fields-target-selection', '', 'Link-Ziel', 'Soll sich der Link im Shopware-Fenster oder einem neuen Fenster öffnen?', '', '', '', '', '', '', 0),
        (11, 4, 'link_data', 'hidden', 'json', '', '', '', '', '', '', '', '', 0),
        (12, 4, 'description', 'textfield', '', 'Überschrift', '', '', '', '', '', '', '', 1),
        (13, 5, 'headline', 'textfield', '', 'Überschrift', '', '', '', '', '', '', '', 0),
        (14, 5, 'voucher_selection', 'newsletter-components-fields-voucher-selection', '', 'Gutschein', '', '', '', '', '', '', '', 0),
        (15, 5, 'text', 'tinymce', '', 'Text', 'Anzuzeigender Text', 'HTML-Text', 'Geben Sie hier den Text ein, der in dem Element angezeigt werden soll.', '', '', '', 'Gutscheincode: {\$sVoucher.code}', 0),
        (16, 5, 'image', 'mediaselectionfield', '', 'Bild', '', '', '', '', '', '', '', 1),
        (17, 5, 'url', 'textfield', '', 'Direktlink', '', '', '', '', '', '', '', 1),
        (18, 6, 'headline', 'textfield', '', 'Überschrift', '', '', '', '', '', '', '', 1),
        (19, 6, 'number', 'newsletter-components-fields-numberfield', '', 'Anzahl der vorgeschlagenen Artikel', '', '', '', '', '', '', 3, 0);
        ";

        Shopware()->Db()->exec($sql);

        return true;
    }

    /**
     * Convenience function to register template and snippet dirs
     */
    protected function registerMyTemplateDir()
    {

        if(!$this->checkLicense(false)){
            return;
        };

        $this->Application()->Snippets()->addConfigDir(
            $this->Path() . 'Snippets/'
        );
        $this->Application()->Template()->addTemplateDir(
            $this->Path() . 'Views/'
        );
    }

    public function onPreDispatch(Enlight_Event_EventArgs $args)
    {

        if(!$this->checkLicense(false)){
            return;
        };

        $response = $args->getSubject()->Response();
        $view     = $args->getSubject()->View();
        $request  = $args->getSubject()->Request();

        if ($response->isException()) {
            return;
        }

        $templates = $view->Engine()->getTemplateDir();
        array_unshift($templates, dirname(__FILE__) . '/Controllers/');
        array_unshift($templates, dirname(__FILE__) . '/Views/');

        $view->setTemplateDir($templates);
    }

    public function onPostDispatch(Enlight_Event_EventArgs $arguments)
    {

        if(!$this->checkLicense(false)){
            return;
        };

        $this->registerMyTemplateDir();
        $arguments->getSubject()->View()->addTemplateDir(
            $this->Path() . 'Views/'
        );

        //if the controller action name equals "load" we have to load all application components.
        if ($arguments->getRequest()->getActionName() === 'load') {
            $arguments->getSubject()->View()->extendsTemplate(
                'backend/newsletter_manager/controller/overview.js'
            );
        }

        //if the controller action name equals "index" we have to extend the backend newsletter_manager application
        if ($arguments->getRequest()->getActionName() === 'index') {
            $arguments->getSubject()->View()->extendsTemplate(
                'backend/newsletter_manager/newsletter_app.js'
            );
        }
    }

    /**
     * Returns the backend controller path
     * @param   Enlight_Event_EventArgs $args
     * @return  string
     */
    public function onGetControllerPathBackend(Enlight_Event_EventArgs $args)
    {

        if(!$this->checkLicense(false)){
            return null;
        };

        $this->registerMyTemplateDir();

        return $this->Path() . '/Controllers/Backend/SwagNewsletter.php';

    }

    /**
     * Get label
     * @return string
     */
    public function getLabel()
    {
        return "Intelligenter Newsletter";
    }

    /**
     * Get version
     * @return string
     */
    public function getVersion()
    {
        return "2.0.0";
    }
}