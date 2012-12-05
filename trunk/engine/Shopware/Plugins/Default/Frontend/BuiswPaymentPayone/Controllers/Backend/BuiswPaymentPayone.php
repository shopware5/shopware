<?php

/*
  ##############################################################################
  # Plugin for Shopware
  # ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
  # @version $Id$
  # @copyright:   found in /lic/copyright.txt
  #
  ##############################################################################
 */

class Shopware_Controllers_Backend_BuiswPaymentPayone extends Enlight_Controller_Action {

	public function init() {
		$this->View()->addTemplateDir(dirname(__FILE__) . '/../../Views/Backend/');;
	}
    
    
//    public function preDispatch() {
//        if (in_array($this->Request()->getActionName(), array(
//            'skeleton', 
//            'getTransactionsLogs', 
//            'getApiLogs',
//            'loadTransactionsFormData',
//            'loadFormData',
//            'configForm',
//            'assignCountriesStoresEct',
//            'assignCountries',
//            'assignShops',
//            'assignGroups',
//            'logs',
//            'transactions',
//            'config',
//            'saveConfig',
//            'saveAssignments',
//            'captureAmount',
//            'refundAmount',
//            'test'
//         ))) {
//            $this->Front()->Plugins()->Json()->setRenderer(false);
//            $this->Front()->Plugins()->ViewRenderer()->setNoRender();
//        }
//    }

	public function skeletonAction() {
        $this->View()->setTemplate();
		$action = $this->Request()->getParam('target_action');
        
		$this->View()->assign('action', $action);
		if ($action == 'Config') {
			$this->View()->assign('actionTitle', 'Konfiguration');
		}
		if ($action == 'Logs') {
			$this->View()->assign('actionTitle', 'Protokolle');
		}
		if ($action == 'Transactions') {
			$this->View()->assign('actionTitle', 'Transaktionen');
		}
		if ($action == 'assignCountriesStoresEct') {
			$this->View()->assign('actionTitle', 'L&auml;nderzuordnung');
		}
		$key = $this->Request()->getParam('key');

		if ($key) {
			$this->View()->assign('key', $key);
			$this->View()->assign('text', $this->findTextFromKey($key));
		}
		if ($action == 'Orders') {
            $basepath = Shopware()->AppPath();
            
            $tmp = explode('/engine/', $basepath);
            $basepath = 'engine/'.$tmp[1];
            
			$this->View()->assign('path', urlencode(Shopware()->AppPath()));
			$this->View()->assign('basepath', urlencode($basepath));
			$this->View()->assign('shopdir', urlencode($basepath));
			$this->View()->loadTemplate('skeleton_orders.tpl');
		} elseif($action == "Informations") {
			$this->View()->loadTemplate('skeleton_informations.tpl');
//			$this->View()->loadTemplate('skeleton.tpl');
		} else {
			$this->View()->loadTemplate('skeleton.tpl');
		}
	}

	public function getTransactionsLogsAction() {
		Shopware()->Plugins()->Controller()->ViewRenderer()->setNoRender();

		$log = new payone_TransactionLogs();

		$this->getLogsAction(array ('id', 'occoured', 'order_number', 'transaction_no', 'paymethod', 'customer_email', 'amount', 'status'), $log, '`status` !="APPROVED"');
	}

	public function getApiLogsAction() {
		Shopware()->Plugins()->Controller()->ViewRenderer()->setNoRender();


		$log = new payone_ApiLogs();

		$this->getLogsAction(array ('id', 'occoured', 'request', 'response', new Zend_Db_Expr("IF( api = 'c', 'Clientapi', 'Serverapi' ) AS api")), $log);
	}

