<?php
class Shopware_Controllers_Backend_HeidelContact extends Enlight_Controller_Action
{

	public function init(){/*{{{*/
		$this->View()->addTemplateDir(dirname(__FILE__)."/Views/");
	}/*}}}*/

  public function indexAction(){/*{{{*/
    $url = 'http://testshops.heidelpay.de/contactform/?campaign=shopware&shop=shopware';
    $this->View()->loadTemplate("backend/plugins/HeidelContact/index.tpl");
    $this->View()->HPUrl = $url;
  }/*}}}*/

	public function skeletonAction(){/*{{{*/
		$this->View()->loadTemplate("backend/plugins/HeidelContact/skeleton.tpl");
  }/*}}}*/ 

}
?>
