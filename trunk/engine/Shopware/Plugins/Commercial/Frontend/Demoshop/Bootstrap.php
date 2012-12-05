<?php
/**
 * Shopware Demoshop Plugin
 * 
 * @link http://www.shopware.de
 * @copyright Copyright (c) 2011, shopware AG
 * @author Stefan Hamann
 * @package Shopware
 * @subpackage Plugins
 */
class Shopware_Plugins_Frontend_Demoshop_Bootstrap extends Shopware_Components_Plugin_Bootstrap
{
	/**
	 * Install plugin method
	 *
	 * @return bool
	 */
	public function install()
	{
		$this->subscribeEvent($this->createEvent('Enlight_Controller_Action_PostDispatch','onPostDispatch'));
		
		$event = $this->createEvent('Enlight_Controller_Dispatcher_ControllerPath_Frontend_RegisterDemo','onGetRegisterDemo');
        $this->subscribeEvent($event);
        
        $this->subscribeEvent(
           'Enlight_Controller_Front_RouteStartup',
           'onRouteStartup',
           99
        );
         // Event to add info assets when user is in staging-backend
        $this->subscribeEvent('Enlight_Controller_Action_PostDispatch_Backend_Index','onRenderBackendIndex');

        $event = $this->createEvent(
            'Enlight_Bootstrap_InitResource_Auth',
            'onInitResourceAuth',
            -10
           );
        $this->subscribeEvent($event);
        /*
    	$event = $this->createEvent('Enlight_Controller_Dispatcher_ControllerPath_Backend_DemoBackend','onGetDemoBackend');
        $this->subscribeEvent($event);
        
        // Disable insecure plugin manager methods
        $this->subscribeHook($this->createHook('Shopware_Controllers_Backend_Plugin','saveDetailAction','permitMethod',Enlight_Hook_HookHandler::TypeBefore,0));
		$this->subscribeHook($this->createHook('Shopware_Controllers_Backend_Plugin','getDeleteListAction','permitMethod',Enlight_Hook_HookHandler::TypeBefore,0));
		$this->subscribeHook($this->createHook('Shopware_Controllers_Backend_Plugin','disableAction','permitMethod',Enlight_Hook_HookHandler::TypeBefore,0));
		$this->subscribeHook($this->createHook('Shopware_Controllers_Backend_Plugin','uninstallAction','permitMethod',Enlight_Hook_HookHandler::TypeBefore,0));
		$this->subscribeHook($this->createHook('Shopware_Controllers_Backend_Plugin','uploadAction','permitMethod',Enlight_Hook_HookHandler::TypeBefore,0));
		$this->subscribeHook($this->createHook('Shopware_Controllers_Backend_Plugin','downloadAction','permitMethod',Enlight_Hook_HookHandler::TypeBefore,0));
		$this->subscribeHook($this->createHook('Shopware_Controllers_Backend_Plugin','deleteAction','permitMethod',Enlight_Hook_HookHandler::TypeBefore,0));

		// Disable sending of newsletters
		$this->subscribeHook($this->createHook('Shopware_Controllers_Backend_Newsletter','mailAction','permitMethod',Enlight_Hook_HookHandler::TypeBefore,0));
		
		$event = $this->createEvent(
		    'Enlight_Bootstrap_InitResource_Auth',
		    'onInitResourceAuth',
		    -10
		   );
		$this->subscribeEvent($event);

		$parent = $this->Menu()->findOneBy('label', 'Marketing');
		
		

		$item = $this->createMenuItem(array(
			'label' => 'Mehr Funktionen',
			'onclick' => 'openAction(\'DemoBackend\');',
			'class' => 'ico shopware',
			'active' => 1,
			'parent' => $parent
		));
		$this->Menu()->save();
	 	
		
		$this->Menu()->addItem($item);
		
		$this->Menu()->save();
		
		Shopware()->Db()->query("
        UPDATE s_core_menu SET `parent` = 0 WHERE `name` = 'Mehr Funktionen'
        ");
		
        */
		/*
		Tasks
		- Statusbar in Demo-Frontend
		-- Anzeige Version & Datum
		-- Link zum Backend
		- Datei-Archiv sperren
		- Backend-Login anderweitig konfigurieren
		- Upload-Funktion f�r Artikeldateien sperren
		- Newsletter-Versand sperren
		- eMail-Versand auf Server konfigurieren (?)
		- Plugin-Upload sperren (?)
		- Ver�ndern von Grundeinstellungen sperren
		- Reset DB + Files
		
		- 
		*/
		return true;
	}
    public function onRenderBackendIndex(Enlight_Event_EventArgs $args){
           $view = $args->getSubject()->View();

           if (!$view->hasTemplate()){
               return;
           }

           $this->Application()->Template()->addTemplateDir(
                   $this->Path() . 'Views/'
           );

           $view->extendsTemplate("override.tpl");
     }