	protected function getLogsAction(array $cols, payone_tableBasics $logTable, $initialWhere = null) {
		try {

			$select = $logTable->select()->columns($cols);
			if ($initialWhere) {
				$select->where ($initialWhere);
			}
            $searchTerm = $this->Request()->getParam('search');
			if (!is_null($searchTerm)) {
				$searchin = $logTable->tableColumns();
				foreach ($searchin as & $s) {
					$s = "$s like ?";
				}
				$where = join(' or ', $searchin);

				$select->where($where, '%' . $searchTerm . '%');
			}
			$count = $logTable->count(join (' ', $select->getPart ('where')));
			
            $start          = (int)$this->Request()->getParam('start', 25);
            $limit          = (int)$this->Request()->getParam('limit', 0);
			$select->limit($limit, $start);
			
			$select->order("occoured DESC");

			$result = Shopware()->Db()->fetchAll($select);

		} catch (Exception $e) {
			$result = array ();
			$count = 0;
		}

		$return = array ('count' => $count, 'data' => $result);

		echo json_encode($return);
	}

	public function loadTransactionsFormDataAction() {
		Shopware()->Plugins()->Controller()->ViewRenderer()->setNoRender();
        $id = $this->Request()->getParam('id');
		$log = new payone_TransactionLogs();
		$result = $log->fetchRow(
						$log->select()->columns('data_array')->where('id=?', $id)
		);
		$result = $result['data_array'];

		if ($result != null && $result != '') {
			$result = unserialize($result);
			$resultCount = count($result);
		}

		$html = '<style type="text/css">';
		$html .= '.zeile0{background-color:#D6E4F5;}';
		$html .= '.zeile1{background-color:#C2D6F1;}';
		$html .= '.container{font-family:Verdana;font-size:12px;}';
		$html .= '</style>';

		// History
		$html .= '<div class="container" style="padding:10px;">';
		//$html .= '<h1>History' . ($resultCount ? " ($resultCount)" : '') . '</h1>';
		//$html .= "<br />";
		foreach ($result as $result) {
			echo '<h2>' . $result['occoured'] . '</h2>';
			$html .= '<table>';

			$i = 0;

			foreach ($result as $key => $val) {
				if ($key == 'occoured') continue;
				$html .= "<tr>";
				$html .= '<td style="padding:5px;" class="zeile' . ($i % 2 ) . '">' . $key . '</td>';
				$html .= '<td style="padding:5px;" class="zeile' . ($i % 2 ) . '">' . $val . '</td>';
				$html .= "</tr>";

				$i++;
			}

			$html .= "</table>";
			$html .= "<br /><br />";
		}

		$html .= "</div>";

		echo $html;
		exit;
	}

	public function loadFormDataAction() {
		Shopware()->Plugins()->Controller()->ViewRenderer()->setNoRender();
		new Enlight_Loader();
		$log = new payone_ApiLogs();
		try {
            $id = $this->Request()->getParam('id');
			$result = $log->fetchRow(
							$log->select()->columns(array ('request_data', 'response_data'))->where('id=?', $id)
			);
		} catch (Exception $e) {
		}

		$result['request_data'] = unserialize($result['request_data']);
		$result['response_data'] = unserialize($result['response_data']);


		$html = '<style type="text/css">';
		$html .= '.zeile0{background-color:#D6E4F5;}';
		$html .= '.zeile1{background-color:#C2D6F1;}';
		$html .= '.container{font-family:Verdana;font-size:12px;}';
		$html .= '</style>';

		// Request
		$html .= '<div class="container" style="padding:10px;">';
		$html .= '<table style="float:left; margin-right:100px;">';
		$html .= "<tr>";
		$html .= '<th colspan=2>Request</th>';
		$html .= "</tr>";

		$i = 0;

		foreach ($result['request_data'] as $key => $val) {
			$html .= "<tr>";
			$html .= '<td style="padding:5px;" class="zeile' . ($i % 2 ) . '">' . $key . '</td>';
			$html .= '<td style="padding:5px;" class="zeile' . ($i % 2 ) . '">' . $val . '</td>';
			$html .= "</tr>";

			$i++;
		}

		$html .= "</table>";

		// Response
		$html .= '<table style="float:left;">';
		$html .= "<tr>";
		$html .= '<th colspan=2>Response</th>';
		$html .= "</tr>";

		$i = 0;

		foreach ($result['response_data'] as $key => $val) {
			$html .= "<tr>";
			$html .= '<td style="padding:5px;" class="zeile' . ($i % 2 ) . '">' . $key . '</td>';
			$html .= '<td style="padding:5px;" class="zeile' . ($i % 2 ) . '">' . $val . '</td>';
			$html .= "</tr>";

			$i++;
		}

		$html .= "</table>";

		$html .= "</div>";

		echo $html;
		exit;
	}

