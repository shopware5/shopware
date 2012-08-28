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
 * @package    Shopware_Controllers
 * @subpackage OrderState
 * @copyright  Copyright (c) 2012, shopware AG (http://www.shopware.de)
 * @version    $Id$
 * @author     Stefan Hamann
 * @author     $Author$
 */

/**
 * Order state controller
 *
 * todo@all: Documentation
 */
class Shopware_Controllers_Backend_OrderState extends Enlight_Controller_Action
{
	/**
	 * Init controller method
	 */
	public function init()
	{
		Shopware()->Plugins()->Controller()->ViewRenderer()->setNoRender();
	}
	
	/**
	 * Read action method
	 */
	public function readAction()
	{
		$orderId = $this->Request()->id;
		$statusId = $this->Request()->status;
		$template = $this->Request()->mailtype;
		
		$mail = Shopware()->Modules()->Order()->createStatusMail($orderId, $statusId, $template);
		
		if(!empty($mail)) {
			$ret = array(
				"content" => utf8_encode($mail->getPlainBodyText()),
				"subject" => utf8_encode($mail->getSubject()),
				"email" => utf8_encode(implode(', ', $mail->getTo())),
				"frommail" => utf8_encode($mail->getFrom()),
				"fromname" => utf8_encode($mail->getFromName())
			);
			echo Zend_Json::encode($ret);
		} else {
			echo 'FAIL';
		}
	}
	
	/**
	 * Send action method
	 */
	public function sendAction()
	{
		$mail = clone Shopware()->Mail();
		
		$mail->clearRecipients();
		
		$mail->setSubject($this->Request()->subject);
		$mail->setBodyText($this->Request()->content);
		$mail->setFrom($this->Request()->frommail, $this->Request()->fromname);
		$mail->addTo($this->Request()->email);

		if (!Shopware()->Modules()->Order()->sendStatusMail($mail)){
			echo 'FAIL';
		}
	}
}