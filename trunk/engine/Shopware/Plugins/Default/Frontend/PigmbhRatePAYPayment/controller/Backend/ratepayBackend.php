<?php

/**
 * RatePAY Payment Module
 *
 * @author       PayIntelligent GmbH  <http://www.payintelligent.de/>
 * @package      PiPaymentRatepay
 * @copyright(C) 2011 RatePAY GmbH. All rights reserved. <http://www.ratepay.com/>
 */
class Shopware_Controllers_Backend_RatepayBackend extends Enlight_Controller_Action
{

    /**
     * Adds template directory to the view
     *
     * @return void
     */
    public function init()
    {
        $this->View()->addTemplateDir(dirname(__FILE__) . "/Views/");
    }

    /**
     * Loads index.php(Javascript part) for RatePAY orderwindow
     *
     * @return void
     */
    public function indexAction()
    {
        $this->View()->loadTemplate("Backend/PigmbhRatePAYPayment/index.tpl");
    }

    /**
     * Loads Skeleton for RatePAY orderwindow
     *
     * @return void
     */
    public function skeletonAction()
    {
        $this->View()->loadTemplate("Backend/PigmbhRatePAYPayment/skeleton.tpl");
    }

    /**
     * Gets all orders that where payed with RatePAY and delivers a JSON String.
     * Checks all orders that have the "pending" state and changes the state if neccesary.
     *
     * @see templates/backend/plugins/PigmbhRatePAYPayment/index.php
     * @return void
     */
    public function getOrdersAction()
    {
        $this->View()->setTemplate();
        $start = (int)isset($this->Request()->start)? $this->Request()->start: 0;
        $limit = (int)isset($this->Request()->limit)? $this->Request()->limit: 10;
        $paidString = $this->Request()->nurbezahlt == 'true'? "AND a.cleared = ".(int)getAcceptedStatusId()."": "";
        $search = isset($this->Request()->search)? $this->Request()->search: '';
        $search = Shopware()->Db()->quote($search);
        $searchFor = isset($this->Request()->suchenach)?  $this->Request()->suchenach: 1;
        $searchString = "";
        if ($search == '') $searchFor = 3;
        switch ($searchFor) {
            case 1:
                $searchString = "a.ordernumber LIKE '%" . $search . "%'
                              OR a.transactionID LIKE '%" . $search . "%'
                              OR d.description LIKE '%" . $search . "%'
                              OR b.lastname LIKE '%" . $search . "%' ";
                break;
            case 2:
                $searchString = "a.ordernumber LIKE '%" . $search . "%'";
                break;
            case 3:
                $searchString = "a.transactionID LIKE '%" . $search . "%'";
                break;
            case 4:
                $searchString = "d.description LIKE '%" . $search . "%'";
                break;
            case 5:
                $searchString = "b.lastname LIKE '%" . $search . "%'";
                break;
        }
        $sql = "SELECT DISTINCT
                    a.id AS id,
                    a.ordertime AS bestellzeit,
                    a.ordernumber AS bestellnr,
                    a.transactionID AS transaktion,
                    a.invoice_amount AS betrag,
                    CONCAT(b.firstname,' ', b.lastname) AS kunde,
                    c.description AS zahlstatus,
                    d.description AS bestellstatus,
                    e.description AS zahlart,
                    f.name AS sprache
                FROM `s_order` AS a
                LEFT JOIN `s_user_billingaddress` b ON a.userID = b.UserID
                LEFT JOIN `s_core_states` c ON a.cleared = c.id
                LEFT JOIN `s_core_states` d ON a.status = d.id
                LEFT JOIN `s_core_paymentmeans` e ON a.paymentID = e.id
                LEFT JOIN `s_core_multilanguage` f ON a.language = f.isocode
                WHERE " . $searchString . "
                " . $paidString . "
                AND(a.paymentID = ?	OR  a.paymentID = ? OR  a.paymentID = ?)
                AND a.cleared !='Abgebrochen'
                ORDER BY a.ordertime DESC
                LIMIT " . $start . "," . $limit . "";
        $orders = Shopware()->Db()->fetchAll($sql,array(
            (int)getInvoicePaymentId(),
            (int)getRatePaymentId(),
            (int)getDebitPaymentId()
        ));
        $total = Shopware()->Db()->fetchAll(substr($sql, 0, strpos($sql, 'LIMIT')),array(
            (int)getInvoicePaymentId(),
            (int)getRatePaymentId(),
            (int)getDebitPaymentId()
        ));
        foreach ($orders as $key => $order) {
            $dispatchId = $this->_getDispatchId($order['bestellnr']);
            $dispatchId == 0? $order['versand'] = 'Keine Versandkosten': $order['versand'] = $this->_getDispatchName($dispatchId);
            $order['userid'] = $this->_getUserId($order['bestellnr']);
            $order['kunde'] = htmlentities($order['kunde']);
            $order['RatePAYid'] = ($key + 1) + $start;
            $order['betrag'] = number_format($order['betrag'], 2, ',', '.');
            $order['bestellstatus_kurz'] = $this->_removeSpans($order['bestellstatus']);
            $order['bestellstatus']=  str_replace('\"', "", $order['bestellstatus']);
            $order['zahlstatus']=  str_replace('\"', "", $order['zahlstatus']);
            $order['zahlstatus_kurz'] = $this->_removeSpans($order['zahlstatus']);;
            $order['options_delete'] = '&nbsp;';
            $img = $this->_getImgPath($order['zahlart']);
            $order['zahlart'] = '<a title="Bestellung Nr. ' . $order['bestellnr'] . ' bearbeiten" class="mylogoonclick" onclick="orderwindow('
                              . $order['id'] . ',' . $order['bestellnr'] . ',\''  . $order['kunde'] . '\')">'
                              . '<img class="RatePAY_order_img" src="' . $img . '" width="65px";/></a>';
            $order['options_RatePAY'] = '<a class ="pencil myonclick" title="Bestellung Nr. ' . $order['bestellnr']
                                      . ' bearbeiten" onclick="orderwindow(' . $order['id'] . ',' . $order['bestellnr'] . ',\''
                                      . $order['kunde'] . '\')">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</a>';
            $orders[$key] = $order;
        }
        echo json_encode(array("total" => count($total), "items" => $orders));
    }