	protected function getPaystates() {
		$sql = 'select id,description from s_core_states where `group`="payment" order by position,description';
		$db = Shopware()->Db();
		$res = $db->fetchAll($sql);
		$data = array ();
		$value = null;
		foreach ($res as $d) {
			if (!$selected) {
				$selected = $d['id'];
			}
			$descr = addslashes($d['description']) . '|' . $d['id'];
			if ($selected == $d['id']) {
				$value = $descr;
			}
			$data[] = '[' . $d['id'] . ',"' . $descr . '"]';
		}
		return '[' . join(',', $data) . ']';
	}

	public function configFormAction() {
		$this->View()->loadTemplate('configForm.tpl');
	}

	public function assignCountriesStoresEctAction() {
		$key = $this->Request()->getParam('key');
		$this->View()->assign('key', $key);
		$this->View()->assign('text', $this->findTextFromKey($key));
		$this->View()->loadTemplate('assignCountries.tpl');
	}

	public function assignCountriesAction() {
		Shopware()->Plugins()->Controller()->ViewRenderer()->setNoRender();
        $get_assigned = $this->Request()->getParam('assigned') == 1;
        $key          = $this->Request()->getParam('key');
        $data         = array();
        $assignments  = $this->getAssignments();
		foreach ($this->getCounties() as $id => $c) {
			if (($get_assigned && $assignments[$key]['c'][$id])
							|| (!$get_assigned && !$assignments[$key]['c'][$id])
			) {
				$data[] = array ('id' => $id, 'country' => $c);
			}
		}
		echo json_encode(array ('count' => count($data), 'data' => $data));
	}

	public function assignShopsAction() {
		Shopware()->Plugins()->Controller()->ViewRenderer()->setNoRender();
		$get_assigned = $this->Request()->getParam('assigned') == 1;
        $key          = $this->Request()->getParam('key');
		$data = array ();
		$assignments = $this->getAssignments();

		foreach ($this->getShops() as $id => $c) {
			if (($get_assigned && $assignments[$key]['s'][$id])
							|| (!$get_assigned && !$assignments[$key]['s'][$id])
			) {
				$data[] = array ('id' => $id, 'shop' => $c);
			}
		}
		echo json_encode(array ('count' => count($data), 'data' => $data));
	}

	public function assignGroupsAction() {
		Shopware()->Plugins()->Controller()->ViewRenderer()->setNoRender();
		$get_assigned = $this->Request()->getParam('assigned') == 1;
        $key          = $this->Request()->getParam('key');
		$data = array ();
		$assignments = $this->getAssignments();

		foreach ($this->getGroups() as $id => $c) {
			if (($get_assigned && $assignments[$key]['g'][$id])
							|| (!$get_assigned && !$assignments[$key]['g'][$id])
			) {
				$data[] = array ('id' => $id, 'group' => $c);
			}
		}
		echo json_encode(array ('count' => count($data), 'data' => $data));
	}

	protected static $groups = null;

	protected function getGroups() {
		if (self::$groups === null) {
			self::$groups = array ();
			$sql = 'SELECT id,description FROM `s_core_customergroups` ORDER BY `description`';
			$res = Shopware()->Db()->fetchAll($sql);
			foreach ($res as $r) {
				self::$groups[$r['id']] = $r['description'];
			}
		}
		return self::$groups;
	}

	protected static $shops = null;

	protected function getShops() {
		if (self::$shops === null) {
			self::$shops = array ();
			$sql = 'SELECT id,name FROM `s_core_multilanguage` ORDER BY `name`';
			$res = Shopware()->Db()->fetchAll($sql);
			foreach ($res as $r) {
				self::$shops[$r['id']] = $r['name'];
			}
		}
		return self::$shops;
	}

	protected static $countries = null;

