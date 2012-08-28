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
class payone_ApiLogger {
	const CLIENT_API = 'c';
	const SERVER_API = 's';



	static public function logClientAPICall ($request, $response, $api) {
		$cardpan = $request['cardpan'];
		$len = strlen ($cardpan);
		$cardpan = $cardpan[0] . str_repeat('X', $len-2) . $cardpan[$len-1];
		$request['cardpan'] = $cardpan;

		//unset this !!! security reasons and regulation from Payment Card Industry Data Security Standard
		//unset($request['cardpan']);
		unset($request['cardcvc2']);
		unset($request['cardexpiredate']);

		self::logAPI ($api, $request['request'], $response['status'], $request, $response);
	}

	static public function logAPI ($api, $request, $response, $request_data, $response_data) {

        $uid = 0; 
		$log = new payone_ApiLogs();
		$data = array(
				'user_id' => $uid,
				'api' => $api,
				'request' => $request,
				'response' => $response,
				'request_data' => serialize($request_data),
				'response_data' => serialize($response_data)
		);
		try {
		 // $stm->execute(array ($uid, $api, $request, $response, serialize ($request_data), serialize ($response_data)));
			$log->insert($data);
		}
		catch (Exception $e) {
			return 0;
		}
	 return Shopware()->Db()->lastInsertId();
	}

	static public function logTransaction ($apiLogId, $transactionNumber, $paymethod, $custEmail, $amount, $currency, $status, $mode, $orderNumber = 0, $data_array = null) {
		$uid = Shopware()->Session()->sUserId;
		$log = new payone_TransactionLogs();
		try {
			$data = array(
					'api_log_id' => $apiLogId,
					'user_id' => $uid,
					'order_number' => $orderNumber,
					'transaction_no' => $transactionNumber,
					'paymethod' => $paymethod,
					'customer_email' => $custEmail,
					'amount' => $amount,
					'currency' => $currency,
					'status' => $status,
					'mode' => $mode == 'live' ? 'l' : 't'
			);
			if (is_array ($data_array)) {
				$data['data_array'] = serialize (array ($data_array));
			}
			else {
				// das else nur falls hier gedebugt werden muss
			}
			$log->insert($data);
		}
		catch (Exception $e) {
			return 0;
		}
	 return Shopware()->Db()->lastInsertId();
	}

	static public function assignOrderNumberToTransactionLog ($transactionLogId, $orderNumber) {
		$log = new payone_TransactionLogs();
		$log->update(array ('order_number' => $orderNumber), 'id=' . $transactionLogId);
	}
}

?>
