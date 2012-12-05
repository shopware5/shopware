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
 * @package    Shopware_Plugins
 * @subpackage Staging
 * @copyright  Copyright (c) 2012, shopware AG (http://www.shopware.de)
 * @version    1.0.1
 * @author     st.hamann
 */

/**
 * Staging-System Bootstrap
 */
class Shopware_Plugins_Core_SwagStaging_Bootstrap extends Shopware_Components_Plugin_Bootstrap
{
	public function install()
	{
        $this->checkLicense(true);
        $parent = $this->Menu()->findOneBy('label', 'Einstellungen');
        $this->createMenuItem(array(
           'label' => 'Staging',
           'controller' => 'Staging',
           'action' => 'Index',
           'class' => 'sprite-share',
           'active' => 1,
           'parent' => $parent
        ));
        $this->createTables();

        // Deactivate shop redirects
        $this->subscribeEvent(
           'Enlight_Controller_Front_RouteStartup',
           'onRouteStartup',
           99
        );

        // Backend-Controller
        $this->subscribeEvent('Enlight_Controller_Dispatcher_ControllerPath_Backend_Staging','onGetControllerPathBackend');

        // Event to add info assets when user is in staging-backend
        $this->subscribeEvent('Enlight_Controller_Action_PostDispatch_Backend_Index','onRenderBackendIndex');

        return true;
    }

    /**
    * @param   bool $throwException
    * @throws  Exception
    * @return  bool
    */
   public function checkLicense($throwException = true)
   {
       static $r, $m = 'SwagStaging';
       if(!isset($r)) {
           $s = base64_decode('DlmKIK9vNwfTqDG2xOwH0zcG5Bs=');
           $c = base64_decode('DG8VlQ7O7EWVf+JUHYC1Jag1npM=');
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
     * Create database tables
     */
    protected function createTables(){
        $this->Application()->Db()->query("
        CREATE TABLE IF NOT EXISTS `s_plugin_staging_config` (
          `id` int(11) NOT NULL AUTO_INCREMENT,
          `active` int(1) NOT NULL,
          `database_user` varchar(255) NOT NULL,
          `database_password` varchar(255) NOT NULL,
          `database_host` varchar(255) NOT NULL,
          `file_distribution` varchar(25) NOT NULL,
          `master_database` varchar(255) NOT NULL,
          `slave_database` varchar(255) NOT NULL,
          `slave_host` varchar(255) NOT NULL,
          `slave_resources` varchar(255) NOT NULL,
          `slave_files` varchar(255) NOT NULL,
          `slave_cache` varchar(255) NOT NULL,
          `slave_proxies` varchar(255) NOT NULL,
          PRIMARY KEY (`id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;
        ");

        $this->Application()->Db()->query("
        CREATE TABLE IF NOT EXISTS `s_plugin_staging_jobs` (
          `id` int(11) NOT NULL AUTO_INCREMENT,
          `profile_id` int(11) NOT NULL,
          `description` varchar(255) NOT NULL,
          `create_date` datetime DEFAULT NULL,
          `start_date` datetime DEFAULT NULL,
          `end_date` datetime DEFAULT NULL,
          `user` varchar(255) NOT NULL,
          `running` int(1) NOT NULL,
          `jobs_total` int(11) NOT NULL,
          `jobs_current` int(11) NOT NULL,
          `successful` int(11) NOT NULL,
          `error_msg` text NOT NULL,
          PRIMARY KEY (`id`)
        ) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=3 ;
        ");

        $this->Application()->Db()->query("CREATE TABLE IF NOT EXISTS `s_plugin_staging_jobs_profiles` (
          `id` int(11) NOT NULL AUTO_INCREMENT,
          `profile_assignment` varchar(255) NOT NULL,
          `profile_key` varchar(255) NOT NULL,
          `profile_text` varchar(255) NOT NULL,
          `profile_description` text NOT NULL,
          `jobs_per_request` int(11) NOT NULL,
          PRIMARY KEY (`id`)
        ) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=4 ;");

        $this->Application()->Db()->query("
        CREATE TABLE IF NOT EXISTS `s_plugin_staging_jobs_queue` (
          `id` int(11) NOT NULL AUTO_INCREMENT,
          `job_id` int(11) NOT NULL,
          `text` varchar(255) NOT NULL,
          `job` text NOT NULL,
          `done` int(1) NOT NULL,
          `error_msg` varchar(255) NOT NULL,
          `start` datetime NOT NULL,
          `duration` time NOT NULL,
          PRIMARY KEY (`id`)
        ) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=719 ;
        ");

        $this->Application()->Db()->query("
        CREATE TABLE IF NOT EXISTS `s_plugin_staging_tables` (
          `id` int(11) NOT NULL AUTO_INCREMENT,
          `profile_id` int(11) NOT NULL,
          `table_name` varchar(255) NOT NULL,
          `strategy` varchar(255) NOT NULL,
          PRIMARY KEY (`id`),
          UNIQUE KEY `profile_id` (`profile_id`,`table_name`)
        ) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=677 ;
        ");

        $this->Application()->Db()->query("
        CREATE TABLE IF NOT EXISTS `s_plugin_staging_tables_columns` (
          `id` int(11) NOT NULL AUTO_INCREMENT,
          `table_id` int(11) NOT NULL,
          `col` varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
          PRIMARY KEY (`id`)
        ) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=3 ;
        ");

    }

    public function getVersion()
    {
       return "1.0.1";
    }

    public function getLabel(){
        return "Shopware Staging-System";
    }

    /**
     * Prevent shop from redirect to live-shop-url
     * Pass parameters from staging-config to shop-config
     *
     * @param Enlight_Controller_EventArgs $args
     */
    public function onRouteStartup(Enlight_Controller_EventArgs $args)
    {

        $stagingConfig = Shopware()->getOption("custom");

        if (empty($stagingConfig["is_staging"])) return;

        $bootstrap = $this->Application()->Bootstrap();
        if ($bootstrap->issetResource('Shop')) {

            $shop = $this->Application()->Shop();
            $main = $shop->getMain();

            if($main === null) {
                /** @var $repository Shopware\Models\Shop\Repository */
                $repository = Shopware()->Models()->getRepository('Shopware\Models\Shop\Shop');
                $main = $repository->getActiveById($shop->getId());
            }
            // Template Model übergeben
            if (!empty($stagingConfig["staging_template"])){
               $repository = 'Shopware\Models\Shop\Template';
               $repository = Shopware()->Models()->getRepository($repository);
               $template = $stagingConfig["staging_template"];
               $template = $repository->findOneBy(array('template' => $template));
               if ($template !== null){
                   $shop->setTemplate($template);
               }else {
                   $shop->setTemplate($main->getTemplate());
               }
            }else {
                $shop->setTemplate($main->getTemplate());
            }
            /*
            if (empty($stagingConfig["staging_template"])){
                $shop->setTemplate($main->getTemplate());
            }else {
                $shop->setTemplate($stagingConfig["staging_template"]);
            }*/
            $shop->setHost($stagingConfig["staging_url"]);

            if (!empty($stagingConfig["staging_url_ssl"])){
                $shop->setSecureHost($main->getSecureHost() ?: $main->getHost());
            }
        }
    }

    /**
     * Add staging assets if staging-system is active
     * @param Enlight_Event_EventArgs $args
     */
    public function onRenderBackendIndex(Enlight_Event_EventArgs $args){
       $view = $args->getSubject()->View();
       $stagingConfig = Shopware()->getOption("custom");

       if (empty($stagingConfig["is_staging"])){
           $this->Application()->Template()->addTemplateDir(
                     $this->Path() . 'Views/'
           );

           $view->extendsTemplate("backend/staging/override_master.tpl");
           return;
       }

       if (!$view->hasTemplate()){
           return;
       }

       $this->Application()->Template()->addTemplateDir(
               $this->Path() . 'Views/'
       );

       $view->extendsTemplate("backend/staging/override.tpl");
    }

    /**
     * Add our custom-models & components to scope
     */
    public function afterInit(){
       $this->registerCustomModels();
       require_once($this->Path()."Components/Staging.php");
    }

    public function onGetControllerPathBackend(Enlight_Event_EventArgs $args){
        $this->Application()->Snippets()->addConfigDir(
            $this->Path() . 'Snippets/'
        );

        return dirname(__FILE__) . '/Controllers/Backend/Staging.php';
    }
}