    /**
     * Gets all articles for selected order and delivers all article details as JSON String.
     *
     * @see templates/backend/plugins/PigmbhRatePAYPayment/index.php
     * @return void
     */
    public function getArticlesAction()
    {
        $orderNumber = $this->Request()->myordernumber;
        $this->View()->setTemplate();
        $start = (int)isset($this->Request()->start)? $this->Request()->start: 0;
        $limit = (int)isset($this->Request()->limit)? $this->Request()->limit: 8;
        $search = isset($this->Request()->search)? "%".$this->Request()->search."%": "%";
        $search = Shopware()->Db()->quote($search);
        $sql = "SELECT DISTINCT *
                FROM `pi_ratepay_order_detail`
                WHERE `ordernumber` = ?
                AND `einzelpreis` != 0
                AND (`name` LIKE ? OR `bestell_nr` LIKE ? )
                ORDER BY `name`
                LIMIT " . $start . "," . $limit . "";
        $articles = Shopware()->Db()->fetchAll($sql, array($orderNumber, $search, $search));
        $total = Shopware()->Db()->fetchAll(substr($sql, 0,  strpos($sql, 'LIMIT')), array($orderNumber, $search, $search));
        $orderFixId = $this->_getOrderIdByNumber($orderNumber);
        foreach ($articles as $key => $article) {
            $article['name'] = htmlentities(utf8_decode($article['name']));
            $article['options_delete'] = '&nbsp;';
            $article['einzelpreis'] = number_format($article['einzelpreis'], 2, ',', '.');
            $sql = "SELECT DISTINCT `description`
                    FROM `s_core_states`
                    WHERE `id` =
                    (
                        SELECT DISTINCT `versandstatus`
                        FROM `pi_ratepay_order_detail`
                        WHERE `bestell_nr` = ?
                        AND `ordernumber` = ?
                    )";
            $article['bestellstatus'] = Shopware()->Db()->fetchOne($sql, array($article['bestell_nr'], $orderNumber));
            $article['bestellstatus']=str_replace('\\', '', $article['bestellstatus']);
            $article['bestellstatus_kurz'] = $this->_removeSpans($article['bestellstatus']);
            $sql = "SELECT DISTINCT b.lastname AS kunde
                    FROM `s_order` AS a
                    INNER JOIN `s_order_billingaddress` b ON a.userID = b.UserID
                    WHERE a.ordernumber = ?";
            $article['customer'] = Shopware()->Db()->fetchOne($sql, array( $article['ordernumber']));
            $article['nr'] = $key + 1;
            $article['orderfixid'] = $orderFixId;
            $sql = "SELECT `instock` FROM `s_articles_details` WHERE `ordernumber` = ? ";
            $article['stock'] = Shopware()->Db()->fetchOne($sql, array($article['bestell_nr']));
            if ($article['name'] == 'Gutschein-intern' || $article['name'] == 'Zuschlag f&uuml;r Zahlungsart')
                $article['stock'] = 'unbegrenzt';
            $sql = "SELECT `ordernumber` AS `articlenumber` FROM `s_articles_details` WHERE `articleID` = ?";
            $article['articlenumber'] = Shopware()->Db()->fetchOne($sql, array((int)$article['articleid']));
            $articles[$key]=$article;
        }
        //Create Invoices
        $sql = "SELECT * FROM `pi_ratepay_bills` WHERE order_number = ? and type = 0";
        $bills = Shopware()->Db()->fetchAll($sql, array($orderNumber));
        if(!$bills){
            $orderID =$this->_getOrderIdByNumber($orderNumber);
            $dispatch = $this->_getDispatchId($orderNumber);
            $dispatch > 0? $shippingflag = true: $shippingflag = false;
            $document = Shopware_Components_Document::initDocument($orderID, 1, array(
                "netto" => false,
                "date" => date("Y-m-d H:i:s"),
                "shippingCostsAsPosition" => $shippingflag,
                "bid" => 0,
                "_renderer" => "pdf"
            ));
            $document->render();
            $sql = "UPDATE `pi_ratepay_orders`
                    SET `invoice_number` ='<span class=\'green\'>Rechnung ist aktuell</span>'
                    WHERE order_number = ?";
            Shopware()->Db()->query($sql, array($orderNumber));
            $sql = "SELECT SUM(einzelpreis *(bestellt - storniert - retourniert))
                    FROM `pi_ratepay_order_detail`
                    WHERE `ordernumber` = ?";
            $totalInvoiceAmount = Shopware()->Db()->fetchOne($sql, array($orderNumber));
            $sql = "SELECT * FROM `s_order_documents` WHERE `orderID` = ? AND `type` = ? ";
            $getDocument = Shopware()->Db()->fetchRow($sql, array((int)$orderID, (int)0));
            $sql = "INSERT INTO `pi_ratepay_bills` (`order_id`, `order_number`, `invoice_amount`, `invoice_hash`, `type`)
                    VALUES (?, ?, ?, ?, ?)";
            Shopware()->Db()->query($sql, array($orderID, $orderNumber, (double)$totalInvoiceAmount, $getDocument["hash"], (int)0));
        }
        $sql = "SELECT * FROM `pi_ratepay_bills` WHERE `order_number` = ? and `type` = ?";
        $cancelBill = Shopware()->Db()->fetchRow($sql, array($orderNumber, (int)3));
        if(!$cancelBill){
            $sql = "SELECT SUM(einzelpreis *(bestellt - storniert - retourniert )) FROM `pi_ratepay_order_detail` WHERE `ordernumber` = ?";
            $amount = Shopware()->Db()->fetchOne($sql, array($orderNumber));
            $this->_createCancelInvoice($orderNumber, $amount);
        }
        echo json_encode(array("total" => count($total), "items" => $articles));
    }

    /**
     * Delete order from orderlist
     *
     * @see templates/backend/plugins/PigmbhRatePAYPayment/index.php
     * @throws  RatePAYException
     * @return object	$this->forward('index') redirect
     */
    public function deleteOrderAction()
    {
        $this->View()->setTemplate();
        $orderNumber = $this->Request()->ordernumber;
        $orderid = $this->Request()->orderId;
        $status = $this->Request()->bestellstatus;
        $cleared = $this->Request()->zahlstatus;
        $articles = $this->_getArticlesByOrderNumber($orderNumber);
        $return = array();
        try {
            if ($status == 'offen' && !$cleared) $return = paymentChange($orderNumber, $articles, 'full-cancel');
            $sql = "DELETE FROM `s_order` WHERE `id` = ?";
            Shopware()->Db()->query($sql, array((int)$orderid));
            $sql = "DELETE FROM `pi_ratepay_orders` WHERE `order_number` = ?";
            Shopware()->Db()->query($sql, array($orderNumber));
            $sql = "DELETE FROM `pi_ratepay_order_detail` WHERE `ordernumber` ='" . $orderNumber . "'";
            Shopware()->Db()->query($sql, array($orderNumber));
            $returnText = 'Bestellung erfolgreich gel&ouml;scht';
            echo json_encode(array("returnValue" => $returnText));
        }
        catch (Exception $e) {
            $returnText = 'Fehler beim l&ouml;schen der Bestellung'. $e;
            echo json_encode(array("errorValue" => true, "returnValue" => $returnText));
        }
    }

    /**
     * Gets History. Delivers a JSON String with history details
     *
     * @see templates/backend/plugins/PigmbhRatePAYPayment/index.php
     */
    public function getHistoryAction()
    {
        $this->View()->setTemplate();
        $orderNumber = $this->Request()->myordernumber;
        $sql = "SELECT * FROM `pi_ratepay_history` WHERE `ordernumber` = ?";
        $myhistory = Shopware()->Db()->fetchAll($sql, array($orderNumber));
        for ($i = 0; $i < sizeof($myhistory); $i++) {
            $myhistory[$i]['id'] = $i + 1;
            $myhistory[$i]['name'] = htmlentities($myhistory[$i]['name']);
        }
        echo json_encode(array("total" => count($myhistory), "items" => $myhistory));
    }

    /**
     * Gets log. Delivers a JSON String with history details
     *
     * @see templates/backend/plugins/PigmbhRatePAYPayment/index.php
     */
    public function getLogAction()
    {
        $this->View()->setTemplate();
        $orderNumber = $this->Request()->myordernumber;
        $mytransaction_id = $this->_getTransactionIdByOrderNumber($orderNumber);
        $start = isset($this->Request()->start)? $this->Request()->start: 0;
        $limit = isset($this->Request()->limit)? $this->Request()->limit: 50;
        $mylog = array();
        if ($this->Request()->alllogs) {
            if (isset($this->Request()->search)) {
                $search = $this->Request()->search;
                $sql = "SELECT * FROM `pi_ratepay_log`
                        WHERE `order_number` LIKE ? OR `payment_type` LIKE ? OR `payment_subtype` LIKE ?
                        ORDER BY `date` DESC LIMIT " . (int)$start . "," . (int)$limit . "";
                $mylog = Shopware()->Db()->fetchAll($sql, array("%" . $search . "%", "%" . $search . "%", "%" . $search . "%"));
            } else {
                $sql = "SELECT * FROM `pi_ratepay_log` ORDER BY `date` DESC LIMIT " . (int)$start . "," . (int)$limit . "";
                $mylog = Shopware()->Db()->fetchAll($sql);
            }
        } else {
            $sql = "SELECT * FROM `pi_ratepay_log` WHERE `transaction_id` = ? LIMIT " . (int)$start . "," . (int)$limit . "";
            $mylog = Shopware()->Db()->fetchAll($sql, array($mytransaction_id));
        }
        for ($i = 0; $i < sizeof($mylog); $i++) {
            $mylog[$i]['response'] = removeSpecialCharsforLog($mylog[$i]['response']);
            $mylog[$i]['request']  = removeSpecialCharsforLog($mylog[$i]['request']);
        }
        $sql = "SELECT * FROM pi_ratepay_log";
        $mycountlog = Shopware()->Db()->fetchAll($sql);
        echo json_encode(array("total" => count($mycountlog), "items" => $mylog));
    }

    /**
     * Gets installment details. Delivers a JSON String with history details
     *
     * @see templates/backend/plugins/PigmbhRatePAYPayment/index.php
     */
    public function getRateConfigRequestAction()
    {
        $this->View()->setTemplate();
        include_once dirname(__FILE__) . '/../../Views/Frontend/Ratenrechner/php/pi_ratepay_xml_service.php';
        $ratepay = new pi_ratepay_xml_service;
        $ratepay->live = checkSandboxMode('RatePAYRate');
        $request = $ratepay->getXMLObject();
        $returnItems = array();
        $head = $request->addChild('head');
        $head->addChild('system-id', $_SERVER['REMOTE_ADDR']);
        $head->addChild('operation', 'CONFIGURATION_REQUEST');
        setRatepayHeadCredentials($head, 'CONFIGURATION_REQUEST', false, false);
        $response = $ratepay->paymentOperation($request);
        $response? $responseVar = $response: $responseVar=false;
        writeLog("", "", "CONFIGURATION_REQUEST", "", $request, $responseVar, Shopware()->Config()->Shopname, 'RatePAYRate' );
        $returnItems[0]['text'] = 'interestrate-min';
        $returnItems[0]['value'] = (string) $response->content->{'installment-configuration-result'}->{'interestrate-min'};
        $returnItems[1]['text'] = 'interestrate-default';
        $returnItems[1]['value'] = (string) $response->content->{'installment-configuration-result'}->{'interestrate-default'};
        $returnItems[2]['text'] = 'interestrate-max';
        $returnItems[2]['value'] =  (string) $response->content->{'installment-configuration-result'}->{'interestrate-max'};
        $returnItems[3]['text'] = 'month-number-min';
        $returnItems[3]['value'] = (string) $response->content->{'installment-configuration-result'}->{'month-number-min'};
        $returnItems[4]['text'] = 'month-number-max';
        $returnItems[4]['value'] = (string) $response->content->{'installment-configuration-result'}->{'month-number-max'};
        $returnItems[5]['text'] = 'month-longrun';
        $returnItems[5]['value'] =  (string) $response->content->{'installment-configuration-result'}->{'month-longrun'};
        $returnItems[6]['text'] = 'month-allowed';
        $returnItems[6]['value'] =  (string) $response->content->{'installment-configuration-result'}->{'month-allowed'};
        $returnItems[7]['text'] = 'payment-firstday';
        $returnItems[7]['value'] =  (string) $response->content->{'installment-configuration-result'}->{'payment-firstday'};
        $returnItems[8]['text'] = 'payment-amount';
        $returnItems[8]['value'] =  (string) $response->content->{'installment-configuration-result'}->{'payment-amount'};
        $returnItems[9]['text'] = 'payment-lastrate';
        $returnItems[9]['value'] =  (string) $response->content->{'installment-configuration-result'}->{'payment-lastrate'};
        $returnItems[10]['text'] = 'rate-min-normal';
        $returnItems[10]['value'] =  (string) $response->content->{'installment-configuration-result'}->{'rate-min-normal'};
        $returnItems[11]['text'] = 'rate-min-longrun';
        $returnItems[11]['value'] =  (string) $response->content->{'installment-configuration-result'}->{'rate-min-longrun'};
        $returnItems[12]['text'] = 'service-charge';
        $returnItems[12]['value'] = (string) $response->content->{'installment-configuration-result'}->{'service-charge'};
        echo json_encode(array("total" => count($returnItems), "items" => $returnItems));
    }