	protected function getCounties() {
		if (self::$countries === null) {
			self::$countries = array ();
			$sql = 'SELECT id,countryname FROM `s_core_countries` ORDER BY `position` , `areaID` , `countryname`';
			$res = Shopware()->Db()->fetchAll($sql);
			foreach ($res as $r) {
				self::$countries[$r['id']] = $r['countryname'];
			}
		}
		return self::$countries;
	}

	protected static $assignments = null;

	protected function getAssignments() {
		if (self::$assignments === null) {

			self::$assignments = array ();
			$sql = 'SELECT * from ' . Shopware_Plugins_Frontend_buiswPaymentPayone_Bootstrap::TABLE_ASSIGNMENTS;
			$res = Shopware()->Db()->fetchAll($sql);
			foreach ($res as $r) {
				self::$assignments[$r['key']][$r['allow_type']][$r['allowed_pk']] = true;
			}
		}
		return self::$assignments;
	}

	public function logsAction() {
		$this->View()->loadTemplate('logs.tpl');
	}

	public function transactionsAction() {
		$this->View()->loadTemplate('transactions.tpl');
	}

	static public $creditcards = array (
			array (
					'text' => 'Visa',
					'key' => 'visa',
					'value' => 'V'
			),
			array (
					'text' => 'Mastercard',
					'key' => 'mastercard',
					'value' => 'M'
			),
			array (
					'text' => 'American Express',
					'key' => 'amex',
					'value' => 'A'
			),
			array (
					'text' => 'Diners Club',
					'key' => 'diners',
					'value' => 'D'
			),
			array (
					'text' => 'JCB',
					'key' => 'jcb',
					'value' => 'J'
			),
			array (
					'text' => 'Maestro International',
					'key' => 'maestro_int',
					'value' => 'O'
			),
			array (
					'text' => 'Discover',
					'key' => 'discover',
					'value' => 'C'
			),
			array (
					'text' => 'Carte Bleue',
					'key' => 'carte_bleue',
					'value' => 'B'
			)
	);
	static public $eps_values = array (
			array (
					'text' => 'Volksbanken',
					'key' => 'ARZ_OVB'
			),
			array (
					'text' => 'Bank f&uuml;r &Auml;rzte und Freie Berufe',
					'key' => 'ARZ_BAF'
			),
			array (
					'text' => 'Nieder&ouml;sterreichische Landes-Hypo',
					'key' => 'ARZ_NLH'
			),
			array (
					'text' => 'Vorarlberger Landes-Hypo',
					'key' => 'ARZ_VLH'
			),
			array (
					'text' => 'Bankhaus Carl Sp&auml;ngler & Co. AG',
					'key' => 'ARZ_BCS'
			),
			array (
					'text' => 'Hypo Tirol',
					'key' => 'ARZ_HTB'
			),
			array (
					'text' => 'Hypo Alpe Adria',
					'key' => 'ARZ_HAA'
			),
			array (
					'text' => 'Investkreditbank',
					'key' => 'ARZ_IKB'
			),
			array (
					'text' => '&Ouml;sterreichische Apothekerbank',
					'key' => 'ARZ_OAB'
			),
			array (
					'text' => 'Immobank',
					'key' => 'ARZ_IMB'
			),
			array (
					'text' => 'G&auml;rtnerbank',
					'key' => 'ARZ_GRB'
			),
			array (
					'text' => 'HYPO Investment',
					'key' => 'ARZ_HIB'
			),
			array (
					'text' => 'Bank Austria',
					'key' => 'BA_AUS'
			),
			array (
					'text' => 'BAWAG',
					'key' => 'BAWAG_BWG'
			),
			array (
					'text' => 'PSK Bank',
					'key' => 'BAWAG_PSK'
			),
			array (
					'text' => 'easybank',
					'key' => 'BAWAG_ESY'
			),
			array (
					'text' => 'Sparda Bank',
					'key' => 'BAWAG_SPD'
			),
			array (
					'text' => 'Erste Bank',
					'key' => 'SPARDAT_EBS'
			),
			array (
					'text' => 'Bank Burgenland',
					'key' => 'SPARDAT_BBL'
			),
			array (
					'text' => 'Raiffeisen',
					'key' => 'RAC_RAC'
			),
			array (
					'text' => 'Hypo Ober&ouml;sterreich',
					'key' => 'HRAC_OOS'
			),
			array (
					'text' => 'Hypo Salzburg',
					'key' => 'HRAC_SLB'
			),
			array (
					'text' => 'Hypo Steiermark',
					'key' => 'HRAC_STM'
			)
	);
	static public $idl_values = array (
			array (
					'text' => 'ABN Amro',
					'key' => 'ABN_AMRO_BANK'
			),
			array (
					'text' => 'Rabobank',
					'key' => 'RABOBANK'
			),
			array (
					'text' => 'Friesland Bank',
					'key' => 'FRIESLAND_BANK'
			),
			array (
					'text' => 'ASN Bank',
					'key' => 'ASN_BANK'
			),
			array (
					'text' => 'SNS Bank',
					'key' => 'SNS_BANK'
			),
			array (
					'text' => 'Troodos',
					'key' => 'TRIODOS_BANK'
			),
			array (
					'text' => 'SNS Regio Bank',
					'key' => 'SNS_REGIO_BANK'
			),
			array (
					'text' => 'ING',
					'key' => 'ING_BANK'
			)
	);
	static public $directdebits = array (
			array (
					'text' => 'Sofort-&Uuml;berweisung',
					'key' => 'sofort',
					'value' => 'PNT'
			),
			array (
					'text' => 'giropay',
					'key' => 'giropay',
					'value' => 'GPY'
			),
			array (
					'text' => 'eps - Online-&Uuml;berweisung',
					'key' => 'eps',
					'value' => 'EPS'
			),
			array (
					'text' => 'PostFinance E-Finance',
					'key' => 'post_e',
					'value' => 'PFF'
			),
			array (
					'text' => 'PostFinance Card',
					'key' => 'post_c',
					'value' => 'PFC'
			),
			array (
					'text' => 'iDeal',
					'key' => 'ideal',
					'value' => 'IDL'
			)
	);
	static public $otherPayments = array (
			array (
					'text' => 'PayPal',
					'key' => 'paypal'
			),
			array (
					'text' => 'Lastschrift',
					'key' => 'lastschrift'
			),
			array (
					'text' => 'Rechnung',
					'key' => 'rechnung'
			),
			array (
					'text' => 'Vorkasse',
					'key' => 'vorkasse'
			),
			array (
					'text' => 'Nachnahme',
					'key' => 'nachnahme'
			),
	);