	 public function onRouteStartup(Enlight_Controller_EventArgs $args)
    {


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
            if (!empty($_REQUEST["sTpl"])){
            	Shopware()->Session()->sTpl = $_REQUEST["sTpl"];
            }
            if (!empty(Shopware()->Session()->sTpl)){
               $repository = 'Shopware\Models\Shop\Template';
               $repository = Shopware()->Models()->getRepository($repository);
               $template = Shopware()->Session()->sTpl;
               $template = $repository->findOneBy(array('template' => $template));
               if ($template !== null){
                   $shop->setTemplate($template);
               }else {
                   $shop->setTemplate($main->getTemplate());
               }
            }else {
                $shop->setTemplate($main->getTemplate());
            }
 			
        }
    }
	
	 /**
	  * Event listener method
	  *
	  * @param Enlight_Event_EventArgs $args
	  */
	 public function onInitResourceAuth(Enlight_Event_EventArgs $args)
	 {
        $bootstrap = $this->Application()->Bootstrap();
        $bootstrap->loadResource('BackendSession');

        $resource = Shopware_Components_Auth::getInstance();
        $adapter = new Shopware_Components_Auth_Adapter_Default();
        //$adapter->setSessionIdColumn("");

        $storage = new Zend_Auth_Storage_Session('Shopware', 'Auth');
        $resource->setBaseAdapter($adapter);
        $resource->addAdapter($adapter);
        $resource->setStorage($storage);
        $resource->login("demo", "demo");
        $user = $resource->getIdentity();
        if(!empty($user->roleID)) {
           $user->role = Shopware()->Models()->find(
                'Shopware\Models\User\Role',
                $user->roleID
            );
        }

        if($user && !isset($user->locale)) {
            $user->locale = Shopware()->Models()->getRepository(
                'Shopware\Models\Shop\Locale'
            )->find(Shopware()->Plugins()->Backend()->Auth()->getDefaultLocale());
        }
        Shopware()->Plugins()->Backend()->Auth()->registerAclPlugin($resource);

        return $resource;
	 }



	/**
	 * Enter description here...
	 *
	 * @param Enlight_Hook_HookArgs $args
	 */
	public static function permitMethod(Enlight_Hook_HookArgs $args)
	{
		if (!in_array($_SERVER["REMOTE_ADDR"],array("80.152.242.183","217.92.126.57","87.139.198.184"))){
			throw new Enlight_Exception ("This part of shopware is disabled in public demo!");
		}
	}
	
	/**
	 * Enter description here...
	 *
	 * @param Enlight_Event_EventArgs $args
	 */
	public static function onGetRegisterDemo(Enlight_Event_EventArgs $args){
		
		return dirname(__FILE__).'/RegisterDemo.php';
	}
	
	public static function onGetDemoBackend(Enlight_Event_EventArgs $args){
		
		return dirname(__FILE__).'/DemoBackend.php';
	}
	
	/**
	 * Dispatcher to inject tiptip js (common jquery tooltip plugin)
	 *
	 * @param Enlight_Event_EventArgs $args
	 */
	public static function onPostDispatch(Enlight_Event_EventArgs $args){
		$request = $args->getSubject()->Request();
		$response = $args->getSubject()->Response();
		if ($request->getModuleName() == 'backend' && $request->getControllerName() == "index"){
			
			
			if (strpos($_SERVER['HTTP_USER_AGENT'], 'MSIE') !== false){
				$args->getSubject()->forward('browserError','DemoBackend');
			}
		}
		if(!$request->isDispatched() 
			|| $response->isException() 
			|| $request->getModuleName() != 'frontend' 
			) {
			return;
		}
		$args->getSubject()->View()->addTemplateDir(dirname(__FILE__)."/Views/");
		$args->getSubject()->View()->extendsTemplate(dirname(__FILE__).'/Views/plugin.tpl');
	}
}