    /**
     * Adds Voucher to order. Delivers a JSON String with vocuher details
     *
     * @see templates/backend/plugins/PigmbhRatePAYPayment/index.php
     * @throws RatePAYException
     */
    public function addVoucherAction()
    {
        $this->View()->setTemplate();
        $orderNumber = $this->Request()->myordernumber;
        $myprice = $this->Request()->price;
        $myarticlenumber = $this->Request()->articlenumber;
        $myorderid = $this->_getOrderIdByNumber($orderNumber);
        $allarticles = $this->_getArticlesByOrderNumber($orderNumber);
        $articles = array();
        $articlescount = 0;
        $myprice = str_replace(",", ".", $myprice);
        for ($i = 0; $i < sizeof($allarticles); $i++) {
            $articles[$i]['name'] = $allarticles[$i]['name'];
            $articles[$i]['storniert'] = $allarticles[$i]['storniert'];
            $articles[$i]['anzahl'] = $allarticles[$i]['anzahl'];
            $articles[$i]['offen'] = $allarticles[$i]['offen'];
            $articles[$i]['retourniert'] = $allarticles[$i]['retourniert'];
            $articles[$i]['bestellt'] = $allarticles[$i]['bestellt'];
            $articles[$i]['einzelpreis'] = $allarticles[$i]['einzelpreis'];
            $articles[$i]['bestellnr'] = $allarticles[$i]['bestell_nr'];
            $articles[$i]['einzelpreis_net'] = $allarticles[$i]['einzelpreis_net'];
            $articlescount += 1;
        }

        $sql = "SELECT max(tax) FROM s_core_tax";
        $maxTax = Shopware()->Db()->fetchOne($sql);
        $myprice = str_replace(",", ".", $myprice);
        $mynetprice =$myprice / ( 100 + $maxTax ) * 100;
        $articles[$articlescount]['name'] = "Gutschein-intern";
        $articles[$articlescount]['bestellt'] = 1;
        $articles[$articlescount]['bestellnr'] = $myarticlenumber;
        $articles[$articlescount]['geliefert'] = 0;
        $articles[$articlescount]['storniert'] = 0;
        $articles[$articlescount]['offen'] = 1;
        $articles[$articlescount]['retourniert'] = 0;
        $articles[$articlescount]['anzahl'] = 1;
        $articles[$articlescount]['einzelpreis'] = $myprice;
        $articles[$articlescount]['einzelpreis_net'] =$mynetprice;

        $returnValue = paymentChange($orderNumber, $articles, 'credit');

        if ($returnValue == true) {
            $sql = "INSERT INTO `s_order_details`
                    (
                        `orderID`,
                        `ordernumber`,
                        `taxID`,
                        `articleID`,
                        `articleordernumber`,
                        `price`,
                        `quantity`,
                        `name`,
                        `status`,
                        `shipped`,
                        `shippedgroup`
                    )
                    VALUES
                    (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
            Shopware()->Db()->query($sql, array(
                (int)$myorderid,
                $orderNumber,
                (int)1,
                (int)172,
                $myarticlenumber,
                (double)$myprice,
                (int)1,
                'Gutschein-intern',
                (int)0,
                (int)0,
                (int)0
            ));
            $sql = "INSERT INTO `pi_ratepay_order_detail` VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
            Shopware()->Db()->query($sql, array(
                "",
                $orderNumber,
                (int)172,
                $myarticlenumber,
                (int)1,
                'Gutschein-intern',
                (double)$myprice,
                (double)$myprice,
                (int)1,
                (int)1,
                (int)0,
                (int)0,
                (int)0,
                (int)18,
                (int)0,
                (double)$mynetprice
            ));
            $event = "<span>Gutschein hinzugef&uuml;gt</span>";
            historyEntry($orderNumber, $event, 'Gutschein-intern', $myarticlenumber, '1');
            berechneGesamtpreis($orderNumber);
        }
        echo json_encode(array('articlename' => 'Gutschein-intern', 'returnValue' => $returnValue, "articles" => $articles));
    }

    /**
     * Gets articles for the send and cancel window. Delivers a JSON String with article details
     *
     * @see templates/backend/plugins/PigmbhRatePAYPayment/index.php
     */
    public function getSendAndCancelArticlesAction()
    {
        $this->View()->setTemplate();
        $orderNumber = $this->Request()->myordernumber;
        $sql = "SELECT c.description FROM `s_order` a INNER JOIN s_core_states c ON a.cleared = c.id WHERE a.ordernumber = ?";
        $checkpayment = Shopware()->Db()->fetchOne($sql, array($orderNumber));
        $start = isset($this->Request()->start)? $this->Request()->start: 0;
        $limit = isset($this->Request()->limit)? $this->Request()->limit: 1000;
        $articles = array();
        $total = 0;
        if ($checkpayment != str_replace("Zahlung von RatePAY akzeptiert", "", $checkpayment)) {
            if(isset($this->Request()->search)) {
                $search = $this->Request()->search;
                $sql = "SELECT DISTINCT * FROM `pi_ratepay_order_detail`
                        WHERE `ordernumber` = ? AND `offen` > 0 AND `einzelpreis` <> 0 AND (`name` LIKE ? OR `bestell_nr` LIKE ?)
                        LIMIT " . (int)$start . "," .(int) $limit . "";
                $articles = Shopware()->Db()->fetchAll($sql, array($orderNumber, "%" . $search . "%", "%" . $search . "%"));
                $total = $articles;
            }else {
                $sql = "SELECT * FROM `pi_ratepay_order_detail` WHERE `ordernumber` = ? AND `offen` > 0	AND `einzelpreis` <> 0";
                $total = Shopware()->Db()->fetchAll($sql, array($orderNumber));
                $sql = "SELECT * FROM `pi_ratepay_order_detail`
                        WHERE `ordernumber` = ? AND `offen` > 0 AND `einzelpreis` <> 0
                        LIMIT " . (int)$start . "," . (int)$limit . "";
                $articles = Shopware()->Db()->fetchAll($sql, array($orderNumber));
            }
            for ($i = 0; $i < sizeof($articles); $i++) {
                $articles[$i]['gesamtpreis'] = number_format($articles[$i]['einzelpreis'] * $articles[$i]['offen'], 2, ',', '.');
                $articles[$i]['einzelpreis'] = number_format($articles[$i]['einzelpreis'], 2, ',', '.');
                $articles[$i]['name'] = htmlentities(utf8_decode($articles[$i]['name']));
                $articles[$i]['anzahl'] = $articles[$i]['offen'];
            }
        } else {
            $articles = '';
        }
        echo json_encode(array("total" => Count($total), "items" => $articles));
    }

    /**
     * Sends articles and creates invoice. Delivers a JSON String with article details
     *
     * @see templates/backend/plugins/PigmbhRatePAYPayment/index.php
     * @throws RatePAYException
     */
    public function sendArticlesAction()
    {
        $myflag = true;
        $articleCounter = 0;
        $totalSendAmount = 0;
        //send a part of the order
        $this->View()->setTemplate();
        $articleNumber = explode(";", $this->Request()->articlenr);
        $quantity = explode(";", $this->Request()->anzahl);
        $orderNumber = $this->Request()->myordernumber;
        $sql = "SELECT Count(*) FROM `pi_ratepay_order_detail` WHERE `ordernumber` = ?";
        $allArticlesQuantity = Shopware()->Db()->fetchOne($sql, array($orderNumber));
        $ratepayArticles = array();
        $myarticle = array();
        $newOpen = array();
        $newDelivered = array();
        $cleared = 0;
        for ($i = 0; $i < sizeof($quantity); $i++) {
            $sql = "SELECT * FROM `pi_ratepay_order_detail` WHERE `ordernumber` = ? AND `bestell_nr` = ?";
            $myarticle[$i] = Shopware()->Db()->fetchAll($sql, array($orderNumber, $articleNumber[$i]));
            $newOpen[$i] = $myarticle[$i][0]['offen'] - $quantity[$i];
            $newDelivered[$i] = $myarticle[$i][0]['geliefert'] + $quantity[$i];
            if ($newOpen[$i] != 0 || sizeof($quantity) != $allArticlesQuantity) $myflag = false;
            if ($quantity[$i] > 0) {
                $ratepayArticles[$articleCounter]['bestell_nr'] = $articleNumber[$i];
                $ratepayArticles[$articleCounter]['anzahl'] = $quantity[$i];
                $ratepayArticles[$articleCounter]['einzelpreis'] = $myarticle[$i][0]['einzelpreis'];
                $ratepayArticles[$articleCounter]['name'] = htmlentities($myarticle[$i][0]['name']);
                $ratepayArticles[$articleCounter]['einzelpreis_net'] = $myarticle[$i][0]['einzelpreis_net'];
                $articleCounter++;
                $totalSendAmount+=$quantity[$i] * $myarticle[$i][0]['einzelpreis'];
            }
        }
        $returnValue = confirmDelivery($orderNumber, $ratepayArticles);
        if ($returnValue == true) {
            for ($i = 0; $i < sizeof($quantity); $i++) {
                if ($quantity[$i] > 0) {
                    $articleName = str_replace("'", "\'", $myarticle[$i][0]['name']);
                    historyEntry($orderNumber, "<span>Artikel versendet</span>", $articleName, $articleNumber[$i], $quantity[$i]);
                    if ($newOpen[$i] == 0) {
                        $cleared = getCompleteSentStatusID();
                    } elseif ($newOpen[$i] > 0) {
                        $cleared = getPartSentStatusID();
                    }
                    $sql = "UPDATE `pi_ratepay_order_detail` SET `offen` = ?, `geliefert` = ?, `versandstatus` = ?
                            WHERE `ordernumber` = ? AND `bestell_nr` = ?";
                    Shopware()->Db()->query($sql, array(
                        (int)$newOpen[$i],
                        (int)$newDelivered[$i],
                        (int)$cleared,
                        $orderNumber,
                        $articleNumber[$i]
                    ));
                }
            }
            if ($myflag == true) {
                setOrderState($orderNumber, getCompleteSentStatusID());
                $sql = "UPDATE `pi_ratepay_order_detail` SET `versandstatus` = ? WHERE `ordernumber` = ?";
                Shopware()->Db()->query($sql, array((int)getCompleteSentStatusID(), $orderNumber));
                historyEntry($orderNumber, "<b class=\"green\">Bestellung vollst&auml;ndig versendet</b>", "", "" , "");
            } else {
                $sql = " SELECT sum(`offen`) FROM `pi_ratepay_order_detail` WHERE `ordernumber` = ?";
                $totalOpen = Shopware()->Db()->fetchOne($sql, array($orderNumber));
                $totalOpen > 0? setOrderState($orderNumber, getPartSentStatusID()): setOrderState($orderNumber, getCompleteSentStatusID());
            }
        }
        echo json_encode(array('articlename' => $myarticle, 'returnValue' => $returnValue));
    }

    /**
     * Cancels articles from order. Delivers a JSON String with article details
     *
     * @see templates/backend/plugins/PigmbhRatePAYPayment/index.php
     * @throws RatePAYException
     */
    public function cancelArticlesAction()
    {
        $this->View()->setTemplate();
        $articlenr = explode(";", $this->Request()->articlenr);
        $cancelQuantity = explode(";", $this->Request()->anzahl);
        $orderNumber = $this->Request()->myordernumber;
        $myflag = true;
        $completeQuantity = 0;
        $returnValue = array('error' => false, 'errormessage' => 'Komplett');
        $sql = "SELECT SUM(`anzahl`-`storniert`-`retourniert`) FROM `pi_ratepay_order_detail` WHERE `ordernumber` = ?";
        $articlesquantity = Shopware()->Db()->fetchOne($sql, array($orderNumber));
        $myarticle = array();
        $newOpen = array();
        $newCanceled = array();
        $newSended = array();
        $totalAmount = array();
        $articles = array();
        $allarticles = $this->_getArticlesByOrderNumber($orderNumber);
        for ($i = 0; $i < sizeof($articlenr); $i++) {
            for ($j = 0; $j < sizeof($allarticles); $j++) {
                if ($articlenr[$i] == $allarticles[$j]['bestell_nr']) {
                    $allarticles[$j]['storniert'] = $allarticles[$j]['storniert'] + $cancelQuantity[$i];
                    $allarticles[$j]['offen'] = $allarticles[$j]['offen'] - $cancelQuantity[$i];
                }
            }
            $sql = "SELECT * FROM `pi_ratepay_order_detail` WHERE `ordernumber` = ? AND `bestell_nr` = ?";
            $myarticle = Shopware()->Db()->fetchAll($sql, array($orderNumber, $articlenr[$i]));
            $newOpen[$i] = $myarticle[0]['offen'] - $cancelQuantity[$i];
            $newCanceled[$i] = $myarticle[0]['storniert'] + $cancelQuantity[$i];
            $newSended[$i] = $myarticle[0]['bestellt'] - $newCanceled[$i];
            $totalAmount[$i] = $newOpen[$i] * $myarticle[0]['einzelpreis'];
            $totalAmount[$i]+=$myarticle[0]['geliefert'] * $myarticle[0]['einzelpreis'];
            $completeQuantity+=$cancelQuantity[$i];
        }
        for ($i = 0; $i < sizeof($allarticles); $i++) {
            $articles[$i]['name'] = $allarticles[$i]['name'];
            $articles[$i]['storniert'] = $allarticles[$i]['storniert'];
            $articles[$i]['anzahl'] = $allarticles[$i]['anzahl'];
            $articles[$i]['offen'] = $allarticles[$i]['offen'];
            $articles[$i]['retourniert'] = $allarticles[$i]['retourniert'];
            $articles[$i]['bestellt'] = $allarticles[$i]['bestellt'];
            $articles[$i]['einzelpreis'] = $allarticles[$i]['einzelpreis'];
            $articles[$i]['bestellnr'] = $allarticles[$i]['bestell_nr'];
            $articles[$i]['einzelpreis_net'] = $allarticles[$i]['einzelpreis_net'];
        }
        if ($completeQuantity != $articlesquantity) $myflag = false;
        if ($myflag == true) {
            try {
                $returnValue = paymentChange($orderNumber, $articles, 'full-cancellation');
                if($returnValue){
                    $sql = "UPDATE `pi_ratepay_orders` SET `invoice_number` = '<span class=\'red\'>Rechnung ist nicht aktuell</span>'
                            WHERE order_number = ?";
                    Shopware()->Db()->query($sql, array($orderNumber));
                    for ($i = 0; $i < sizeof($articlenr); $i++) {
                        $sql = "UPDATE pi_ratepay_order_detail
                                SET offen = ?, storniert = ?, gesamtpreis = ?, bezahlstatus = ?, versandstatus = ?
                                WHERE ordernumber = ? AND bestell_nr = ?";
                        Shopware()->Db()->query($sql, array(
                            (int)$newOpen[$i],
                            (int)$newCanceled[$i],
                            (double)$totalAmount[$i],
                            (int)getCompleteCancelStatusId(),
                            (int)getCompleteCancelStatusId(),
                            $orderNumber,
                            $articlenr[$i]
                        ));
                        $sql = "SELECT (bestellt - storniert) FROM pi_ratepay_order_detail WHERE ordernumber = ? AND bestell_nr = ?";
                        $myorderquantity = Shopware()->Db()->fetchOne($sql, array($orderNumber, $articlenr[$i]));
                        if ($myorderquantity > 0) {
                            $sql = "UPDATE s_order_details SET quantity= ? WHERE ordernumber = ? AND articleordernumber = ?";
                            Shopware()->Db()->query($sql, array((int)$myorderquantity, $orderNumber, $articlenr[$i]));
                        } else {
                            $sql = "DELETE FROM s_order_details WHERE ordernumber = ? AND articleordernumber = ?";
                            Shopware()->Db()->query($sql, array($orderNumber, $articlenr[$i]));
                        }
                        $sql = "UPDATE pi_ratepay_order_detail SET bezahlstatus = ?, versandstatus= ?
                                WHERE ordernumber = ? AND bestell_nr = ?";
                        Shopware()->Db()->query($sql, array(
                            (int)getCompleteCancelStatusId(),
                            (int)getCompleteCancelStatusId(),
                            $orderNumber,
                            $articlenr[$i]
                        ));
                    }
                    historyEntry($orderNumber, "<b class=\"red\">Bestellung vollst&auml;ndig storniert</b>", "", "" , "");
                    $sql = "UPDATE s_order SET status = ?, invoice_shipping = ?, invoice_shipping_net = ?, dispatchID = ?
                            WHERE ordernumber = ?";
                    Shopware()->Db()->query($sql, array(
                        (int)getCompleteCancelStatusId(),
                        (double)0,
                        (double)0,
                        (int)0,
                        $orderNumber
                    ));
                    $sql = "UPDATE `pi_ratepay_orders` SET `invoice_number` ='<span class=\'red\'>Rechnung ist nicht aktuell</span>'
                            WHERE order_number = ?";
                    Shopware()->Db()->query($sql, array($orderNumber));
                    berechneGesamtpreis($orderNumber);
                    $returnValue['error'] = false;
                } else{
                    $returnValue['error'] = true;
                    $returnValue['errormessage'] = "stornieren fehl geschlagen";
                }
                echo json_encode(array('articlename' => $myflag, 'returnValue' => $returnValue, 'komplett' => getCompleteCancelStatusId()));
            }
            catch (Exception $e) {
                $returnValue['error'] = true;
                $returnValue['errormessage'] = $e->getMessage() . " (#" . $e->getCode() . ")";
                echo json_encode(array('articlename' => $myflag, 'returnValue' => $returnValue, 'komplett' => true));
            }
        } else {
            $returnValue = paymentChange($orderNumber, $articles, 'partial-cancellation');
            if ($returnValue == true) {
                $sql = "UPDATE `pi_ratepay_orders` SET `invoice_number` ='<span class=\'red\'>Rechnung ist nicht aktuell</span>'
                        WHERE order_number = ?";
                Shopware()->Db()->query($sql, array($orderNumber));
                for ($i = 0; $i < sizeof($articlenr); $i++) {
                    $sql = "UPDATE pi_ratepay_order_detail SET offen = ?, storniert = ?, gesamtpreis = ?
                            WHERE ordernumber = ? AND bestell_nr = ?";
                    Shopware()->Db()->query($sql, array(
                        (int)$newOpen[$i],
                        (int)$newCanceled[$i],
                        (double)$totalAmount[$i],
                        $orderNumber,
                        $articlenr[$i]
                    ));
                    $sql = "SELECT (bestellt - storniert) FROM pi_ratepay_order_detail WHERE ordernumber = ? AND bestell_nr = ?";
                    $myorderquantity = Shopware()->Db()->fetchOne($sql, array($orderNumber, $articlenr[$i]));
                    if ($myorderquantity > 0) {
                        $sql = "UPDATE s_order_details SET quantity= ? WHERE ordernumber = ? AND articleordernumber = ?";
                        Shopware()->Db()->query($sql, array((int)$myorderquantity, $orderNumber, $articlenr[$i]));
                    } else {
                        $sql = "DELETE FROM s_order_details WHERE ordernumber = ? AND articleordernumber = ?";
                        Shopware()->Db()->query($sql, array($orderNumber, $articlenr[$i]));
                    }
                    if ($cancelQuantity[$i] > 0) {
                        $sql = "SELECT name FROM pi_ratepay_order_detail WHERE ordernumber = ? AND bestell_nr = ?";
                        $articlename = Shopware()->Db()->fetchOne($sql, array($orderNumber, $articlenr[$i]));
                        if ($articlename == 'Versandkosten') {
                            $sql = "UPDATE s_order SET invoice_shipping = ?, invoice_shipping_net = ?, dispatchID = ? WHERE ordernumber = ?";
                            Shopware()->Db()->query($sql, array((double)0, (double)0, (int)0, $orderNumber));
                        }
                        $myarticlename = str_replace("'", "\'", $articlename);
                        $status = 0;
                        historyEntry($orderNumber, "<span class=\"red\">Artikel storniert</span>", $myarticlename, $articlenr[$i], $cancelQuantity[$i]);
                        if ($newOpen[$i] == 0 && $newSended[$i] == 0) {
                            $status=getCompleteCancelStatusId();
                        }
                        elseif ($newOpen[$i] == 0 && $newSended[$i] > 0) {
                            $status=getCompleteSentStatusID();
                        }
                        elseif ($newOpen[$i] > 0 && $newSended[$i] == 0) {
                            $status=getPartCanceledStatusId();
                        }
                        $sql = "UPDATE pi_ratepay_order_detail SET versandstatus= ? WHERE ordernumber = ? AND bestell_nr = ?";
                        Shopware()->Db()->query($sql, array((int)$status, $orderNumber, $articlenr[$i]));
                    }
                }
            }
            $sql = "SELECT sum(offen) FROM pi_ratepay_order_detail WHERE ordernumber = ?";
            $delivered = Shopware()->Db()->fetchOne($sql, array($orderNumber));
            $sql = "SELECT sum(storniert) FROM pi_ratepay_order_detail WHERE ordernumber = ?";
            $canceledRest = Shopware()->Db()->fetchOne($sql, array($orderNumber));
            $sql = "SELECT sum(bestellt) FROM pi_ratepay_order_detail WHERE ordernumber = ?";
            $orderedRest = Shopware()->Db()->fetchOne($sql, array($orderNumber));
            if ($delivered < 1) {
                setOrderState($orderNumber, getCompleteSentStatusID());
                historyEntry($orderNumber, "<b class=\"green\">Bestellung vollst&auml;ndig versendet</b>", "", "" , "");
            }
            elseif ($canceledRest == $orderedRest) {
                setOrderState($orderNumber, getCompleteCancelStatusId());
                historyEntry($orderNumber, "<b class=\"red\">Bestellung vollst&auml;ndig storniert</b>", "", "" , "");
            }
            $returnValue['error'] = false;
            $returnValue['errormessage'] = "Komplett";
            berechneGesamtpreis($orderNumber);
            echo json_encode(array("total" => $myflag, "returnValue" => $returnValue));
        }
    }

    /**
     * Gets articles for the return window. Delivers a JSON String with article details
     *
     * @see templates/backend/plugins/PigmbhRatePAYPayment/index.php
     */
    public function getReturnArticlesAction()
    {
        $this->View()->setTemplate();
        $orderNumber = $this->Request()->myordernumber;
        $articles = array();
        if (isset($this->Request()->search)) {
            $search = $this->Request()->search;
            $sql = "SELECT DISTINCT * FROM pi_ratepay_order_detail
                    WHERE ordernumber = ? AND einzelpreis <> 0 AND (name LIKE ? OR bestell_nr LIKE ?)ORDER BY name";
            $articles = Shopware()->Db()->fetchAll($sql, array($orderNumber, "%" . $search . "%", "%" . $search . "%"));
        } else {
            $sql = "SELECT * FROM pi_ratepay_order_detail WHERE geliefert > 0 AND ordernumber = '" . $orderNumber . "'";
            $articles = Shopware()->Db()->fetchAll($sql, array($orderNumber));
        }
        if (sizeof($articles) > 0) {
            for ($i = 0; $i < sizeof($articles); $i++) {
                $articles[$i]['gesamtpreis'] = number_format($articles[$i]['einzelpreis'] * $articles[$i]['anzahl'], 2, ',', '.');
                $articles[$i]['einzelpreis'] = number_format($articles[$i]['einzelpreis'], 2, ',', '.');
                $articles[$i]['name'] = htmlentities(utf8_decode($articles[$i]['name']));
                $articles[$i]['anzahl'] = $articles[$i]['geliefert'];
            }
        }
        echo json_encode(array("total" => count($articles), "items" => $articles));
    }

    /**
     * Returns articles from the order. Delivers a JSON String with article details
     *
     * @see templates/backend/plugins/PigmbhRatePAYPayment/index.php
     * @throws RatePAYException
     */
    public function returnArticlesAction()
    {
        $this->View()->setTemplate();
        $articlenr = explode(";", $this->Request()->articlenr);
        $quantity = explode(";", $this->Request()->anzahl);
        $orderNumber = $this->Request()->myordernumber;
        $completeQuantity = 0;
        $articles = array();
        $myerror = array('error' => false, 'errormessage' => 'Fehler beim retournieren der Artikel');
        $sql = "SELECT SUM(anzahl-storniert-retourniert) FROM pi_ratepay_order_detail WHERE ordernumber = ?";
        $articlesquantity = Shopware()->Db()->fetchOne($sql, array($orderNumber));
        $allarticles = $this->_getArticlesByOrderNumber($orderNumber);
        for ($counter = 0; $counter < sizeof($articlenr); $counter++) {
            for ($j = 0; $j < sizeof($allarticles); $j++) {
                if ($articlenr[$counter] == $allarticles[$j]['bestell_nr']) {
                    $allarticles[$j]['retourniert'] = $allarticles[$j]['retourniert'] + $quantity[$counter];
                    $allarticles[$j]['geliefert'] = $allarticles[$j]['geliefert'] - $quantity[$counter];
                    $allarticles[$j]['anzahl'] = $quantity[$counter];
                }
            }
            $completeQuantity+=$quantity[$counter];
        }
        $totalreturn = 0;
        $totalordered = 0;
        for ($counter = 0; $counter < sizeof($allarticles); $counter++) {
            $articles[$counter]['name'] = $allarticles[$counter]['name'];
            $articles[$counter]['geliefert'] = $allarticles[$counter]['geliefert'];
            $articles[$counter]['bestellt'] = $allarticles[$counter]['bestellt'];
            $articles[$counter]['storniert'] = $allarticles[$counter]['storniert'];
            $articles[$counter]['anzahl'] = $allarticles[$counter]['anzahl'];
            $articles[$counter]['quantity'] = $allarticles[$counter]['bestellt'];
            $articles[$counter]['offen'] = $allarticles[$counter]['offen'];
            $articles[$counter]['retourniert'] = $allarticles[$counter]['retourniert'];
            $articles[$counter]['bestellt'] = $allarticles[$counter]['bestellt'];
            $articles[$counter]['einzelpreis'] = $allarticles[$counter]['einzelpreis'];
            $articles[$counter]['bestellnr'] = $allarticles[$counter]['bestell_nr'];
            $articles[$counter]['ordernumber'] = $allarticles[$counter]['bestell_nr'];
            $articles[$counter]['einzelpreis_net'] = $allarticles[$counter]['einzelpreis_net'];
            $totalreturn+=$articles[$counter]['retourniert'];
            $totalordered+=$articles[$counter]['bestellt'];
        }
        if (sizeof($articlenr) > 0) {
            $articlesquantity == $completeQuantity? $subtype='full-return': $subtype='partial-return';
            try {
                $myerror['error'] = paymentChange($orderNumber, $articles, $subtype);
            } catch (Exception $e) {
                $myerror['error'] = false;
                $myerror['errormessage'] = $e->getMessage() . " (#" . $e->getCode() . ")";
                echo json_encode(array("items" => $myerror));
            }
            if ($myerror['error'] == true) {
                $sql = "UPDATE `pi_ratepay_orders` SET `invoice_number` ='<span class=\'red\'>Rechnung ist nicht aktuell.</span>'
                        WHERE order_number = ?";
                Shopware()->Db()->query($sql, array($orderNumber));
                for ($counter = 0; $counter < sizeof($articlenr); $counter++) {
                    if ($quantity[$counter] > 0) {
                        $sql = "SELECT retourniert FROM pi_ratepay_order_detail WHERE ordernumber = ? AND bestell_nr = ?";
                        $returned = Shopware()->Db()->fetchOne($sql, array($orderNumber, $articlenr[$counter]));
                        $sql = "SELECT geliefert FROM pi_ratepay_order_detail WHERE ordernumber = ? AND bestell_nr = ?";
                        $delivered = Shopware()->Db()->fetchOne($sql, array($orderNumber, $articlenr[$counter]));
                        $sql = "UPDATE pi_ratepay_order_detail SET retourniert = ?, geliefert= ? WHERE ordernumber = ? AND bestell_nr = ?";
                        Shopware()->Db()->query($sql, array(
                            $returned + (int)$quantity[$counter],
                            $delivered - (int)$quantity[$counter],
                            $orderNumber,
                            $articlenr[$counter]
                        ));
                        $sql = "UPDATE pi_ratepay_order_detail SET gesamtpreis = geliefert * einzelpreis
                                WHERE ordernumber = ? AND bestell_nr = ?";
                        Shopware()->Db()->query($sql, array(
                            $orderNumber,
                            $articlenr[$counter]
                        ));
                        $sql = "SELECT * FROM s_order_details WHERE ordernumber = ? AND articleordernumber = ?";
                        $ratepayOrder= Shopware()->Db()->fetchRow($sql, array($orderNumber, $articlenr[$counter]));
                        if (($ratepayOrder['quantity'] - $quantity[$counter]) > 0) {
                            $sql = "SELECT quantity FROM s_order_details WHERE ordernumber = ? AND articleordernumber = ?";
                            $quant = Shopware()->Db()->fetchOne($sql, array($orderNumber, $articlenr[$counter]));
                            $sql = "UPDATE s_order_details SET quantity = ? WHERE ordernumber = ? AND articleordernumber = ?";
                            $newQuant = $quant - (int)$quantity[$counter];
                            Shopware()->Db()->query($sql, array($newQuant, $orderNumber, $articlenr[$counter]));
                        } else {
                            $sql = "DELETE FROM s_order_details WHERE ordernumber = ? AND articleordernumber = ?";
                            Shopware()->Db()->query($sql, array($orderNumber, $articlenr[$counter]));
                        }
                        $sql = "SELECT * FROM pi_ratepay_order_detail WHERE ordernumber = ? AND bestell_nr = ?";
                        $ratepayOwnOrder= Shopware()->Db()->fetchRow($sql, array($orderNumber, $articlenr[$counter]));
                        if ($ratepayOwnOrder['bestellt'] == $ratepayOwnOrder['retourniert']) {
                            $sql = "UPDATE pi_ratepay_order_detail SET versandstatus= ? WHERE ordernumber = ? AND bestell_nr= ?";
                            Shopware()->Db()->query($sql, array((int)getCompleteReturnStatusId(), $orderNumber, $articlenr[$counter]));
                        }
                        if ($ratepayOwnOrder['name'] == 'Versandkosten') {
                            $sql = "UPDATE s_order SET invoice_shipping = ?, invoice_shipping_net = ?, dispatchID = ? WHERE ordernumber = ?";
                            Shopware()->Db()->query($sql, array((double)0, (double)0, (int)0, $orderNumber));
                        }
                        $sql = " SELECT name FROM pi_ratepay_order_detail WHERE ordernumber = ? AND bestell_nr = ?";
                        $myarticlename = Shopware()->Db()->fetchOne($sql, array($orderNumber, $articlenr[$counter]));
                        $myarticlename = str_replace("'", "\'", $myarticlename);
                        historyEntry($orderNumber, "<span class=\"red\">Artikel retourniert</span>", $myarticlename, $articlenr[$counter] , $quantity[$counter]);
                    }
                }
            }
        }
        berechneGesamtpreis($orderNumber);
        $sql = "SELECT invoice_amount FROM s_order WHERE ordernumber = ?";
        $orderamount = Shopware()->Db()->fetchOne($sql, array($orderNumber));
        if ($orderamount == 0) {
            setOrderState($orderNumber, getCompleteReturnStatusId());
            historyEntry($orderNumber, "<b class=\"red\">Bestellung vollst&auml;ndig retourniert</b>", "" , "" , "");
        }
        echo json_encode(array("items" => $myerror, "articlesquantity" => $articlesquantity));
    }

    /**
     * Gets invoices for the invoice window. Delivers a JSON String with invoice details
     *
     * @see templates/backend/plugins/PigmbhRatePAYPayment/index.php
     */
    public function getInvoicesAction()
    {
        $this->View()->setTemplate();
        $orderNumber = $this->Request()->myordernumber;
        $sql = "SELECT * FROM `pi_ratepay_bills` WHERE order_number = ? and type = ?";
        $bills = Shopware()->Db()->fetchAll($sql, array($orderNumber, (int)0));
        $stonoBill = Shopware()->Db()->fetchRow($sql, array($orderNumber, (int)3));
        $bills[0]['id'] = 1;
        $sql = "SELECT invoice_number FROM `pi_ratepay_orders` WHERE order_number = ?";
        $bills[0]['method'] = Shopware()->Db()->fetchOne($sql, array($orderNumber));
        $sql = "SELECT invoice_amount FROM `pi_ratepay_bills` WHERE order_number = ? AND `type` = ?";
        $bills[0]['invoice_amount'] = Shopware()->Db()->fetchOne($sql, array($orderNumber, (int)0));
        $sql = "SELECT invoice_amount FROM `s_order` WHERE ordernumber = ?";
        $bills[0]['invoice_amount_new'] = Shopware()->Db()->fetchOne($sql, array($orderNumber));
        $bills[0]['invoice_amount'] = number_format($bills[0]['invoice_amount'], 2, ',', '.');
        $bills[0]['invoice_amount_new'] = number_format($bills[0]['invoice_amount_new'], 2, ',', '.');
        if($bills[0]['invoice_amount_new']==$bills[0]['invoice_amount']){
            $bills[0]['invoice_amount_new']="Die Rechnung ist aktuell";
        }
        $bills[0]['open'] = Shopware()->DocPath() . "files/documents/" . $bills[$i]['invoice_hash'] . ".pdf";
        $sql = "SELECT invoice_amount FROM `pi_ratepay_bills` WHERE order_number = ? AND `type` = ?";
        $bills[0]['stornoAmount'] = Shopware()->Db()->fetchOne($sql, array($orderNumber, (int)3));
        $sql = "SELECT SUM((`einzelpreis` * `bestellt`) *(-1)) FROM `pi_ratepay_order_detail` WHERE `ordernumber` = ?";
        $bills[0]['stornoAmount'] = Shopware()->Db()->fetchOne($sql, array($orderNumber));
        $bills[0]['stornoAmount'] = number_format($bills[0]['stornoAmount'], 2, ',', '.');
        $bills[0]['stornoOpen'] = Shopware()->DocPath() . "files/documents/" . $stonoBill['invoice_hash'] . ".pdf";
        $bills[0]['stornoHash'] = $stonoBill['invoice_hash'];
        echo json_encode(array("total" => count($bills), "items" => $bills));
    }

    /**
     * deletes invoice.
     *
     * @see templates/backend/plugins/PigmbhRatePAYPayment/index.php
     * @throws RatePAYException
     */
    public function getNewInvoiceAction()
    {
        $this->View()->setTemplate();
        $orderNumber = $this->Request()->ordernumber;
        $type = $this->Request()->type;
        $orderID =$this->_getOrderIdByNumber($orderNumber);
        $totalSendAmount = 0;
        $sql = "SELECT docID FROM s_order_documents WHERE orderID = ? AND type = ?";
        $bid = Shopware()->Db()->fetchOne($sql, array((int)$orderID, (int)0));
        $dispatch = $this->_getDispatchId($orderNumber);
        $dispatch > 0? $shippingflag = true: $shippingflag = false;
        $document = Shopware_Components_Document::initDocument($orderID, $type, array(
            "netto" => false,
            "date" => date("Y-m-d H:i:s"),
            "shippingCostsAsPosition" => $shippingflag,
            "bid" => $bid,
            "_renderer" => "pdf"
        ));
        $document->render();
        $sql = "UPDATE `pi_ratepay_orders` SET `invoice_number` = '<span class=\'green\'>Rechnung ist aktuell</span>' WHERE order_number = ?";
        Shopware()->Db()->query($sql, array($orderNumber));
        if($bid==3){
            $sql = "SELECT SUM(einzelpreis * bestellt)) FROM `pi_ratepay_order_detail` WHERE `ordernumber` = ?";
        } else {
            $sql = "SELECT SUM(`einzelpreis` *(bestellt-storniert-retourniert)) FROM `pi_ratepay_order_detail` WHERE `ordernumber` = ?";
        }
        $totalSendAmount = Shopware()->Db()->fetchOne($sql, array($orderNumber));
        $sql = "UPDATE `pi_ratepay_bills` SET `invoice_amount` = ? WHERE `order_number` = ? AND `type` = ?";
        Shopware()->Db()->query($sql, array((double)$totalSendAmount, $orderNumber, (int)$type));
        echo json_encode(array("total" => count($document), "items" => $document));
    }

    /**
     * Gets data for stats. Delivers JASON String
     *
     * @see templates/backend/plugins/PigmbhRatePAYPayment/index.php
     * @throws RatePAYException
     */
    public function getStatsOrderAction()
    {
        $this->View()->setTemplate();
        $orders = array();
        $orders[0]['bestellstatus'] = 'Anzahl der Bestellungen die von RatePAY akzeptiert wurden';
        $orders[1]['bestellstatus'] = 'Anzahl der Bestellungen die von RatePAY abgelehnt wurden';
        $sql = "SELECT COUNT('accepted') FROM pi_ratepay_stats WHERE accepted = 1";
        $orders[0]['total'] = Shopware()->Db()->fetchOne($sql);
        $sql = "SELECT COUNT('notaccepted') FROM pi_ratepay_stats WHERE notaccepted = 1";
        $orders[1]['total'] = Shopware()->Db()->fetchOne($sql);
        echo json_encode(array("total" => count($orders), "items" => $orders));
    }

    /**
     * Gets data for stats. Delivers JSON String
     *
     * @see templates/backend/plugins/PigmbhRatePAYPayment/index.php
     * @throws RatePAYException
     */
    public function getStatsOrderZahlAction()
    {
        $this->View()->setTemplate();
        $sql = "SELECT d.description AS bestellstatus, d.id AS id FROM s_order a
                INNER JOIN s_core_states d ON a.status = d.id
                WHERE a.paymentID = ? OR a.paymentID = ? OR a.paymentID = ?
                GROUP BY bestellstatus";
        $orders = Shopware()->Db()->fetchAll($sql, array((int)getInvoicePaymentId(), (int)getRatePaymentId(), (int)getDebitPaymentId()));

        foreach ($orders as $key => $order) {
            $searchArray=Array(
                '<span class=\"ratepaystate\" style=\"color:green\">',
                '<span class=\"ratepaystate\" style=\"color:orange\">',
                '<span class=\"ratepaystate\" style=\"color:red\">',
                '</span>',
            );
            $replaceArray=Array('','','','');
            $order['bestellstatus']=str_replace($searchArray, $replaceArray, $order['bestellstatus']);
            if($order['bestellstatus']=='<span class="ratepaystate" style="color:red">Komplett retourniert</span>'){
                $order['bestellstatus']="Komplett retourniert";
            }
            $sql = "SELECT COUNT('status') FROM s_order
                    WHERE paymentID = ? AND status = ?
                    OR(paymentID = ? AND status = ?)
                    OR(paymentID = ? AND status = ?)";
            $order['total'] = Shopware()->Db()->fetchOne($sql, array(
                (int)getInvoicePaymentId(),
                (int)$order['id'],
                (int)getRatePaymentId(),
                (int)$order['id'],
                (int)getDebitPaymentId(),
                (int)$order['id']
            ));
            $order[$key]= $order;
        }
        echo json_encode(array("total" => count($orders), "items" => $orders));
    }

    /**
     * Gets data for stats. Delivers JASON String
     *
     * @see templates/backend/plugins/PigmbhRatePAYPayment/index.php
     * @throws RatePAYException
     */
    public function getStatsOrderInvoiceAction()
    {
        $this->View()->setTemplate();
        $sql = "SELECT invoice_amount, ordernumber FROM s_order  WHERE paymentID = ? OR paymentID = ? OR paymentID = ? ORDER BY invoice_amount";
        $orders = Shopware()->Db()->fetchAll($sql, array((int)getInvoicePaymentId(), (int)getRatePaymentId(), (int)getDebitPaymentId()));
        $myordercounter = 0;
        for ($i = 0; $i < sizeof($orders); $i++) {
            $orders[$i]['total'] = intval($orders[$i]['invoice_amount']);
            $myordercounter+=100;
        }
        echo json_encode(array("mytotal" => count($orders), "items" => $orders));
    }

    /**
     * Gets data for stats. Delivers JASON String
     *
     * @see templates/backend/plugins/PigmbhRatePAYPayment/index.php
     * @throws RatePAYException
     */
    public function getStatsOrderInvoiceOrderedAction()
    {
        $this->View()->setTemplate();
        $sql = "SELECT invoice_amount, ordernumber FROM s_order WHERE paymentID = ? OR paymentID = ? OR paymentID = ? ORDER BY ordernumber";
        $orders = Shopware()->Db()->fetchAll($sql, array((int)getInvoicePaymentId(), (int)getRatePaymentId(), (int)getDebitPaymentId()));
        $myordercounter = 0;
        for ($i = 0; $i < sizeof($orders); $i++) {
            $orders[$i]['total'] = intval($orders[$i]['invoice_amount']);
            $myordercounter+=100;
        }
        echo json_encode(array("mytotal" => count($orders), "items" => $orders));
    }

    /**
     * Gets data for stats. Delivers JASON String
     *
     * @see templates/backend/plugins/PigmbhRatePAYPayment/index.php
     * @throws RatePAYException
     */
    public function getStatsUmsatzKundeAction()
    {
        $this->View()->setTemplate();
        $sql = "SELECT userID FROM s_order WHERE status != -1 AND (paymentID = ? OR paymentID = ? OR paymentID =?) GROUP BY userID";
        $myordercounter = 0;
        $orders = array();
        $customer = Shopware()->Db()->fetchAll($sql, array((int)getInvoicePaymentId(), (int)getRatePaymentId(), (int)getDebitPaymentId()));
        for ($i = 0; $i < sizeof($customer); $i++) {
            $sql = "SELECT SUM(invoice_amount) FROM s_order
                    WHERE userID = ? AND status != -1 AND (paymentID = ? OR paymentID = ? OR paymentID =?) LIMIT 1";
            $orders[$i]['invoice_amount'] = Shopware()->Db()->fetchOne($sql,array(
                        (int)$customer[$i]['userID'],
                        (int)getInvoicePaymentId(),
                        (int)getRatePaymentId(),
                        (int)getDebitPaymentId()
            ));
            if (!$orders[$i]['invoice_amount']) $orders[$i]['invoice_amount'] = 0.00;
            $sql = "SELECT lastname FROM s_user_billingaddress WHERE userID = ? LIMIT 1";
            $orders[$i]['customer'] = Shopware()->Db()->fetchOne($sql,array((int)$customer[$i]['userID']));
            $orders[$i]['customer'] = htmlentities($orders[$i]['customer']);
            $orders[$i]['total'] = intval($orders[$i]['invoice_amount']);
            $myordercounter = max($orders[$i]['invoice_amount']);
        }
        echo json_encode(array("mytotal" => $myordercounter, "items" => $orders));
    }

    /**
     * Gets data for stats. Delivers JASON String
     *
     * @see templates/backend/plugins/PigmbhRatePAYPayment/index.php
     * @throws RatePAYException
     */
    public function getStatsUmsatzMonatAction()
    {
        $this->View()->setTemplate();
        $sql = "SELECT MONTH(ordertime) as month ,SUM(invoice_amount) as invoice_amount
                FROM s_order WHERE paymentID = ? OR paymentID = ? OR paymentID = ? GROUP BY month";
        $counter = Shopware()->Db()->fetchAll($sql, array((int)getInvoicePaymentId(), (int)getRatePaymentId(), (int)getDebitPaymentId()));
        $counter > 12? $start = $counter - 12: $start = 0;
        $sql = "SELECT MONTH(ordertime) as month ,SUM(invoice_amount) as invoice_amount FROM s_order
                WHERE paymentID = ? OR paymentID = ? OR paymentID = ? GROUP BY month
                ORDER BY ordertime LIMIT " . (int)$start . ",12";
        $orders = Shopware()->Db()->fetchAll($sql, array((int)getInvoicePaymentId(), (int)getRatePaymentId(), (int)getDebitPaymentId()));
        for ($i = 0; $i < sizeof($orders); $i++) {
            $orders[$i]['total'] = intval($orders[$i]['invoice_amount']);
            switch ($orders[$i]['month']) {
                case 1:
                    $orders[$i]['month'] = 'Januar';
                    break;
                case 2:
                    $orders[$i]['month'] = 'Februar';
                    break;
                case 3:
                    $orders[$i]['month'] = 'M&auml;rz';
                    break;
                case 4:
                    $orders[$i]['month'] = 'April';
                    break;
                case 5:
                    $orders[$i]['month'] = 'Mai';
                    break;
                case 6:
                    $orders[$i]['month'] = 'Juni';
                    break;
                case 7:
                    $orders[$i]['month'] = 'Juli';
                    break;
                case 8:
                    $orders[$i]['month'] = 'August';
                    break;
                case 9:
                    $orders[$i]['month'] = 'September';
                    break;
                case 10:
                    $orders[$i]['month'] = 'Oktober';
                    break;
                case 11:
                    $orders[$i]['month'] = 'November';
                    break;
                case 12:
                    $orders[$i]['month'] = 'Dezember';
                    break;
                default:
                    $orders[$i]['month'] = "Monat";
            }
        }
        echo json_encode(array("mytotal" => $counter, "items" => $orders));
    }

    /**
     * gets dispatch ID from current order
     *
     * @params  String  $ordernumber  Ordernumber
     * @return  int                   Dispatch ID
     */
    private function _getDispatchId($ordernumber)
    {
        $sql = "SELECT dispatchID FROM s_order WHERE ordernumber = ?";
        return Shopware()->Db()->fetchOne($sql, array($ordernumber));
    }
    /**
     * gets dispatch name from current order
     *
     * @params  String  $dispatchId   dispatch id
     * @return  int                   User ID
     */
    private function _getDispatchName($dispatchId)
    {
        $sql = "SELECT name FROM s_premium_dispatch WHERE id = ?";
        return Shopware()->Db()->fetchOne($sql, array((int)$dispatchId));
    }

    /**
     * gets description of invoice payment method
     * @return  int                   User ID
     */
    private function _getInvoiceDescription()
    {
        $sql = "Select description FROM s_core_paymentmeans WHERE name = ?";
        return Shopware()->Db()->fetchOne($sql, array('RatePAYInvoice'));
    }

    /**
     * gets description of debit payment method
     * @return  int                   User ID
     */
    private function _getDebitDescription()
    {
        $sql = "Select description FROM s_core_paymentmeans WHERE name = ?";
        return Shopware()->Db()->fetchOne($sql, array('RatePAYDebit'));
    }

    /**
     * gets userid from current order
     *
     * @params  String  $ordernumber  Ordernumber
     * @return  int                   User ID
     */
    private function _getUserId($ordernumber)
    {
        $sql = "SELECT DISTINCT b.userID FROM s_user_billingaddress a
                INNER JOIN s_order b on a.userID = b.userID
                WHERE a.userID = b.userID AND ordernumber = ?";
        return Shopware()->Db()->fetchOne($sql, array($ordernumber));
    }

    /**
     * gets image path from current payment method
     *
     * @params  String  $paymentMethod  Current payment method
     * @return  String                  Plugin img path
     */
    private function _getImgPath($paymentMethod)
    {
        if ($paymentMethod == $this->_getInvoiceDescription()){
            $img = $this->View()->fetch('string:{link file=' . var_export( '/engine/Shopware/Plugins/Default/Frontend/PigmbhRatePAYPayment/img/Logo_Ratepay_Rechnung_01_Final_RGB_Farbe_01_Xtrasmall.png', true) . ' fullPath}');
            return $img;
        }elseif ($paymentMethod == $this->_getDebitDescription()){
            $img = $this->View()->fetch('string:{link file=' . var_export( '/engine/Shopware/Plugins/Default/Frontend/PigmbhRatePAYPayment/img/Logo_Ratepay_Lastschrift_01_Final_RGB_Farbe_01_Xtrasmall.png', true) . ' fullPath}');
            return $img;
        } else {
            $img = $this->View()->fetch('string:{link file=' . var_export( '/engine/Shopware/Plugins/Default/Frontend/PigmbhRatePAYPayment/img/Logo_Ratepay_Ratenzahlung_01_Final_RGB_Farbe_01_Xtrasmall.png', true) . ' fullPath}');
            return $img;
        }
    }

    /**
     * Creates Cancel invoice
     *
     * @return  String  $ordernumber   Ordernumber
     * @params  String  $amount        new amount
     */
     private function _createCancelInvoice($orderNumber, $amount)
    {
        $orderID =$this->_getOrderIdByNumber($orderNumber);
        $dispatch = $this->_getDispatchId($orderNumber);
        $shippingflag = $dispatch > 0?  true: false;
        $sql = "SELECT * FROM s_order_documents WHERE orderID = ? AND type= ?";
        $getDocument = Shopware()->Db()->fetchRow($sql, array((int)$orderID, (int)3));
        $sql = "SELECT docID FROM s_order_documents WHERE orderID = ? AND type= ?";
        $bid = Shopware()->Db()->fetchOne($sql, array((int)$orderID, (int)3));
        if(!$getDocument){
            $document = Shopware_Components_Document::initDocument($orderID, 3, array(
                "netto" => false,
                "date" => date("Y-m-d H:i:s"),
                "shippingCostsAsPosition" => $shippingflag,
                "bid"=>$bid,
                "_renderer" => "pdf"
            ));
            $document->render();
            $sql = "SELECT * FROM s_order_documents WHERE orderID = ? AND type= ?";
            $getDocument = Shopware()->Db()->fetchRow($sql, array((int)$orderID, (int)3));
            $sql = "INSERT INTO `pi_ratepay_bills`(`order_id`, `order_number`, `invoice_amount`, `invoice_hash`, `type`)
                    VALUES(?, ?, ?, ?, ?)	";
            Shopware()->Db()->query($sql, array($orderID, $orderNumber, (double)$amount, $getDocument['hash'],(int)3));
        }
        else{
            $sql = "UPDATE pi_ratepay_bills SET invoice_hash = ? WHERE order_number = ? AND type = ?";
            Shopware()->Db()->query($sql, array($getDocument['hash'], $orderNumber,(int)3));
        }
    }

     /**
     * gets orderid by ordernumber
     *
     * @params  String  $orderNumber    Ordernumber
     * @return  String                  Order Id
     */
    private function _getOrderIdByNumber($orderNumber)
    {
        $sql = "SELECT id FROM s_order WHERE ordernumber = ?";
        return Shopware()->Db()->fetchOne($sql, array($orderNumber));
    }

    /**
     * gets articles by ordernumber
     *
     * @params  String  $orderNumber    Ordernumber
     * @return  Array                  Articles
     */
    private function _getArticlesByOrderNumber($orderNumber)
    {
        $sql = "SELECT * FROM pi_ratepay_order_detail WHERE ordernumber = ?";
        return Shopware()->Db()->fetchAll($sql, array($orderNumber));
    }

    /**
     * gets articles by ordernumber
     *
     * @params  String  $orderNumber    Ordernumber
     * @return  String                  Transaction ID
     */
    private function _getTransactionIdByOrderNumber($orderNumber)
    {
        $sql = "SELECT transactionID FROM s_order WHERE ordernumber = ?";
        return Shopware()->Db()->fetchOne($sql, array($orderNumber));
    }

     /**
     * removes spans vom string
     *
     * @params  String  $stringToReplace    String to replace
     * @return  String  $replacedString     replaced String
     */
    private function _removeSpans($stringToReplace)
    {
        $replacedString =  str_replace('<span class="ratepaystate" style="color:red">', '', $stringToReplace);
        $replacedString =  str_replace('<span class="ratepaystate" style="color:orange">', '', $stringToReplace);
        $replacedString =  str_replace('<span class="ratepaystate" style="color:green">', '', $stringToReplace);
        $replacedString =  str_replace('</span>', '', $replacedString);
        return $replacedString;
    }
}