	protected function findTextFromKey($k) {
		foreach (array_merge(self::$creditcards, self::$directdebits, self::$otherPayments) as $c) {
			if ($c['key'] == $k)
				return $c['text'];
		}
		return '';
	}

	protected function configActionHelper(& $c) {
		$active = $c['key'] . '_active';
		$mode = $c['key'] . '_mode';
		$auth = $c['key'] . '_authmethod';
		$ampelwert = $c['key'] . '_ampelwert';
		foreach (self::$textfields as $t) {
			$c[$t] = $this->Config($c['key'] . "_$t");
		}
		$c['active'] = $this->Config($active) == 'on' ? 'true' : 'false';
		$c[$this->Config($mode)] = 'true';
		$c['authmethod'] = $this->Config($auth) == 'auth' ? 'auth' : 'preauth';
		$c['ampelwert'] = $this->Config($ampelwert);

	}

	protected static $textfields = array ('sortorder', 'cost_total', 'cost_percent', 'cost_country', 'boniscore');
	protected static $paystati = array ('paystatus_acctepted',
			'paystatus_appointed',
			'paystatus_capture',
			'paystatus_paid',
			'paystatus_underpaid',
			'paystatus_cancelation',
			'paystatus_refund',
			'paystatus_debit',
			'paystatus_reminder',
			'paystatus_approved',
	);

