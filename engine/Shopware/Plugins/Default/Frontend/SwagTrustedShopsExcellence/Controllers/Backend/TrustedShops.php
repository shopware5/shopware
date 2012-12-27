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
 */

/**
 * Shopware Backend Controller - SwagTrustedShopsExcellence
 *
 * @category  Shopware
 * @package   Shopware\Controllers\Backend
 * @copyright Copyright (c) 2012, shopware AG (http://www.shopware.de)
 */
class Shopware_Controllers_Backend_TrustedShops extends Shopware_Controllers_Backend_ExtJs
{


    /**
     * Fires when the user click on "Import buyer protection articles" button in the plugin config
     * @return void
     */
    public function testConnectionAction()
    {
        //access to the plugin configuration
        $plugin  = Shopware()->Plugins()->Frontend()->SwagTrustedShopsExcellence();
        $config = $plugin->Config();
        $message = "";

        //to import the trusted shop articles the trusted shop id must given
        if(empty($config->tsEID)) {
            $message .= $this->createMessageTag("- Es wurde keine Trusted Shop ID hinterlegt");
        }
        if(empty($config->tsWebServiceUser)) {
            $message .= $this->createMessageTag(" - Es wurde keine Trusted Shop Web Service User hinterlegt");
        }
        if(empty($config->tsWebServicePassword)) {
            $message .= $this->createMessageTag(" - Es wurde kein Trusted Shop Web Service Passwort hinterlegt");
        }

        //check login
        $loginParams = array("tsId" => $config["id"], "wsUser" => $config["user"], "wsPassword" => $config["pw"]);
        $tsDataModel = $plugin->getDataModel();

        if(!$tsDataModel->checkLogin($loginParams)) {
            $message .= $this->createMessageTag(" - Der Login bei Trusted Shop war nicht erfolgreich. Bitte überprüfen Sie Ihre Zugangsdaten.");
        }

        if(!empty($message)) {
            $this->View()->assign(array('success' => true, 'message' => $message));
            return;
        }

        $validCertifications = array("EXCELLENCE", "PRODUCTION", "INTEGRATION", "TEST");
        $certificated = $tsDataModel->checkCertificate();

        if(!in_array($certificated->stateEnum, $validCertifications)) {
            $message .= $this->createMessageTag(" - Ihr Trusted Shops Zertifikat ist nicht gültig.");
        }

        if(empty($message)) {
            $message .= $this->createMessageTag("- Die Verbindung konnte erfolgreich aufgebaut werden.");
        }

        $this->View()->assign(array('success' => true, 'message' => $message));
    }



	/**
	 * Fires when the user click on "Import buyer protection articles" button in the plugin config
	 * @return void
	 */
	public function importBuyerProtectionItemsAction()
	{
		//access to the plugin configuration
		$config = Shopware()->Plugins()->Frontend()->SwagTrustedShopsExcellence()->Config();
		$message = "";

		//to import the trusted shop articles the trusted shop id must given
		if(empty($config->tsEID)) {
			$message .= $this->createMessageTag("- Es wurde keine Trusted Shop ID hinterlegt, die wird f&uuml;r den Import der Trusted Shop Artikel ben&ouml;tigt");
		}
		//if the trusted shop id don't given, cancel import
		if(!empty($message)) {
            $this->View()->assign(array('success' => true, 'message' => $message));
            return;
		}

		//import the trusted shop buyer protection items
		$data = Shopware()->Plugins()->Frontend()->SwagTrustedShopsExcellence()->updateTrustedShopsProtectionItems();

		//check success
		if(empty($data)) {
			$message .= $this->createMessageTag(" - Die Trusted Shop Artikel konnten nicht importiert werden, bitte pr&uuml;fen Sie Ihre Trusted Shop Daten (Benutzer, Passwort, ID)");
		} else {
			$message .= $this->createMessageTag(" - Die Trusted Shop Artikel wurden importiert.");
		}

		$paramsForImageGiven = true;

		//to import the rating image the web service user and password must given.
		if(empty($config->tsWebServiceUser)) {
			$paramsForImageGiven = false;
			$message .= $this->createMessageTag(" - Es wurde keine Trusted Shop Web Service User hinterlegt, dieser wird f&uuml;r den Import des Trusted Shop Rating Images ben&ouml;tigt.");
		}
		if(empty($config->tsWebServicePassword)) {
			$paramsForImageGiven = false;
			$message .= $this->createMessageTag(" - Es wurde kein Trusted Shop Web Service Passwort hinterlegt, dieses wird f&uuml;r den Import des Trusted Shop Rating Images ben&ouml;tigt.");

		}
		//if the web service user or password don't given, cancel the image import.
		if(!$paramsForImageGiven) {
            $this->View()->assign(array('success' => true, 'message' => $message));
            return;
		}

		//import the trusted shop rating image
		$image = Shopware()->Plugins()->Frontend()->SwagTrustedShopsExcellence()->importTsRatingImage();

		//check success
		if(empty($image)) {
			$message .= $this->createMessageTag(" - Beim Import des Trusted Shop Rating Image ist jedoch ein Fehler aufgetreten, bitte pr&uuml;fen Sie Ihre Trusted Shop Daten (Benutzer, Passwort, ID)");
		} else {
			$message .= $this->createMessageTag(" - Das Trusted Shop Rating Image wurde importiert.");
		}

        $this->View()->assign(array('success' => true, 'message' => $message));
	}

    /**
     * Internal helper function to create the message tag with a highlight color
     * @param $message
     * @return string
     */
	private function createMessageTag($message)
	{
		return '<p>' . $message . '<br><br></p>';
	}

}