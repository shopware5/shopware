<?php
/**
 * This plugin manages all controllers, models and views of the shopware bug tracker
 * The bug tracker communicates with the local jira instance of shopware.
 * Over the bug tracker external users can view the tasks of shopware and has
 * the possibility to create new tickets and proposals
 *
 * This plugin starts a redirect of all index controller calls to the jira index controller
 *
 * @copyright Copyright (c) 2011, Shopware AG
 * @author d.scharfenberg
 * @author $Author$
 * @package Shopware
 * @subpackage Plugins_Frontend
 * @creation_date 16.05.12 11:46
 * @version $Id$
 */
class Shopware_Plugins_Frontend_SwagBugTracker_Bootstrap extends Shopware_Components_Plugin_Bootstrap
{
    /**
     * Installs the plugin subscribes which are the following:
     * - Register of the new controller bug_tracker
     * - Global pre dispatcher event
     *
     * @return bool
     */
    public function install()
    {
        //Registers of the new controller bug_tracker
        $event = $this->createEvent(
            'Enlight_Controller_Dispatcher_ControllerPath_Widgets_Jira',
            'onGetControllerPathFrontendJira'
        );
        $this->subscribeEvent($event);

        //Registers the global pre dispatcher event
        $event = $this->createEvent(
            'Enlight_Controller_Action_PreDispatch',
            'onPreDispatch'
        );
        $this->subscribeEvent($event);

        //Registers the global post dispatcher event
        $event = $this->createEvent(
            'Enlight_Controller_Action_PostDispatch',
            'onPostDispatch'
        );
        $this->subscribeEvent($event);

        //Create the new resource Jira
        $event = $this->createEvent(
            'Enlight_Bootstrap_InitResource_Jira',
            'onInitResourceJira'
        );
        $this->subscribeEvent($event);

        $event = $this->createEvent(
            'Enlight_Controller_Dispatcher_ControllerPath_Frontend_Github',
            'onSyncGithub'
        );
        $this->subscribeEvent($event);

        $this->installSql();

        return true;
    }

    public function installSql(){
        $sql = "CREATE TABLE IF NOT EXISTS `swag_bug_github` (
          `id` int(11) NOT NULL AUTO_INCREMENT,
          `date` datetime NOT NULL,
          `author` varchar(255) NOT NULL,
          `message` varchar(255) NOT NULL,
          `url` varchar(255) NOT NULL,
          PRIMARY KEY (`id`),
          KEY `message` (`message`)
        ) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;
        ";

      Shopware()->Db()->query($sql);

      Shopware()->Db()->query("ALTER TABLE `swag_bug_github` ADD UNIQUE (
      `url`
      )");
    }

    public static function onSyncGithub()
   	{
           return dirname(__FILE__) . '/Controller/Frontend/Github.php';
    }

    /**
     * Returns the path to a helper controller that is used for commit message
     * synchronisation with github
     * @static
     * @return string
     */
    public static function onGetControllerPathFrontendJira()
    {
        return dirname(__FILE__) . '/Controller/Widgets/Jira.php';
    }

    /**
     * Global pre dispatcher
     * - Adds the local template path as a new template dir
     * @static
     * @param Enlight_Event_EventArgs $args
     */
    public static function onPreDispatch(Enlight_Event_EventArgs $args)
    {
        //Fetches the subject and request object of the controller instance
        $me = $args->getSubject();
        $request = $me->Request();

        //Setup the alternative path for the namespace Shopware_Components
        Shopware()->Loader()->registerNamespace('Shopware_Components', dirname(__FILE__).'/Components/');



        $me->View()->addTemplateDir(dirname(__FILE__).'/Views/');
        //Aborts if the current is not a frontend controller
        if(strtolower($request->getModuleName()) != 'frontend') {
            return;
        }

        //Adds the local template path as a new template dir


        //Redirects to the jira controller if index controller was called
        if(strtolower($request->getControllerName()) == 'index') {
          $me->forward('index', 'jira','widgets');

        }
    }

    /**
     * Global pre dispatcher
     * - Checks if a error was thrown
     * @static
     * @param Enlight_Event_EventArgs $args
     */
    public static function onPostDispatch(Enlight_Event_EventArgs $args)
    {
        //Fetches the subject and request object of the controller instance
        $me = $args->getSubject();
        $request = $me->Request();

        //reset the template if a error was thrown
        if(strtolower($request->getControllerName()) == 'error') {
            //$me->View()->setTemplate('frontend/jira/error.tpl');
        }
    }

    /**
     * Returns an instance of the Shopware_Components_Jira object
     * The instance will be initializes as a singleton
     *
     * @static
     * @param Enlight_Event_EventArgs $args
     * @return Shopware_Components_Jira
     */
    public static function onInitResourceJira(Enlight_Event_EventArgs $args)
    {
        return new Shopware_Components_Jira();
    }
}