	public function configAction() {
        $this->View()->setTemplate();
		foreach (self::$creditcards as & $c) {
			$this->configActionHelper($c);
		}
		foreach (self::$directdebits as & $c) {
			$this->configActionHelper($c);
		}
		foreach (self::$otherPayments as & $c) {
			$this->configActionHelper($c);
		}
		$this->View()->assign('creditcards', self::$creditcards);
		$this->View()->assign('directdebits', self::$directdebits);        
		$this->View()->assign('otherpayments', self::$otherPayments);
        
		$this->View()->assign('config', $this->Config());
		$this->View()->assign('paystates', $this->getPaystates());
        
		foreach (self::$paystati as $key) {
			if ($paystatus = $this->Config($key)) {
				$paystatus = explode('|', $paystatus);
				$paystatus = (int) $paystatus[count($paystatus) - 1];
			} else {
				$paystatus = 0;
			}
			$this->View()->assign($key, $paystatus);
		}
        
		$this->view->loadTemplate('config.tpl');
	}

	public function saveConfigAction() {
		Shopware()->Plugins()->Controller()->ViewRenderer()->setNoRender();
        $applyNewAddress = $this->Request()->getParam('applynewaddress',false);
        $onAddressCheckErrorRedirect = $this->Request()->getParam('onaddresscheckerrorredirect',false);
        $data = $this->Request()->getParams();
		foreach ($data as $k => $v) {
			if (strstr($k, '_authmethod')) {
				$v = ($v === 'Authorisierung') ? 'auth' : 'preauth';
			}

			if (strstr($k, '_ampelwert')) {
				switch($v) {
					case 'Gruen':
						$v = "G";
						break;
					case 'Gelb':
						$v = "Y";
						break;
					case 'Rot';
						$v = "R";
						break;
				}
			}

			$this->Config($k, $v);
		}

		foreach (array_merge(self::$creditcards, self::$directdebits, self::$otherPayments) as $c) {
			$active = $c['key'] . '_active';
            $activeFlagCheck = $this->Request()->getParam($active,false);
			if (!$activeFlagCheck) {
				$this->Config($active, 'off');
			}
		}
	}

	public function saveAssignmentsAction() {
		Shopware()->Plugins()->Controller()->ViewRenderer()->setNoRender();
		$key = $this->Request()->getParam('key');
		$countries = $this->Request()->getParam('countries', array()); 
		$shops = $this->Request()->getParam('shops',array()); 
		$groups = $this->Request()->getParam('groups',array()); 
		if (count($countries) > 0) {
			$sql = 'delete from ' . Shopware_Plugins_Frontend_buiswPaymentPayone_Bootstrap::TABLE_ASSIGNMENTS . ' where `key`=? and allow_type="c"';
			Shopware()->Db()->query($sql, array($key));
			foreach ($countries as $c) {
				$vals[] = '("' . $key . '", "c", ' . $c . ')';
			}
			$sql = 'insert into ' . Shopware_Plugins_Frontend_buiswPaymentPayone_Bootstrap::TABLE_ASSIGNMENTS . '(`key`,allow_type,allowed_pk) values ' .
							join(',', $vals);

			Shopware()->Db()->exec($sql);
		}

		if (count($shops) > 0) {
			$sql = 'delete from ' . Shopware_Plugins_Frontend_buiswPaymentPayone_Bootstrap::TABLE_ASSIGNMENTS . ' where `key`=?" and allow_type="s"';
			Shopware()->Db()->query($sql, array($key));

			foreach ($shops as $s) {
				$vals[] = '("' . $key . '", "s", ' . $s . ')';
			}
			$sql = 'insert into ' . Shopware_Plugins_Frontend_buiswPaymentPayone_Bootstrap::TABLE_ASSIGNMENTS . '(`key`,allow_type,allowed_pk) values ' .
							join(',', $vals);

			Shopware()->Db()->exec($sql);
		}

		if (count($groups) > 0) {
			$sql = 'delete from ' . Shopware_Plugins_Frontend_buiswPaymentPayone_Bootstrap::TABLE_ASSIGNMENTS . ' where `key`=? and allow_type="g"';
			Shopware()->Db()->query($sql, array($key));

			foreach ($groups as $s) {
				$vals[] = '("' . $key . '", "g", ' . $s . ')';
			}
			$sql = 'insert into ' . Shopware_Plugins_Frontend_buiswPaymentPayone_Bootstrap::TABLE_ASSIGNMENTS . '(`key`,allow_type,allowed_pk) values ' .
							join(',', $vals);

			Shopware()->Db()->exec($sql);
		}
	}

