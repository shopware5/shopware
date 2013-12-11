<?php
/**
 * Shopware 4
 * Copyright © shopware AG
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
 * Export controller
 *
 * This controller is used by the ProductFeed modul.
 * The ProductFeed modul will call this controller to export the chosen ProductFeed with all options.
 * The controller uses the base class sExport for all export relevant methods.
 * Sets a different header to return a downloadable export file.
 */
class Shopware_Controllers_Backend_Export extends Enlight_Controller_Action
{
	/**
	 * Init controller method
	 *
	 * Disables the authorization-checking and template renderer.
	 */
	public function init()
	{
		Shopware()->Plugins()->Backend()->Auth()->setNoAuth();
		Shopware()->Plugins()->Controller()->ViewRenderer()->setNoRender();
		$this->Front()->setParam('disableOutputBuffering', true);
		$this->Front()->returnResponse(true);
	}



	/**
	 * Index action method
	 *
	 * Creates the export product.
	 */
	public function indexAction()
	{
        /**
         * initialize the base class sExport
         */
		$export = Shopware()->Modules()->Export();
		$export->sSystem = Shopware()->System();
        $export->request = $this->Request();
        $export->sFeedID = (int) $this->Request()->feedID;
        $export->sHash = $this->Request()->hash;
		$export->sDB = Shopware()->Adodb();

		$export->sInitSettings();

		/**
         * initialize smarty
         */
		$export->sSmarty = $this->View()->Engine();
		$export->sInitSmarty();

        /**
         * set feed specific options to the export and sets
         * the right header
         */
		if(!empty($export->sSettings['encodingID'])&&$export->sSettings['encodingID']==2) {
			if(!empty($export->sSettings['formatID'])&&$export->sSettings['formatID']==3) {
				$this->Response()->setHeader('Content-Type', 'text/xml;charset=utf-8');
			} else {
				$this->Response()->setHeader('Content-Type', 'text/x-comma-separated-values;charset=utf-8');
			}
		} else {
			if(!empty($export->sSettings['formatID'])&&$export->sSettings['formatID']==3) {
				$this->Response()->setHeader('Content-Type', 'text/xml;charset=iso-8859-1');
			} else {
				$this->Response()->setHeader('Content-Type', 'text/x-comma-separated-values;charset=iso-8859-1');
			}
		}
        $this->Response()->sendHeaders();
        $export->sSmarty->display('string:'.$export->sSettings['header'], $export->sFeedID);

		$sql = $export->sCreateSql();

		$result = Shopware()->Db()->query($sql);

        if($result===false) {
			return;
		}

        // updates the db with the latest informations
		$count = (int) $result->rowCount();
		$sql = 'UPDATE s_export SET last_export=NOW(), count_articles=? WHERE id=?';
		Shopware()->Db()->query($sql, array($count, $export->sFeedID));

        // fetches all required data to smarty
		$rows = array();
		for ($rowIndex=1; $row = $result->fetch(); $rowIndex++) {

			if(!empty($row['group_ordernumber_2'])) {
				$row['group_ordernumber'] = $export->_decode_line($row['group_ordernumber_2']);
				$row['group_pricenet'] = explode(';',$row['group_pricenet_2']);
				$row['group_price'] = explode(';',$row['group_price_2']);
				$row['group_instock'] = explode(';',$row['group_instock_2']);
				$row['group_active'] = explode(';',$row['group_active_2']);
				unset($row['group_ordernumber_2'], $row['group_pricenet_2']);
				unset($row['group_price_2'], $row['group_instock_2'], $row['group_active_2']);
				for ($i=1;$i<=10;$i++) {
					if(!empty($row['group_group'.$i])) {
						$row['group_group'.$i] = $export->_decode_line($row['group_group'.$i]);
					} else  {
						unset($row['group_group'.$i]);
					}
					if(!empty($row['group_option'.$i])) {
						$row['group_option'.$i] = $export->_decode_line($row['group_option'.$i]);
					}else {
						unset($row['group_option'.$i]);
					}
				}
				unset($row['group_additionaltext']);
			} elseif(!empty($row['group_ordernumber'])) {
				$row['group_ordernumber'] = $export->_decode_line($row['group_ordernumber']);
				$row['group_additionaltext'] = $export->_decode_line($row['group_additionaltext']);
				$row['group_pricenet'] = explode(';', $row['group_pricenet']);
				$row['group_price'] = explode(';', $row['group_price']);
				$row['group_instock'] = explode(';', $row['group_instock']);
				$row['group_active'] = explode(';', $row['group_active']);
			}
			if (!empty($row['article_translation'])) {
				$translation = $export->sMapTranslation('article', $row['article_translation']);
				$row = array_merge($row, $translation);
			}
            else if (!empty($row['article_translation_fallback'])) {
                $translation = $export->sMapTranslation('article', $row['article_translation_fallback']);
                $row = array_merge($row, $translation);
            }
			if (!empty($row['detail_translation'])) {
				$translation = $export->sMapTranslation('detail', $row['detail_translation']);
				$row = array_merge($row, $translation);
			}
            else if (!empty($row['detail_translation_fallback'])) {
				$translation = $export->sMapTranslation('detail', $row['detail_translation_fallback']);
				$row = array_merge($row, $translation);
			}
			$row['name'] = htmlspecialchars_decode($row['name']);
			$row['supplier'] = htmlspecialchars_decode($row['supplier']);

            //cast it to float to prevent the devision by zero warning
            $row['purchaseunit'] = floatval($row['purchaseunit']);
            $row['referenceunit'] = floatval($row['referenceunit']);
			if(!empty($row['purchaseunit']) && !empty($row['referenceunit'])) {
                $row['referenceprice'] = Shopware()->Modules()->Articles()->calculateReferencePrice($row['price'], $row['purchaseunit'], $row['referenceunit']);
			}

			$rows[] = $row;


			if($rowIndex==$count || count($rows)>=50) {

				@set_time_limit(30);

				$export->sSmarty->assign('sArticles', $rows);
				$rows = array();

				$template = 'string:{foreach $sArticles as $sArticle}' . $export->sSettings['body'] . '{/foreach}';

				$export->sSmarty->display($template, $export->sFeedID);
			}
		}

		$export->sSmarty->display('string:'.$export->sSettings['footer'], $export->sFeedID);
	}
}
