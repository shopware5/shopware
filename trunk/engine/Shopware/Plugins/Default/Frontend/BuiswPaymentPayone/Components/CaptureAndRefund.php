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
class payone_CaptureAndRefund extends payone_tableBasics {

	const TABLE_NAME = 's_bui_plg_payone_capture_refunds';

	/**
	 *
	 * @param string $txid
	 * @return int
	 */
	public function nextSequenceNumber($txid) {
		$sql = 'select max(sequencenumber) from ' . self::TABLE_NAME .
						' where transaction_no="' . $txid . '"';
		$res = Shopware()->Db()->fetchOne($sql);

		return (int) ($res ? $res + 1 : 1);
	}

	/**
	 *
	 * @param string $txid
	 * @param int $nextSequenceNumber
	 * @param int $amount
	 * @param int $apilogid
	 */
	public function log($txid, $nextSequenceNumber, $ammount, $apilogid) {
		$data = array ('transaction_no' => $txid,
				'ammount' => $ammount,
				'sequencenumber' => $nextSequenceNumber,
				'api_log_id' => $apilogid
		);
		$this->insert($data);
	}

}
?>