	private static $config = null;

	public function Config($k = null, $v = null) {
		if (self::$config === null) {
			self::$config = array ();
			$sql = 'select * from ' . Shopware_Plugins_Frontend_buiswPaymentPayone_Bootstrap::TABLE_CONFIG;
			$res = Shopware()->Db()->fetchAll($sql);
			foreach ($res as $r) {
				self::$config[$r['key']] = $r['val'];
			}
			foreach (array_merge(self::$creditcards, self::$directdebits, self::$otherPayments) as $c) {
				$active = $c['key'] . '_active';
				$mode = $c['key'] . '_mode';
				if (!array_key_exists($active, self::$config)) {
					self::$config[$active] = 'on';
				}
				if (!array_key_exists($mode, self::$config)) {
					self::$config[$mode] = 'test';
				}
			}
			if (!array_key_exists('send_cart', self::$config)) {
				self::$config['send_cart'] = 'off';
			}
			if (!array_key_exists('bonitaets_mode', self::$config)) {
				self::$config['bonitaets_mode'] = 'test';
			}
			if (!array_key_exists('bonitaets_type', self::$config)) {
				self::$config['bonitaets_type'] = 'NO';
			}
			if (!array_key_exists('bonitaets_lifetime', self::$config)) {
				self::$config['bonitaets_lifetime'] = '30';
			}
			if (!array_key_exists('bonitaets_minbasketvalue', self::$config)) {
				self::$config['bonitaets_minbasketvalue'] = '100';
			}
			if (!array_key_exists('bonitaets_defaultindex', self::$config)) {
				self::$config['bonitaets_defaultindex'] = '250';
			}
			if (!array_key_exists('addresscheck_type', self::$config)) {
				self::$config['addresscheck_type'] = 'NO';
			}
		}
		if (!$k) {
			return self::$config;
		}
		if ($v !== null) {
			$sql = 'insert into ' . Shopware_Plugins_Frontend_buiswPaymentPayone_Bootstrap::TABLE_CONFIG .
							'(`key`,`val`) values (?,?) on duplicate key update `val`=?';
			Shopware()->Db()->query($sql, array($k, $v, $v));
			self::$config[$k] = $v;
		}
		return self::$config[$k];
	}


	/*
	 * ab hier capture && refund
	 */


	protected function checkAmmount ($a) {
		$amount = str_replace (',', '.', trim ($a));
		if (! $amount || ! preg_match ('/^([0-9]+)(\.?([0-9]+))?$/', $amount)) {
			$return->error = "Bitte geben Sie einen gültigen Betrag an.";

			return $return;
		}
		$amount = (int) ($amount * 100);
		return $amount;
	}

	public function captureAmountAction() {
		Shopware()->Plugins()->Controller()->ViewRenderer()->setNoRender();


		$return = new stdClass();
		$amount = $this->checkAmmount ($this->Request()->getParam('amount'));
		if (is_object ($amount)) {
			echo json_encode($amount);
			exit (0);
		}

		$order_number = (int) $this->Request()->getParam('oID');
		$row = Shopware()->Db()->fetchRow('select transactionID,userID from s_order where ordernumber="' . $order_number . '"');
		$txid = $row['transactionID'];

		//$uid = Shopware()->Session()->sUserId = $row['userID'];

		try {
			$translog = new payone_TransactionLogs();
			$s = $translog->select()->columns(array ('id','api_log_id','amount','currency', 'mode', 'data_array'));
			$s->where('transaction_no=?', $txid);
			$transLogEntry = $translog->fetchRow($s);


			$mode = $transLogEntry['mode'];

			$captureAndRefund = new payone_CaptureAndRefund();

			$nextSequenceNumber = $captureAndRefund->nextSequenceNumber($txid);

            require_once dirname(__FILE__).'/../Frontend/PaymentBuiswPaymentPayone.php';
			$params = Shopware_Controllers_Frontend_PaymentBuiswPaymentPayone::createFundamentalParams($mode == 'l' ? 'live' : 'test');
			$params['request'] = 'capture';
			$params['txid'] = $txid;
			$params['amount'] = $amount;
			$params['sequencenumber'] = $nextSequenceNumber;
			$params['currency'] = $transLogEntry['currency'];
			$params['settleaccount'] = "auto";

			$err = false;
			$apilogid = null;
			$response = Shopware_Controllers_Frontend_PaymentBuiswPaymentPayone::curlCallAndApiLog($params, $err, $apilogid);

			if ($err) {
				$return->error = 'technical problems: ' . $response[0];
				echo json_encode ($return);
				exit (0);
			}

			if ($response['status'] == 'ERROR') {
				$return->error = 'error (' . $response['errorcode'] . ') ' . $response['errormessage'];
				echo json_encode ($return);
				exit (0);
			}
			// erst ab hier ist alles erstmal ok.
			$captureAndRefund->log ($txid, $nextSequenceNumber, $amount,$apilogid);

		} catch (Exception $e) {
				$return->error = 'technical problems: ' . $e->getMessage();
				echo json_encode ($return);
				exit (0);
		}
		//$return->error = $orderID . " asdsda $amount $txid";

		echo json_encode($return);
	}

	public function refundAmountAction() {

		Shopware()->Plugins()->Controller()->ViewRenderer()->setNoRender();


		$return = new stdClass();
        
		$amount = $this->checkAmmount ($this->Request()->getParam('amount'));
		if (is_object ($amount)) {
			echo json_encode($amount);
			exit (0);
		}

		$order_number = (int) $this->Request()->getParam('oID');
		$row = Shopware()->Db()->fetchRow('select transactionID,userID from s_order where ordernumber="' . $order_number . '"');
		$txid = $row['transactionID'];


		//$uid = Shopware()->Session()->sUserId = $row['userID'];

		try {
            require_once dirname(__FILE__).'/../Frontend/PaymentBuiswPaymentPayone.php';
			$translog = new payone_TransactionLogs();
			$s = $translog->select()->columns(array ('id','api_log_id','amount','currency', 'mode', 'data_array'));
			$s->where('transaction_no=?', $txid);
			$transLogEntry = $translog->fetchRow($s);


			$mode = $transLogEntry['mode'];

			$captureAndRefund = new payone_CaptureAndRefund();

			$nextSequenceNumber = $captureAndRefund->nextSequenceNumber($txid);

			$params = Shopware_Controllers_Frontend_PaymentBuiswPaymentPayone::createFundamentalParams($mode == 'l' ? 'live' : 'test');
			// $params['request'] = 'refund';
			$params['request'] = 'debit';

			$params['txid'] = $txid;
			$amount = -1 * $amount;
			$params['amount'] =  $amount;
			$params['sequencenumber'] = $nextSequenceNumber;
			$params['currency'] = $transLogEntry['currency'];


			$err = false;
			$apilogid = null;
  		$response = Shopware_Controllers_Frontend_PaymentBuiswPaymentPayone::curlCallAndApiLog($params, $err, $apilogid);

			if ($err) {
				$return->error = 'technical problems: ' . $response[0];
				echo json_encode ($return);
				exit (0);
			}
			if ($response['status'] == 'ERROR') {
				$return->error = 'error (' . $response['errorcode'] . ') ' . $response['errormessage'];
				echo json_encode ($return);
				exit (0);
			}
			// erst ab hier ist alles erstmal ok.
			$captureAndRefund->log ($txid, $nextSequenceNumber, $amount,$apilogid);

		} catch (Exception $e) {
			$return->error = 'technical problems: ' . $e->getMessage();
				echo json_encode ($return);
				exit (0);
		}

		echo json_encode($return);
	}
    
    public function indexAction()
    {
        $this->Front()->Plugins()->ViewRenderer()->setNoRender();
        $this->View()->loadTemplate("app.js");
    }
    public function loadAction()
    {
        $this->Front()->Plugins()->ViewRenderer()->setNoRender();
        
    }   
    public function showOrdersAction()
    {
        $this->View()->loadTemplate("app.js");
    }
    
    public function loadOrdersAction(){
        define('BUIWPAYMENTTOKEN', 'securedCall');
        require_once '../../orders.php';
    }
    
    
}
