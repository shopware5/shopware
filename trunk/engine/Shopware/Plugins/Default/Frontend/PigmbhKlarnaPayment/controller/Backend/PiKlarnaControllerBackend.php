<?php

class Shopware_Controllers_Backend_PiPaymentKlarnaBackend extends Enlight_Controller_Action
{

    /**
     * Adds template directory to the view
     *
     * @return void
     */
    public function init()
    {
        Shopware()->Template()->addTemplateDir(dirname(__FILE__) . '/Views/'); 
    }

    /**
     * Loads index.php(Javascript part) for Klarna orderwindow
     *
     * @return void
     */
    public function indexAction()
    {
        $this->View()->loadTemplate("Backend/PigmbhKlarnaPayment/index.tpl");
    }

    /**
     * Loads Skeleton for Klarna orderwindow
     *
     * @return void
     */
    public function skeletonAction()
    {
        $this->View()->loadTemplate("Backend/PigmbhKlarnaPayment/skeleton.tpl");
    }

    /**
     * Gets all orders that where payed with Klarna and delivers a JSON String.
     * Checks all orders that have the "pending" state and changes the state if neccesary.
     *
     * @see templates/backend/plugins/PigmbhKlarnaPayment/index.php
     * @return void
     */
    public function getOrdersAction()
    {
        piKlarnaCheckPendingOrders();
        $piKlarnaInvoiceId = piKlarnaGetInvoicePaymentId();
        $piKlarnaRateId    = piKlarnaGetRatePaymentId();
        $this->View()->setTemplate();

        $piKlarnaStart            = isset($this->Request()->start) ? $this->Request()->start : 0;
        $piKlarnaLimit            = isset($this->Request()->limit) ? $this->Request()->limit : 10;
        $piKlarnaAcceptedStatusId = piKlarnaGetAcceptedStatusId();

        if ($this->Request()->nurbezahlt == 'true') {
            $piKlarnaZahlString = "AND a.cleared = " . (int)$piKlarnaAcceptedStatusId . "";
        } else {
            $piKlarnaZahlString = "";
        }
        if (isset($this->Request()->search)) {
            $piKlarnaSearch = mysql_escape_string($this->Request()->search);
        } else {
            $piKlarnaSearch = '';
        }
        if (isset($this->Request()->suchenach)) {
            $piKlarnaSearchFor = $this->Request()->suchenach;
        } else {
            $piKlarnaSearchFor = 1;
        }
        if ($piKlarnaSearch == '') {
            $piKlarnaSearchString = "a.transactionID LIKE '%%'";
        } else {
            switch($piKlarnaSearchFor) {
                case 1:
                    $piKlarnaSearchString = "a.ordernumber LIKE '%" . $piKlarnaSearch . "%'" . "OR a.transactionID LIKE '%" . $piKlarnaSearch . "%'" . "OR d.description LIKE '%" . $piKlarnaSearch . "%'" . "OR b.lastname LIKE '%" . $piKlarnaSearch . "%' ";
                    break;
                case 2:
                    $piKlarnaSearchString = "a.ordernumber LIKE '%" . $piKlarnaSearch . "%'";
                    break;
                case 3:
                    $piKlarnaSearchString = "a.transactionID LIKE '%" . $piKlarnaSearch . "%'";
                    break;
                case 4:
                    $piKlarnaSearchString = "d.description LIKE '%" . $piKlarnaSearch . "%'";
                    break;
                case 5:
                    $piKlarnaSearchString = "b.lastname LIKE '%" . $piKlarnaSearch . "%'";
                    break;
                default:
                    $piKlarnaSearchString = "a.ordernumber LIKE '%" . $piKlarnaSearch . "%'" . " OR a.transactionID LIKE '%" . $piKlarnaSearch . "%'" . " OR d.description LIKE '%" . $piKlarnaSearch . "%'" . " OR b.lastname LIKE '%" . $piKlarnaSearch . "%' ";
            }
        }
        $sql = "SELECT DISTINCT
                a.id AS id,
                a.ordertime AS bestellzeit,
                a.ordernumber AS bestellnr,
                a.transactionID AS transaktion,
                a.invoice_amount AS betrag,
                b.lastname AS kunde,
                c.description AS zahlstatus,
                d.description AS bestellstatus,
                e.description AS zahlart,
                f.name AS sprache
            FROM
                s_order AS a
            LEFT JOIN
                s_order_billingaddress b ON a.userID = b.UserID
            LEFT JOIN
                s_core_states c ON a.cleared = c.id
            LEFT JOIN
                s_core_states d ON a.status = d.id
            LEFT JOIN
                s_core_paymentmeans e ON a.paymentID = e.id
            LEFT JOIN
                s_core_multilanguage f ON a.language = f.isocode
            WHERE ".$piKlarnaSearchString."
            ".$piKlarnaZahlString."
            AND
            (
                    a.paymentID = ?
                OR  a.paymentID = ?
            )
            AND a.cleared !='Abgebrochen'
            AND b.orderID = a.id 
            ORDER BY
                a.ordertime desc
            LIMIT ".(int)$piKlarnaStart.",".(int)$piKlarnaLimit."
        ";
        $piKlarnaOrders = Shopware()->Db()->fetchAll($sql, array((int)$piKlarnaInvoiceId, (int)$piKlarnaRateId));
        $piKlarnaStringPosition = strpos($sql, 'LIMIT');
        $piKlarnaText = substr($sql, 0, $piKlarnaStringPosition);       
        $piKlarnaTotal = Shopware()->Db()->fetchAll($piKlarnaText, array((int)$piKlarnaInvoiceId, (int)$piKlarnaRateId));
        for ($i = 0; $i < sizeof($piKlarnaOrders); $i++) {
            $piKlarnaDispatchId = piKlarnaGetDispatchId($piKlarnaOrders[$i]['bestellnr']);
            if ($piKlarnaDispatchId == 0) {
                $piKlarnaOrders[$i]['versand'] = 'Keine Versandkosten';
            }
            else {
                $sql="SELECT name FROM s_premium_dispatch WHERE id  = ?";
                $piKlarnaOrders[$i]['versand'] = Shopware()->Db()->fetchOne($sql,array($piKlarnaDispatchId));
            }
            $sql = "SELECT DISTINCT b.userID
                    FROM s_user_billingaddress a
                    INNER JOIN s_order b on a.userID = b.userID
                    WHERE a.userID = b.userID
                    AND ordernumber = ?";
            $piKlarnaOrders[$i]['userid'] = Shopware()->Db()->fetchOne($sql,array($piKlarnaOrders[$i]['bestellnr']));
            $piKlarnaOrders[$i]['klarnaid'] = $i + 1;
            $piKlarnaOrders[$i]['betrag'] = number_format($piKlarnaOrders[$i]['betrag'], 2, ',', '.');
            $piKlarnaOrders[$i]['options_work'] = '&nbsp;';
            $piKlarnaOrders[$i]['kunde'] = htmlentities($piKlarnaOrders[$i]['kunde'], ENT_COMPAT | ENT_HTML401,'UTF-8');
            $piKlarnaOrders[$i]['bestellstatus_kurz'] = $piKlarnaOrders[$i]['bestellstatus'];
            if($piKlarnaOrders[$i]['bestellstatus']=='Komplett ausgeliefert'){
                $piKlarnaOrders[$i]['bestellstatus']='<span style="color:green">Komplett ausgeliefert</span>';
            }
            elseif($piKlarnaOrders[$i]['bestellstatus']=='Teilweise ausgeliefert'){
                $piKlarnaOrders[$i]['bestellstatus']='<span style="color:orange">Teilweise ausgeliefert</span>';
            }
            $piKlarnaOrders[$i]['options_delete'] = '&nbsp;';
            $klarnaimg="";
            if ($piKlarnaOrders[$i]['zahlart'] == 'Klarna Rechnung') {
                $klarnaimg = $this->View()->fetch('string:{link file=' . var_export( '/engine/Shopware/Plugins/Default/Frontend/PigmbhKlarnaPayment/img/de/KlarnaInvoiceLogoSmall.png', true) . ' fullPath}'); 
            }
            else {
                $klarnaimg = $this->View()->fetch('string:{link file=' . var_export( '/engine/Shopware/Plugins/Default/Frontend/PigmbhKlarnaPayment/img/de/KlarnaRatepayLogoSmall.png', true) . ' fullPath}');
            }
            $piKlarnaOrders[$i]['options_klarna'] = '
                <a class ="pencil myonclick" onclick="orderwindow(' . $piKlarnaOrders[$i]['id'] . ','
                                                                    . $piKlarnaOrders[$i]['bestellnr'] . ',\''
                                                                    . $piKlarnaOrders[$i]['kunde'] . '\')">
                    <img class="Klarna_order_img" src="' . $klarnaimg . '" width="70px";/>
                </a>
            ';
        }
        echo json_encode(array(
            "total" => count($piKlarnaTotal),
            "items" => $piKlarnaOrders
        ));
    }

    /**
     * Gets all articles for selected order and delivers all article details as JSON String.
     *
     * @see templates/backend/plugins/PigmbhKlarnaPayment/index.php
     * @return void
     */
    public function getArticlesAction()
    {
        $piKlarnaOrderNumber = $this->Request()->myordernumber;
        $sql = "SELECT id
                FROM s_order_details
                WHERE ordernumber = ?";
        $piKlarnaOrderDetailsId = Shopware()->Db()->fetchOne($sql, array($piKlarnaOrderNumber));
        $this->View()->setTemplate();
        if (isset($this->Request()->start)) $piKlarnaStart = $this->Request()->start;
        else $piKlarnaStart = 0;
        if (isset($this->Request()->limit)) $piKlarnaLimit = $this->Request()->limit;
        else $piKlarnaLimit = 8;
        if (isset($this->Request()->search)) $piKlarnaSearch ="%". $this->Request()->search."%";
        else $piKlarnaSearch = "%%";
        $sql = "
            SELECT DISTINCT *
            FROM Pi_klarna_payment_order_detail
            WHERE ordernumber = ?
            AND
            (
                name LIKE ?
                OR
                bestell_nr LIKE ?
            )
            ORDER BY name
            LIMIT ".(int)$piKlarnaStart.", ".(int)$piKlarnaLimit."
        ";
        $piKlarnaArticles = Shopware()->Db()->fetchAll($sql,array($piKlarnaOrderNumber, $piKlarnaSearch, $piKlarnaSearch));
        $piKlarnaStringPosition = strpos($sql, 'LIMIT');
        $piKlarnaText = substr($sql, 0, $piKlarnaStringPosition);
        $piKlarnaTotal = Shopware()->Db()->fetchAll($piKlarnaText,array($piKlarnaOrderNumber, $piKlarnaSearch, $piKlarnaSearch));
        $sql = "SELECT id
                FROM s_order
                WHERE ordernumber = ?";
        $piKlarnaOrderFixId = Shopware()->Db()->fetchOne($sql, array($piKlarnaOrderNumber));
        for ($i = 0; $i < sizeof($piKlarnaArticles); $i++) {
            $piKlarnaArticles[$i]['name'] = htmlentities($piKlarnaArticles[$i]['name'], ENT_COMPAT | ENT_HTML401,'UTF-8');
            $piKlarnaArticles[$i]['options_delete'] = '&nbsp;';
            $piKlarnaArticles[$i]['einzelpreis'] = number_format($piKlarnaArticles[$i]['einzelpreis'], 2, ',', '.');
            $sql = "SELECT DISTINCT description
                    FROM s_core_states
                    WHERE id =
                    (
                        SELECT DISTINCT bezahlstatus
                        FROM Pi_klarna_payment_order_detail
                        WHERE bestell_nr = ?
                        AND ordernumber = ?
                    )";
            $piKlarnaArticles[$i]['bezahlstatus'] = Shopware()->Db()->fetchOne($sql, array($piKlarnaArticles[$i]['bestell_nr'], $piKlarnaOrderNumber));
            $sql = "SELECT DISTINCT description
                    FROM s_core_states
                    WHERE id =
                    (
                        SELECT DISTINCT versandstatus
                        FROM Pi_klarna_payment_order_detail
                        WHERE bestell_nr = ?
                        AND ordernumber = ?
                    )";
            $piKlarnaArticles[$i]['bestellstatus'] = Shopware()->Db()->fetchOne($sql, array($piKlarnaArticles[$i]['bestell_nr'], $piKlarnaOrderNumber));
            $piKlarnaArticles[$i]['bestellstatus_kurz'] = $piKlarnaArticles[$i]['bestellstatus'];
            if($piKlarnaArticles[$i]['bestellstatus']=='Komplett ausgeliefert'){
                $piKlarnaArticles[$i]['bestellstatus']='<span style="color:green">Komplett ausgeliefert</span>';
            }
            elseif($piKlarnaArticles[$i]['bestellstatus']=='Teilweise ausgeliefert'){
                $piKlarnaArticles[$i]['bestellstatus']='<span style="color:orange">Teilweise ausgeliefert</span>';
            }
            $sql = "SELECT DISTINCT
                    b.lastname AS kunde
                    FROM s_order AS a
                    INNER JOIN s_order_billingaddress b ON a.userID = b.UserID
                    WHERE a.ordernumber = ?";
            $piKlarnaArticles[$i]['customer'] = Shopware()->Db()->fetchOne($sql, array($piKlarnaArticles[$i]['ordernumber']));
            $piKlarnaArticles[$i]['orderfixid'] = $piKlarnaOrderFixId;
            $sql = "SELECT instock FROM s_articles_details WHERE ordernumber = ?";
            $piKlarnaArticles[$i]['stock'] = Shopware()->Db()->fetchOne($sql, array($piKlarnaArticles[$i]['bestell_nr']));
            if ($piKlarnaArticles[$i]['name'] == 'Gutschein-intern'
                || $piKlarnaArticles[$i]['name'] == 'Zuschlag f&uuml;r Zahlungsart'
                || $piKlarnaArticles[$i]['name'] == 'Versandkosten'
            ) {
                $piKlarnaArticles[$i]['stock'] = 'unbegrenzt';
            }
            $sql = "SELECT ordernumber AS articlenumber FROM s_articles_details WHERE articleID = ?";
            $piKlarnaArticles[$i]['articlenumber'] = Shopware()->Db()->fetchOne($sql, array($piKlarnaArticles[$i]['articleid']));
            $piKlarnaArticles[$i]['orderid'] = $piKlarnaOrderDetailsId;
        }
        echo json_encode(array("total" => count($piKlarnaTotal), "items" => $piKlarnaArticles));
    }

    /**
     * Delete order from orderlist
     *
     * @see templates/backend/plugins/PigmbhKlarnaPayment/index.php
     *
     * @throws  KlarnaException
     *
     * @return void
     */
    public function deleteOrderAction()
    {
        $this->View()->setTemplate();
        $piKlarnaOrderNumber = $this->Request()->ordernumber;
        $piKlarnaOrderId = $this->Request()->deleteOne;
        $sql = "SELECT transactionID, invoice_amount, status FROM s_order WHERE ordernumber = ?";
        $piKlarnaOrder = Shopware()->Db()->fetchRow($sql, array($piKlarnaOrderNumber));
        $piKlarnaError = false;
        if ($piKlarnaOrder['invoice_amount'] != 0 && $piKlarnaOrder['status'] != 7) {
            $k = piKlarnaCreateKlarnaInstance($piKlarnaOrderNumber);
            try {
                $k->cancelReservation($piKlarnaOrder['transactionID']);
            }
            catch (Exception $e) {
                echo '<span class="bigred">' . $e->getMessage() . " (#" . $e->getCode() . ")</span>";
                $piKlarnaError = true;
                return $this->forward('index');
            }
        }
        if ($piKlarnaError == false) {
            $sql = "DELETE FROM s_order WHERE id = ?";
            Shopware()->Db()->query($sql, array($piKlarnaOrderId));
            $sql = "DELETE FROM Pi_klarna_payment_order_data WHERE order_number = ?";
            Shopware()->Db()->query($sql, array($piKlarnaOrderNumber));
            $sql = "DELETE FROM Pi_klarna_payment_order_detail WHERE ordernumber = ?";
            Shopware()->Db()->query($sql, array($piKlarnaOrderNumber));
            $sql = "DELETE FROM Pi_klarna_payment_user_data WHERE ordernumber = ?";
            Shopware()->Db()->query($sql, array($piKlarnaOrderNumber));
        }
        echo'<span class="bigred">Bestellung erfolgreich gel&ouml;scht</span>';
        return $this->forward('index');
    }

    /**
     * Add article to order. Delivers a JSON String with article details
     *
     * @see templates/backend/plugins/PigmbhKlarnaPayment/index.php
     * @throws KlarnaException
     */
    public function addArticleAction()
    {
        $this->View()->setTemplate();
        $piKlarnaOrderNumber = $this->Request()->myordernumber;
        $piKlarnaArticleOrderId = $this->Request()->myarticlenumber;
        $sql = "SELECT id,transactionID,invoice_amount,invoice_amount_net FROM s_order WHERE ordernumber = ?";
        $piKlarnaOrder = Shopware()->Db()->fetchRow($sql, array($piKlarnaOrderNumber));
        $sql = "SELECT articleID,id FROM s_articles_details WHERE ordernumber = binary ?";
        $piKlarnaArticleId = Shopware()->Db()->fetchRow($sql, array($piKlarnaArticleOrderId));
        $sql = "SELECT * FROM s_articles WHERE id = ?";
        $piKlarnaArticle = Shopware()->Db()->fetchRow($sql, array($piKlarnaArticleId['articleID']));
        $sql = "SELECT price FROM s_articles_prices WHERE articledetailsID = ?";
        $piKlarnaArticleprice = Shopware()->Db()->fetchOne($sql, array($piKlarnaArticleId['id']));
        $sql = "SELECT tax FROM s_core_tax WHERE id = ?";
        $piKlarnaTax = Shopware()->Db()->fetchOne($sql, array($piKlarnaArticle['taxID']));
        $sql = " SELECT bestell_nr FROM Pi_klarna_payment_order_detail WHERE ordernumber = ?";
        $piKlarnaArticleOrderNumbers = Shopware()->Db()->fetchAll($sql, array($piKlarnaOrderNumber));
//        $piKlarnaArticlename = str_replace("'", "\'", $piKlarnaArticle['name']);
        $piKlarnaArticlename = $piKlarnaArticle['name'];
        $piKlarnaPlusTax = $piKlarnaArticleprice / 100 * (100 + $piKlarnaTax);
        $piKlarnaReturn = piKlarnaChangeReservation($piKlarnaPlusTax, $piKlarnaOrder['transactionID'], 'part');
        $piKlarnaFlag = true;
        $piKlarnaArticleStatus = 0;
        if ($piKlarnaReturn['error'] == false) {
            for ($i = 0; $i < sizeof($piKlarnaArticleOrderNumbers); $i++) {
                if ($piKlarnaArticleOrderNumbers[$i]['bestell_nr'] == $piKlarnaArticleOrderId) {
                    $piKlarnaFlag = false;
                    $sql = "UPDATE Pi_klarna_payment_order_detail
                            SET
                                bestellt=bestellt+1,
                                offen=offen+1,
                                anzahl=anzahl+1
                            WHERE ordernumber = ? 
                            AND bestell_nr = ?";
                    Shopware()->Db()->query($sql, array($piKlarnaOrderNumber, $piKlarnaArticleOrderId));
                    $sql = "UPDATE s_order_details
                            SET quantity=quantity+1
                            WHERE ordernumber ='" . $piKlarnaOrderNumber . "'
                            AND articleordernumber ='" . $piKlarnaArticleOrderId . "'";
                    Shopware()->Db()->query($sql, array($piKlarnaOrderNumber, $piKlarnaArticleOrderId));
                    $sql = "SELECT versandstatus
                            FROM Pi_klarna_payment_order_detail
                            WHERE ordernumber ='" . $piKlarnaOrderNumber . "'
                            AND bestell_nr ='" . $piKlarnaArticleOrderId . "'";
                    $piKlarnaArticleStatus = Shopware()->Db()->fetchOne($sql, array($piKlarnaOrderNumber, $piKlarnaArticleOrderId));
                    if ($piKlarnaArticleStatus != 0) {
                        $piKlarnaPartReservedStatusId = piKlarnaGetPartReservedStatusId();
                        $sql = "UPDATE Pi_klarna_payment_order_detail
                                SET
                                    versandstatus = 6,
                                    bezahlstatus = ?
                                WHERE ordernumber = ?
                                AND bestell_nr = ?";
                        Shopware()->Db()->query($sql, array((int)$piKlarnaPartReservedStatusId, $piKlarnaOrderNumber, $piKlarnaArticleOrderId ));
                    }
                }
            }
            if ($piKlarnaFlag == true) {
                $sql = sprintf("
                    INSERT INTO s_order_details
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
                    (
                        ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?
                    )"
                );
                Shopware()->Db()->query($sql,array(
                    (int)$piKlarnaOrder['id'],
                    $piKlarnaOrderNumber,
                    (int)$piKlarnaArticle['taxID'],
                    (int)$piKlarnaArticleId['articleID'],
                    $piKlarnaArticleOrderId,
                    $piKlarnaPlusTax,
                    (int)1,
                    (double)$piKlarnaArticlename,
                    (int)0,
                    (int)0,
                    (int)0
                ));
                $sql = sprintf("
                    INSERT INTO `Pi_klarna_payment_order_detail`
                    (
                         `ordernumber`,
                         `artikel_id`,
                         `bestell_nr`,
                         `anzahl`,
                         `name`,
                         `einzelpreis`,
                         `gesamtpreis`,
                         `bestellt`,
                         `offen`,
                         `geliefert`,
                         `storniert`,
                         `retourniert`,
                         `bezahlstatus`,
                         `versandstatus`
                    )
                    VALUES
                    (
                        ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?
                    )
                ");
                Shopware()->Db()->query($sql,array(
                    $piKlarnaOrderNumber, 
                    $piKlarnaArticleId['articleID'],
                    $piKlarnaArticleOrderId,
                    (int)1,
                    $piKlarnaArticlename,
                    (double)$piKlarnaPlusTax,
                    (double)$piKlarnaPlusTax,
                    (int)1,
                    (int)1,
                    (int)0,
                    (int)0,
                    (int)0,
                    (int)18,
                    0
                ));
            }
            $piKlarnaHistoryEvent = "Artikel hinzugef&uuml;gt";
            $sql = "INSERT INTO Pi_klarna_payment_history
                    (
                        ordernumber, event, name, bestellnr, anzahl
                    )
                    VALUES
                    (
                        ?, ?, ?, ?, '1'
                    )";
            Shopware()->Db()->query($sql, array($piKlarnaOrderNumber, $piKlarnaHistoryEvent, $piKlarnaArticlename, $piKlarnaArticleOrderId));
            piKlarnaCalculateNewAmount($piKlarnaOrderNumber);
        }
        $piKlarnaArticle['name'] = htmlentities($piKlarnaArticle['name'], ENT_COMPAT | ENT_HTML401,'UTF-8');
        if (!$piKlarnaArticle['name']) $piKlarnaArticle['nofound'] = $piKlarnaArticleOrderId;
        echo json_encode(array(
            'articlename' => $piKlarnaArticle['name'],
            'k_return' => $piKlarnaReturn,
            'articleordernumbers' => $piKlarnaArticleStatus,
            'nofound' => $piKlarnaArticle['nofound']
        ));
    }

    /**
     * Delete articles from order. Delivers a JSON String with article details
     *
     * @see templates/backend/plugins/PigmbhKlarnaPayment/index.php
     * @throws KlarnaException
     */
    public function deleteArticleAction()
    {
        $this->View()->setTemplate();
        $piKlarnaOrderNumber = $this->Request()->myordernumber;
        $piKlarnaArticleOrderId = $this->Request()->myarticlenumber;
        $sql = "SELECT name,anzahl,einzelpreis FROM Pi_klarna_payment_order_detail WHERE bestell_nr  = ? AND ordernumber = ?";
        $piKlarnaArticle = Shopware()->Db()->fetchRow($sql, array($piKlarnaArticleOrderId, $piKlarnaOrderNumber));
        $sql = "SELECT transactionid FROM Pi_klarna_payment_order_data WHERE order_number = ?";
        $piKlarnaTransactionId = Shopware()->Db()->fetchOne($sql, array($piKlarnaOrderNumber));
        $piKlarnaArticlePrice = $piKlarnaArticle['anzahl'] * $piKlarnaArticle['einzelpreis'];
        $piKlarnaReturn = piKlarnaChangeReservation($piKlarnaArticlePrice, $piKlarnaTransactionId, 'deletearticle');
        if ($piKlarnaReturn['error'] == false) {
            $sql = "DELETE FROM Pi_klarna_payment_order_detail WHERE ordernumber = ? AND bestell_nr  = ?";
            Shopware()->Db()->query($sql, array($piKlarnaOrderNumber, $piKlarnaArticleOrderId));
            $sql = "DELETE FROM s_order_details WHERE ordernumber = ? AND articleordernumber  = ?";
            Shopware()->Db()->query($sql, array($piKlarnaOrderNumber, $piKlarnaArticleOrderId));
            if ($piKlarnaArticleOrderId == 'versand_' . $piKlarnaOrderNumber) {
                $sql = "UPDATE s_order
                        SET
                            invoice_shipping='0',
                            invoice_shipping_net='0',
                            dispatchID='0'
                        WHERE ordernumber = ?";
                Shopware()->Db()->query($sql, array($piKlarnaOrderNumber));
            }
//            $piKlarnaArticlename = str_replace("'", "\'", $piKlarnaArticle['name']);
            $piKlarnaArticlename = $piKlarnaArticle['name'];

            $piKlarnaHistoryEvent = "<span>Artikel entfernt</span>";
            $sql = "INSERT INTO Pi_klarna_payment_history
                    (
                        ordernumber, event, name, bestellnr, anzahl
                    )
                    VALUES
                    (
                        ?, ?, ?, ?, ?
                    )";
            Shopware()->Db()->query($sql, array(
                $piKlarnaOrderNumber, 
                $piKlarnaHistoryEvent, 
                $piKlarnaArticlename, 
                $piKlarnaArticleOrderId , 
                $piKlarnaArticle['anzahl'] 
            ));
            piKlarnaCalculateNewAmount($piKlarnaOrderNumber);
        }
        $piKlarnaArticlename = htmlentities($piKlarnaArticlename, ENT_COMPAT | ENT_HTML401,'UTF-8');
        echo json_encode(array('articlename' => $piKlarnaArticlename, 'k_return' => $piKlarnaReturn));
    }

    /**
     * Updates article quantity. Delivers a JSON String with article details
     *
     * @see templates/backend/plugins/PigmbhKlarnaPayment/index.php
     * @throws KlarnaException
     */
    public function updateArticleQuantityAction()
    {
        $this->View()->setTemplate();
        $piKlarnaArticleNr = explode(";", $this->Request()->articleid);
        $piKlarnaQuantity = explode(";", $this->Request()->quantity);
        $piKlarnaOrderNumber = $this->Request()->myordernumber;
        $piKlarnaNewRestAmount = 0;
        $sql = "SELECT transactionid FROM Pi_klarna_payment_order_data WHERE order_number = ?";
        $piKlarnaTransactionId = Shopware()->Db()->fetchOne($sql, array($piKlarnaOrderNumber));
        $piKlarnaQuantityNew = array();
        for ($i = 0; $i < sizeof($piKlarnaArticleNr); $i++) {
            $sql = "SELECT anzahl,storniert,retourniert,einzelpreis
                    FROM Pi_klarna_payment_order_detail
                    WHERE ordernumber = ?
                    AND bestell_nr = ?";
            $piKlarnaArticle = Shopware()->Db()->fetchRow($sql, array($piKlarnaOrderNumber, $piKlarnaArticleNr[$i]));
            $piKlarnaQuantityNew[$i] = $piKlarnaQuantity[$i] - $piKlarnaArticle['retourniert'];
            $piKlarnaQuantityNew[$i]-=$piKlarnaQuantity[$i] - $piKlarnaArticle['storniert'];
            if ($piKlarnaQuantity[$i] != $piKlarnaArticle['anzahl']) {
                if ($piKlarnaQuantity[$i] > $piKlarnaArticle['anzahl']) {
                    $piKlarnaNewRestAmount+=($piKlarnaQuantity[$i] - $piKlarnaArticle['anzahl']) * $piKlarnaArticle['einzelpreis'];
                }
                else {
                    $piKlarnaNewRestAmount-=($piKlarnaArticle['anzahl'] - $piKlarnaQuantity[$i]) * $piKlarnaArticle['einzelpreis'];
                }
            }
        }
        $piKlarnaReturn = piKlarnaChangeReservation($piKlarnaNewRestAmount, $piKlarnaTransactionId, 'part');
        if ($piKlarnaReturn['error'] == false) {
            for ($i = 0; $i < sizeof($piKlarnaArticleNr); $i++) {
                $sql = "SELECT anzahl,storniert,retourniert,einzelpreis,geliefert,name
                        FROM Pi_klarna_payment_order_detail
                        WHERE ordernumber = ?
                        AND bestell_nr = ?";
                $piKlarnaArticle = Shopware()->Db()->fetchRow($sql, array($piKlarnaOrderNumber, $piKlarnaArticleNr[$i]));
                $piKlarnaQuantityNew[$i] = $piKlarnaQuantity[$i] - $piKlarnaArticle['retourniert'];
                $piKlarnaQuantityNew[$i]-=$piKlarnaQuantity[$i] - $piKlarnaArticle['storniert'];
                $piKlarnaNewAmount = $piKlarnaArticle['einzelpreis'] * $piKlarnaQuantityNew[$i];
                if ($piKlarnaQuantity[$i] != $piKlarnaArticle['anzahl']) {
                    if ($piKlarnaQuantity[$i] > $piKlarnaArticle['anzahl']) {
                        $piKlarnaNewRestAmount+=($piKlarnaQuantity[$i] - $piKlarnaArticle['anzahl']) * $piKlarnaArticle['einzelpreis'];
                    }
                    else {
                        $piKlarnaNewRestAmount-=($piKlarnaArticle['anzahl'] - $piKlarnaQuantity[$i]) * $piKlarnaArticle['einzelpreis'];
                    }
                    $piKlarnaNewOpen = (($piKlarnaQuantity[$i] - $piKlarnaArticle['geliefert'])
                                        - $piKlarnaArticle['retourniert']) - $piKlarnaArticle['storniert'];
                    $sql = "UPDATE Pi_klarna_payment_order_detail
                            SET
                                anzahl = ?,
                                gesamtpreis = ?,
                                bestellt = ?,
                                offen = ?
                            WHERE ordernumber = ?
                            AND bestell_nr = ?
                            AND bestell_nr <> 'sw-payment'
                            AND bestell_nr <> 'sw-payment-absolute'
                            AND name <> 'Gutschein-intern'
                            AND name <> 'Gutschein'
                            AND name <> 'Versandkosten'";
                    Shopware()->Db()->query($sql, array(
                        (int)$piKlarnaQuantity[$i], 
                        (double)$piKlarnaNewAmount, 
                        (int)$piKlarnaQuantity[$i], 
                        (int)$piKlarnaNewOpen, 
                        $piKlarnaOrderNumber, 
                        $piKlarnaArticleNr[$i]
                    ));
                    $sql = "UPDATE s_order_details SET quantity = ? WHERE ordernumber = ? AND articleordernumber = ?";
                    Shopware()->Db()->query($sql, array((int)$piKlarnaNewOpen, $piKlarnaOrderNumber, $piKlarnaArticleNr[$i]));
                    //$piKlarnaName = str_replace("'", "\'", $piKlarnaArticle['name']);
                    $piKlarnaName = $piKlarnaArticle['name'];

                    $piKlarnaHistoryEvent = "<span>Artikelanzahl ge&auml;ndert</span>";
                    $sql = "INSERT INTO Pi_klarna_payment_history
                        (
                            ordernumber, event, name, bestellnr, anzahl
                        )
                        VALUES
                        (
                            ?, ?, ?, ?, 'alt:'? '/ neu:'?
                        )";
                    Shopware()->Db()->query($sql, array(
                        $piKlarnaOrderNumber, 
                        $piKlarnaHistoryEvent,
                        $piKlarnaName, 
                        $piKlarnaArticleNr[$i], 
                        $piKlarnaArticle['anzahl'], 
                        $piKlarnaQuantity[$i]
                    ));
                }
            }
            piKlarnaCalculateNewAmount($piKlarnaOrderNumber);
        }
        $sql = "SELECT invoice_amount FROM s_order WHERE ordernumber = ? ORDER BY 'date'";
        $piKlarnaNewInvoiceAmount = Shopware()->Db()->fetchOne($sql, array($piKlarnaOrderNumber));
        $piKlarnaNewInvoiceAmount = number_format($piKlarnaNewInvoiceAmount, 2, ',', '.');
        echo json_encode(array('articlename' => $piKlarnaNewInvoiceAmount, 'k_return' => $piKlarnaReturn));
    }

    /**
     * Gets History. Delivers a JSON String with history details
     *
     * @see templates/backend/plugins/PigmbhKlarnaPayment/index.php
     */
    public function getHistoryAction()
    {
        $this->View()->setTemplate();
        $piKlarnaOrderNumber = $this->Request()->myordernumber;
        $sql = "SELECT * FROM Pi_klarna_payment_history WHERE ordernumber = ?";
        $piKlarnaHistory = Shopware()->Db()->fetchAll($sql, array($piKlarnaOrderNumber));
        for ($i = 0; $i < sizeof($piKlarnaHistory); $i++) {
            $piKlarnaHistory[$i]['id'] = $i + 1;
            $piKlarnaHistory[$i]['name'] = htmlentities($piKlarnaHistory[$i]['name'], ENT_COMPAT | ENT_HTML401,'UTF-8');
        }
        echo json_encode(array("total" => count($piKlarnaHistory), "items" => $piKlarnaHistory));
    }

    /**
     * Adds Voucher to order. Delivers a JSON String with vocuher details
     *
     * @see templates/backend/plugins/PigmbhKlarnaPayment/index.php
     * @throws KlarnaException
     */
    public function addVoucherAction()
    {
        $this->View()->setTemplate();
        $piKlarnaOrderNumber = $this->Request()->myordernumber;
        $piKlarnaPrice = $this->Request()->price;
        $piKlarnaArticlenumber = $this->Request()->articlenumber;
        $piKlarnaRandomNumber = $piKlarnaRandomNumber . rand(1, 100000) - 100000;
        $sql = "SELECT id FROM s_order WHERE ordernumber = ?";
        $piKlarnaOrderid = Shopware()->Db()->fetchOne($sql, array($piKlarnaOrderNumber));
        $piKlarnaTransactionId = piKlarnaGetTransactionIdByOrdernumber($piKlarnaOrderNumber);
        $piKlarnaPrice = str_replace(",", ".", $piKlarnaPrice);
        $piKlarnaReturn = piKlarnaChangeReservation($piKlarnaPrice, $piKlarnaTransactionId, 'part');
        if ($piKlarnaReturn['error'] == false) {
            $sql = sprintf("
                INSERT INTO `s_order_details`
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
                (
                    ?,
                    ?,
                    1,
                    172,
                    ?,
                    ?,
                    1,
                    'Gutschein-intern',
                    0,
                    0,
                    0
                )
            ");
            Shopware()->Db()->query($sql,array((int)$piKlarnaOrderid, $piKlarnaOrderNumber, $piKlarnaArticlenumber, (double)$piKlarnaPrice));
            $sql = "
                INSERT INTO `Pi_klarna_payment_order_detail`
                (
                     `ordernumber`,
                     `artikel_id`,
                     `bestell_nr`,
                     `anzahl`,
                     `name`,
                     `einzelpreis`,
                     `gesamtpreis`,
                     `bestellt`,
                     `offen`,
                     `geliefert`,
                     `storniert`,
                     `retourniert`,
                     `bezahlstatus`,
                     `versandstatus`
                )
                VALUES
                (
                    ?,
                    172,
                    ?,
                    1,
                    'Gutschein-intern',
                    ?,
                    ?,
                    1,
                    1,
                    0,
                    0,
                    0,
                    18,
                    0
                )
            ";
            
            Shopware()->Db()->query($sql, array($piKlarnaOrderNumber, $piKlarnaArticlenumber, (double)$piKlarnaPrice, (double)$piKlarnaPrice));
            
            $piKlarnaHistoryEvent = "<span>Gutschein hinzugef&uuml;gt</span>";
            $sql = "
                INSERT INTO Pi_klarna_payment_history
                (
                    ordernumber, event, name, bestellnr, anzahl
                )
                VALUES
                (
                    ?, ?, 'Gutschein-intern', ?, '1'
                )
            ";
            Shopware()->Db()->query($sql, array($piKlarnaOrderNumber, $piKlarnaHistoryEvent, $piKlarnaArticlenumber));
            piKlarnaCalculateNewAmount($piKlarnaOrderNumber);
        }
        echo json_encode(array('articlename' => 'Gutschein-intern', 'k_return' => $piKlarnaReturn));
    }

    /**
     * Gets articles for the send and cancel window. Delivers a JSON String with article details
     *
     * @see templates/backend/plugins/PigmbhKlarnaPayment/index.php
     */
    public function getSendAndCancelArticlesAction()
    {
        $this->View()->setTemplate();
        $piKlarnaOrderNumber = $this->Request()->myordernumber;
        $sql = "SELECT cleared FROM s_order WHERE ordernumber = ?";
        $piKlarnaCheckPayment = Shopware()->Db()->fetchOne($sql, array($piKlarnaOrderNumber));
        $piKlarnaAcceptedStatus=piKlarnaGetAcceptedStatusId();
        $piKlarnArticles = array();
        if ($piKlarnaCheckPayment == $piKlarnaAcceptedStatus) {
            if (isset($this->Request()->start)) $piKlarnaStart = $this->Request()->start;
            else $piKlarnaStart = 0;
            if (isset($this->Request()->limit)) $piKlarnaLimit = $this->Request()->limit;
            else $piKlarnaLimit = 1000;
            if (isset($this->Request()->search)) {
                $piKlarnaSearch = $this->Request()->search;
                $sql = "SELECT DISTINCT *
                        FROM Pi_klarna_payment_order_detail
                        WHERE ordernumber = ?
                        AND offen > 0
                        AND
                        (
                            name LIKE '%" . $piKlarnaSearch . "%'
                            OR bestell_nr LIKE '%" . $piKlarnaSearch . "%'
                        )
                        LIMIT " . $piKlarnaStart . "," . $piKlarnaLimit . "";
                $piKlarnArticles = Shopware()->Db()->fetchAll($sql, array($piKlarnaOrderNumber,));
            }
            else {
                $sql = "SELECT *
                        FROM Pi_klarna_payment_order_detail
                        WHERE ordernumber = ?
                        AND offen > 0
                        LIMIT " . $piKlarnaStart . "," . $piKlarnaLimit . "";
                $piKlarnArticles = Shopware()->Db()->fetchAll($sql, array($piKlarnaOrderNumber));
            }
            for ($i = 0; $i < sizeof($piKlarnArticles); $i++) {
                $piKlarnArticles[$i]['gesamtpreis'] = number_format($piKlarnArticles[$i]['einzelpreis'] * $piKlarnArticles[$i]['offen'], 2, ',', '.');
                $piKlarnArticles[$i]['einzelpreis'] = number_format($piKlarnArticles[$i]['einzelpreis'], 2, ',', '.');
                $piKlarnArticles[$i]['name'] = htmlentities($piKlarnArticles[$i]['name'], ENT_COMPAT | ENT_HTML401,'UTF-8');
                $piKlarnArticles[$i]['anzahl'] = $piKlarnArticles[$i]['offen'];
            }
        }
        else {
            $piKlarnArticles = '';
        }
        echo json_encode(array("total" => Count($piKlarnArticles), "items" => $piKlarnArticles,));
    }
    
    
    function testAction() {
        $haystack = 'Zusdchlag für';
        if (preg_match('/Zuschlag.f/',$haystack)) {
            die('fnuu: true');
        } else {
            die('fnuu: false');
        }
    }

    /**
     * Sends articles and creates invoice. Delivers a JSON String with article details
     *
     * @see templates/backend/plugins/PigmbhKlarnaPayment/index.php
     * @throws KlarnaException
     */
    public function sendArticlesAction()
    {
        $piKlarnaFlag = true;
        $piKlarnaArticleCounter = 0;
        //send a part of the order
        $this->View()->setTemplate();
        $piKlarnaArticleNr = explode(";", $this->Request()->articlenr);
        $piKlarnaQuantity = explode(";", $this->Request()->anzahl);
        $piKlarnaOrderNumber = $this->Request()->myordernumber;
        $piKlarnaNewOpen = array();
        $piKlarnaNewDelivered = array();
        $piKlarnaArticle = array();
        $piKlarnaReturn = array();
        $myklarnaarticles = array();
        $piKlarnaCheckVoucher = false;
        $k = piKlarnaCreateKlarnaInstance($piKlarnaOrderNumber);
        $sql = "SELECT Count(*)
                FROM Pi_klarna_payment_order_detail
                WHERE ordernumber = ?
                AND offen > 0";
        $piKlarnaQuantityartikel = Shopware()->Db()->fetchOne($sql, array($piKlarnaOrderNumber));
        $sql = "SELECT Count(*)
                FROM Pi_klarna_payment_order_detail
                WHERE ordernumber = ?";
        $piKlarnaQuantityartikelall = Shopware()->Db()->fetchOne($sql, array($piKlarnaOrderNumber));
        for ($i = 0; $i < sizeof($piKlarnaQuantity); $i++) {
            $sql = "SELECT name,bestellt,bestell_nr,geliefert,offen,einzelpreis
                    FROM Pi_klarna_payment_order_detail
                    WHERE ordernumber = ?
                    AND bestell_nr = ?";
            $piKlarnaArticle[$i] = Shopware()->Db()->fetchRow($sql, array($piKlarnaOrderNumber, $piKlarnaArticleNr[$i]));
            $piKlarnaNewOpen[$i] = $piKlarnaArticle[$i]['offen'] - $piKlarnaQuantity[$i];
            $piKlarnaNewDelivered[$i] = $piKlarnaArticle[$i]['geliefert'] + $piKlarnaQuantity[$i];
            if ($piKlarnaNewOpen[$i] != 0 || sizeof($piKlarnaQuantity) != $piKlarnaQuantityartikelall) $piKlarnaFlag = false;
            if ($piKlarnaArticle[$i]['name'] == 'Versandkosten')
                $piKlarnaFlags = KlarnaFlags::INC_VAT + KlarnaFlags::IS_SHIPMENT;
//            elseif (htmlentities($piKlarnaArticle[$i]['name'], ENT_COMPAT | ENT_HTML401,'UTF-8') == 'Zuschlag f&uuml;r Zahlungsart')
            elseif (preg_match('/Zuschlag.f/', $piKlarnaArticle[$i]['name']))
                $piKlarnaFlags = KlarnaFlags::INC_VAT + KlarnaFlags::IS_HANDLING;
            else $piKlarnaFlags = KlarnaFlags::INC_VAT;
            $sql = "SELECT ordercode FROM s_emarketing_vouchers";
            $piKlarnaVoucherOrderCode=Shopware()->Db()->fetchAll($sql);
            for ($j = 0; $j < count($piKlarnaVoucherOrderCode); $j++) {
                if( $piKlarnaVoucherOrderCode[$j]['ordercode']==$piKlarnaArticleNr[$i]){
                    $piKlarnaCheckVoucher = true;
                }
            }
            $piKlarnaTaxid = 0;
            if($piKlarnaCheckVoucher){
                $sql = "SELECT taxconfig FROM s_emarketing_vouchers WHERE ordercode = ?";
                $piKlarnaTaxid=Shopware()->Db()->fetchOne($sql, array($piKlarnaArticleNr[$i]));
                if(!$piKlarnaTaxid){
                    $piKlarnaTaxid=0;
                }
            }
            else{
                $sql = "SELECT taxID FROM s_order_details WHERE ordernumber = ? AND articleordernumber = ?";
                $piKlarnaTaxid = Shopware()->Db()->fetchOne($sql, array($piKlarnaOrderNumber, $piKlarnaArticleNr[$i]));
            }
            if ($piKlarnaArticle[$i]['name'] == 'Versandkosten') {
                $sql = "SELECT dispatchID FROM s_order WHERE ordernumber = ?";
                $piKlarnaDispatchId = Shopware()->Db()->fetchOne($sql, array($piKlarnaOrderNumber));
                $sql = "SELECT tax_calculation FROM s_premium_dispatch WHERE id = ?";
                $piKlarnaTaxid = Shopware()->Db()->fetchOne($sql, array($piKlarnaDispatchId));
                if ($piKlarnaTaxid == 0) {
                    $sql = "SELECT id FROM s_core_tax WHERE tax = (SELECT MAX(tax) FROM s_core_tax)";
                    $piKlarnaTaxid = Shopware()->Db()->fetchOne($sql);
                }
            }
            $sql = "SELECT tax FROM s_core_tax WHERE id = ?";
            $piKlarnaTax = Shopware()->Db()->fetchOne($sql, array($piKlarnaTaxid));
            
            if (!$piKlarnaTax) $piKlarnaTax = 0;
            if ($piKlarnaQuantity[$i] > 0) {           
                $myklarnaarticles[$piKlarnaArticleCounter]['bestell_nr'] = $piKlarnaArticleNr[$i];
                $myklarnaarticles[$piKlarnaArticleCounter]['anzahl'] = $piKlarnaQuantity[$i];
                $myklarnaarticles[$piKlarnaArticleCounter]['einzelpreis'] = $piKlarnaArticle[$i]['einzelpreis'];
                $myklarnaarticles[$piKlarnaArticleCounter]['name'] = $piKlarnaArticle[$i]['name'];
                $piKlarnaArticleCounter++;
                $piKlarnaArticle[$i]['name'] = strip_tags($piKlarnaArticle[$i]['name']);
                $qty = "";
                $artNo = "";
                $title = "";
                $price = "";
                $vat = "";
                $discount = "";
                $flags = "";
                $k->addArticle(
                    $qty = $piKlarnaQuantity[$i],
                    $artNo = $piKlarnaArticleNr[$i],
                    $title = utf8_decode(htmlspecialchars_decode($piKlarnaArticle[$i]['name'])),
                    $price = $piKlarnaArticle[$i]['einzelpreis'],
                    $vat = str_replace(",", ".", $piKlarnaTax),
                    $discount = 0,
                    $flags = $piKlarnaFlags
                );
            }
        }
        if ($piKlarnaFlag == true) {
            $piKlarnaReturn = piKlarnaActivateReservation($k, $piKlarnaOrderNumber, 'complete', $myklarnaarticles);
        }
        elseif ($piKlarnaQuantityartikel == $piKlarnaArticleCounter) {
            $piKlarnaReturn = piKlarnaActivateReservation($k, $piKlarnaOrderNumber, 'last', $myklarnaarticles);
        }
        else {
            $piKlarnaReturn = piKlarnaActivateReservation($k, $piKlarnaOrderNumber, 'part', $myklarnaarticles);
        }
        if ($piKlarnaReturn['error'] == false) {
            for ($i = 0; $i < sizeof($piKlarnaQuantity); $i++) {
                $sql = "UPDATE Pi_klarna_payment_order_detail
                        SET
                            offen = ?,
                            geliefert = ?
                        WHERE ordernumber = ?
                        AND bestell_nr = ?";
                Shopware()->Db()->query($sql, array((int)$piKlarnaNewOpen[$i],(int)$piKlarnaNewDelivered[$i], $piKlarnaOrderNumber, $piKlarnaArticleNr[$i]));
                if ($piKlarnaQuantity[$i] > 0) {
                    //$piKlarnaArticlename = str_replace("'", "\'", $piKlarnaArticle[$i]['name']);
		    $piKlarnaArticlename = $piKlarnaArticle[$i]['name'];

                    $piKlarnaHistoryEvent = "<span>Artikel versendet</span>";
                    $sql = "INSERT INTO Pi_klarna_payment_history
                            (
                                ordernumber, event, name, bestellnr, anzahl
                            )
                            VALUES
                            (
                                ?, ?, ?, ?, ?
                            )";
                    Shopware()->Db()->query($sql, array($piKlarnaOrderNumber, $piKlarnaHistoryEvent, $piKlarnaArticlename, $piKlarnaArticleNr[$i], $piKlarnaQuantity[$i]));
                    if ($piKlarnaNewOpen[$i] == 0) {
                        $piKlarnaCompleteReservedStatusId=piKlarnaGetCompleteReservedStatusId();
                        $sql = "UPDATE Pi_klarna_payment_order_detail
                            SET
                                bezahlstatus = ?,
                                versandstatus = 7
                            WHERE ordernumber = ?
                            AND bestell_nr = ?";
                        Shopware()->Db()->query($sql, array((int)$piKlarnaCompleteReservedStatusId, $piKlarnaOrderNumber, $piKlarnaArticleNr[$i]));
                    }
                    elseif ($piKlarnaNewOpen[$i] > 0) {
                        $piKlarnaPartReservedStatusId=piKlarnaGetPartReservedStatusId();
                        $sql = "UPDATE Pi_klarna_payment_order_detail
                                SET
                                    bezahlstatus = ?,
                                    versandstatus = 6
                                WHERE ordernumber = ?
                                AND bestell_nr = ?";
                        Shopware()->Db()->query($sql, array((int)$piKlarnaPartReservedStatusId, $piKlarnaOrderNumber, $piKlarnaArticleNr[$i]));
                    }
                }
            }
            if ($piKlarnaFlag == true) {
                $piKlarnaGetAcceptedStatusId=piKlarnaGetAcceptedStatusId();
                $sql = "UPDATE s_order SET status = 7, cleared = ? WHERE ordernumber = ?";
                Shopware()->Db()->query($sql, array($piKlarnaGetAcceptedStatusId, $piKlarnaOrderNumber));
                $piKlarnaCompleteReservedStatusId=piKlarnaGetCompleteReservedStatusId();
                $sql = "UPDATE Pi_klarna_payment_order_detail
                        SET
                            versandstatus = 7,
                            bezahlstatus = ?
                        WHERE ordernumber = ?";
                Shopware()->Db()->query($sql, array((int)$piKlarnaCompleteReservedStatusId, $piKlarnaOrderNumber));
                $piKlarnaHistoryEvent = "<b class=\"green\">Bestellung vollst&auml;ndig versendet</b>";
                $sql = $sql = "INSERT INTO Pi_klarna_payment_history (ordernumber, event) VALUES (?, ?)";
                Shopware()->Db()->query($sql, array($piKlarnaOrderNumber, $piKlarnaHistoryEvent ));
            }
            else {
                $piKlarnaOpenFlag = true;
                $sql = "SELECT offen
                        FROM Pi_klarna_payment_order_detail
                        WHERE ordernumber = ?";
                $piKlarnaTotalOpen = Shopware()->Db()->fetchAll($sql, array($piKlarnaOrderNumber));
                for ($i = 0; $i < sizeof($piKlarnaTotalOpen); $i++) {
                    if ($piKlarnaTotalOpen[$i]['offen'] != 0) {
                        $piKlarnaOpenFlag = false;
                    }
                }
                if ($piKlarnaOpenFlag == true) {
                    $sql = "UPDATE s_order SET status = 7 WHERE ordernumber = ?";
                    Shopware()->Db()->query($sql, array($piKlarnaOrderNumber));
                }
                else {
                    $sql = "UPDATE s_order SET status = 6 WHERE ordernumber = ?";
                    Shopware()->Db()->query($sql, array($piKlarnaOrderNumber));
                }
            }
        }
        ob_clean();
        $retValue = json_encode(array(
            'articlename' => $piKlarnaArticle,
            'k_return' => $piKlarnaReturn,
            '$piKlarnaArticle' => $piKlarnaNewOpen,
            'myflag' => $piKlarnaFlag
        ));
        echo  $retValue;
    }

    /**
     * Cancels articles from order. Delivers a JSON String with article details
     *
     * @see templates/backend/plugins/PigmbhKlarnaPayment/index.php
     * @throws KlarnaException
     */
    public function cancelArticlesAction()
    {
        $this->View()->setTemplate();
        $piKlarnaArticleNr = explode(";", $this->Request()->articlenr);
        $piKlarnaQuantity = explode(";", $this->Request()->anzahl);
        $piKlarnaOrderNumber = $this->Request()->myordernumber;
        $piKlarnaArticleCounter = 0;
        $piKlarnaFlag = true;
        $piKlarnaArticle = array();
        $piKlarnaNewOpen = array();
        $piKlarnaNewCanceled = array();
        $piKlarnaDelivered = array();
        $piKlarnaTotalAmount = array();
        $piKlarnaError = array();
        $piKlarnaReturn = array();
        $k = piKlarnaCreateKlarnaInstance($piKlarnaOrderNumber);
        $sql = "SELECT transactionid FROM Pi_klarna_payment_order_data WHERE order_number = ?";
        $rno = Shopware()->Db()->fetchOne($sql, array($piKlarnaOrderNumber));
        for ($i = 0; $i < sizeof($piKlarnaArticleNr); $i++) {
            $sql = "SELECT * FROM Pi_klarna_payment_order_detail WHERE ordernumber = ? AND bestell_nr = ?";
            $piKlarnaArticle[$i] = Shopware()->Db()->fetchRow($sql, array($piKlarnaOrderNumber, $piKlarnaArticleNr[$i]));
            $piKlarnaNewOpen[$i] = $piKlarnaArticle[$i]['offen'] - $piKlarnaQuantity[$i];
            $piKlarnaNewCanceled[$i] = $piKlarnaArticle[$i]['storniert'] + $piKlarnaQuantity[$i];
            $piKlarnaDelivered[$i] = $piKlarnaArticle[$i]['bestellt'] - $piKlarnaNewCanceled[$i];
            $piKlarnaTotalAmount[$i] = $piKlarnaNewOpen[$i] * $piKlarnaArticle[$i]['einzelpreis'];
            $piKlarnaTotalAmount[$i]+=$piKlarnaArticle[$i]['geliefert'] * $piKlarnaArticle[$i]['einzelpreis'];
            if ($piKlarnaArticle[$i]['bestellt'] - $piKlarnaQuantity[$i] != 0) {
                $piKlarnaFlag = false;
            }
            if ($piKlarnaQuantity[$i] > 0) {
                $piKlarnaArticleCounter++;
            }
        }
        $sql = "SELECT Count(*) FROM Pi_klarna_payment_order_detail WHERE ordernumber = ?";
        $piKlarnaQuantityartikel = Shopware()->Db()->fetchOne($sql, array($piKlarnaOrderNumber));
        if ($piKlarnaQuantityartikel != $piKlarnaArticleCounter) {
            $piKlarnaFlag = false;
        }
        if ($piKlarnaFlag == true) {
            $piKlarnaError['error'] = false;
            $piKlarnaError['errormessage'] = "Komplett";
            try {
                $k->cancelReservation($rno);
                for ($i = 0; $i < sizeof($piKlarnaArticleNr); $i++) {
                    $piKlarnaCompleteCancelStatusId=piKlarnaGetCompleteCancelStatusId();
                    $piKlarnaReserverationCanceledStatusId=piKlarnaGetReserverationCanceledStatusId();
                    $sql = "UPDATE Pi_klarna_payment_order_detail
                        SET
                            offen = ?,
                            storniert = ?,
                            gesamtpreis = ?,
                            bezahlstatus = ?,
                            versandstatus = ?
                        WHERE ordernumber = ?
                        AND bestell_nr = ?";
                    Shopware()->Db()->query($sql, array(
                        (int)$piKlarnaNewOpen[$i],
                        (int)$piKlarnaNewCanceled[$i],
                        (double)$piKlarnaTotalAmount[$i],
                        (int)$piKlarnaReserverationCanceledStatusId,
                        (int)$piKlarnaCompleteCancelStatusId,
                        $piKlarnaOrderNumber,
                        $piKlarnaArticleNr[$i]  
                    ));
                    $sql = "SELECT (bestellt - storniert)
                            FROM Pi_klarna_payment_order_detail
                            WHERE ordernumber = ?
                            AND bestell_nr = ?";
                    $piKlarnaOrderquantity = Shopware()->Db()->fetchOne($sql, array($piKlarnaOrderNumber, $piKlarnaArticleNr[$i]));
                    $sql = "UPDATE s_order_details
                            SET quantity = ?
                            WHERE ordernumber = ?
                            AND articleordernumber = ?";
                    Shopware()->Db()->query($sql, array((int)$piKlarnaOrderquantity, $piKlarnaOrderNumber, $piKlarnaArticleNr[$i]));
                }
                $piKlarnaHistoryEvent = "<b class=\"red\">Bestellung vollst&auml;ndig storniert</b>";
                $sql = $sql = "INSERT INTO Pi_klarna_payment_history (ordernumber, event) VALUES (?, ?)";
                Shopware()->Db()->query($sql, array($piKlarnaOrderNumber, $piKlarnaHistoryEvent));
                $piKlarnaCompleteCancelStatusId=piKlarnaGetCompleteCancelStatusId();
                $piKlarnaReserverationCanceledStatusId=piKlarnaGetReserverationCanceledStatusId();
                $sql = "UPDATE s_order
                    SET
                        status = ?,
                        cleared = ?,
                        invoice_shipping = 0,
                        invoice_shipping_net = 0,
                        dispatchID = 0
                    WHERE ordernumber = ?";
                Shopware()->Db()->query($sql, array(
                        (int)$piKlarnaCompleteCancelStatusId, 
                        (int)$piKlarnaReserverationCanceledStatusId, 
                        $piKlarnaOrderNumber
                ));
                piKlarnaCalculateNewAmount($piKlarnaOrderNumber);
                echo json_encode(array('articlename' => $piKlarnaFlag, 'k_return' => $piKlarnaError, 'komplett' => true));
            }
            catch (Exception $e) {
                $piKlarnaError['error'] = true;
                $piKlarnaError['errormessage'] = $e->getMessage() . " (#" . $e->getCode() . ")";
                echo json_encode(array('articlename' => $piKlarnaFlag, 'k_return' => $piKlarnaError, 'komplett' => true));
            }
        }
        else {
            $piKlarnaNewRestAmount = 0;
            $piKlarnaDeliveredPrice = 0;
            $oldQuantity = 0;
            $sql = "SELECT geliefert,einzelpreis FROM Pi_klarna_payment_order_detail WHERE ordernumber = ?";
            $piKlarnaDelivered = Shopware()->Db()->fetchAll($sql, array($piKlarnaOrderNumber));
            for ($i = 0; $i < sizeof($piKlarnaDelivered); $i++) {
                $piKlarnaDeliveredPrice+=$piKlarnaDelivered[$i]['geliefert'] * $piKlarnaDelivered[$i]['einzelpreis'];
            }
            for ($i = 0; $i < sizeof($piKlarnaArticleNr); $i++) {
                $sql = "SELECT anzahl FROM Pi_klarna_payment_order_detail WHERE ordernumber = ? AND bestell_nr = ?";
                $oldQuantity = Shopware()->Db()->fetchOne($sql, array($piKlarnaOrderNumber, $piKlarnaArticleNr[$i]));
                $sql = "SELECT einzelpreis FROM Pi_klarna_payment_order_detail WHERE ordernumber = ? AND bestell_nr= ?";
                $price = Shopware()->Db()->fetchOne($sql, array($piKlarnaOrderNumber, $piKlarnaArticleNr[$i]));   
                $piKlarnaNewAmount = $price * $piKlarnaQuantity[$i];
                $piKlarnaNewRestAmount+=$piKlarnaNewAmount;
            }
            $sql = "SELECT invoice_amount FROM s_order WHERE ordernumber = ?";
            $piKlarnaInvoiceAmount = Shopware()->Db()->fetchOne($sql, array($piKlarnaOrderNumber));
            if ($piKlarnaNewRestAmount == $piKlarnaInvoiceAmount - $piKlarnaDeliveredPrice) {
                try {
                    $k->cancelReservation($rno);
                    for ($i = 0; $i < sizeof($piKlarnaArticleNr); $i++) {
                        if ($piKlarnaArticle[$i]['name'] == 'Versandkosten') {
                            $sql = "UPDATE s_order
                                SET
                                    invoice_shipping = 0,
                                    invoice_shipping_net = 0,
                                    dispatchID = 0
                                WHERE ordernumber = ?";
                            Shopware()->Db()->query($sql, array($piKlarnaOrderNumber));
                        }
                        $sql = "
                            SELECT geliefert
                            FROM Pi_klarna_payment_order_detail
                            WHERE ordernumber = ?
                            AND bestell_nr = ?";
                        $piKlarnaDelivered = Shopware()->Db()->fetchOne($sql, array($piKlarnaOrderNumber, $piKlarnaArticleNr[$i]));
//                        $piKlarnaArticlename = str_replace("'", "\'", $piKlarnaArticle[$i]['name']);
			$piKlarnaArticlename = $piKlarnaArticle[$i]['name'];

                        $piKlarnaHistoryEvent = "<span class=\"red\">Artikel storniert</span>";
                        
                        $sql = "INSERT INTO Pi_klarna_payment_history
                                (
                                    ordernumber, event, name, bestellnr, anzahl
                                )
                                VALUES
                                (
                                    ?, ?, ?, ?, ?
                                )";
                        Shopware()->Db()->query($sql, array(
                            $piKlarnaOrderNumber, 
                            $piKlarnaHistoryEvent, 
                            $piKlarnaArticlename, 
                            $piKlarnaArticleNr[$i],
                            $piKlarnaQuantity[$i] 
                        ));
                        if ($piKlarnaDelivered > 0) {
                            $piKlarnaCompleteReservedStatusId=piKlarnaGetCompleteReservedStatusId();
                            $sql = "UPDATE Pi_klarna_payment_order_detail
                                    SET
                                        offen = ?,
                                        storniert = ?,
                                        gesamtpreis = ?,
                                        bezahlstatus = ?,
                                        versandstatus = 7
                                    WHERE ordernumber = ?
                                    AND bestell_nr = ?";
                            Shopware()->Db()->query($sql, array(
                                (int)$piKlarnaNewOpen[$i],
                                (int)$piKlarnaNewCanceled[$i],
                                (double)$piKlarnaTotalAmount[$i],
                                (int)$piKlarnaCompleteReservedStatusId,
                                $piKlarnaOrderNumber,
                                $piKlarnaArticleNr[$i]
                            ));
                        }
                        else {
                            $piKlarnaCompleteCancelStatusId=piKlarnaGetCompleteCancelStatusId();
                            $piKlarnaReserverationCanceledStatusId=piKlarnaGetReserverationCanceledStatusId();
                            $sql = "UPDATE Pi_klarna_payment_order_detail
                                    SET
                                        offen = ?,
                                        storniert = ?,
                                        gesamtpreis = ?,
                                        bezahlstatus = ?,
                                        versandstatus = ?
                                    WHERE ordernumber = ?
                                    AND bestell_nr = ?";
                            Shopware()->Db()->query($sql, array(
                                (int)$piKlarnaNewOpen[$i],
                                (int)$piKlarnaNewCanceled[$i],
                                (double)$piKlarnaTotalAmount[$i],
                                (int)$piKlarnaReserverationCanceledStatusId,
                                (int)$piKlarnaCompleteCancelStatusId,
                                $piKlarnaOrderNumber,
                                $piKlarnaArticleNr[$i]
                            ));
                            $sql = "SELECT (bestellt - storniert) FROM Pi_klarna_payment_order_detail WHERE ordernumber = ? AND bestell_nr = ?";
                            $piKlarnaOrderquantity = Shopware()->Db()->fetchOne($sql, array($piKlarnaOrderNumber, $piKlarnaArticleNr[$i]));
                            $sql = "UPDATE s_order_details
                                SET quantity = ?
                                WHERE ordernumber = ?
                                AND articleordernumber = ?";
                            Shopware()->Db()->query($sql, array((int)$piKlarnaOrderquantity, $piKlarnaOrderNumber, $piKlarnaArticleNr[$i]));
                        }
                        $piKlarnaReturn['error'] = false;
                        $piKlarnaReturn['errormessage'] = "Komplett";
                        $sql = "SELECT (bestellt - storniert - retourniert)
                                FROM Pi_klarna_payment_order_detail
                                WHERE ordernumber = ?
                                AND bestell_nr = ?";
                        $piKlarnaOrderquantity = Shopware()->Db()->fetchOne($sql, array($piKlarnaOrderNumber, $piKlarnaArticleNr[$i]));
                        $sql = "UPDATE s_order_details
                                SET quantity = ?
                                WHERE ordernumber = ?
                                AND articleordernumber = ?";
                        Shopware()->Db()->query($sql, array((int)$piKlarnaOrderquantity, $piKlarnaOrderNumber, $piKlarnaArticleNr[$i]));
                    }
                    $piKlarnaTotalFlag = true;
                    $sql ="SELECT geliefert FROM Pi_klarna_payment_order_detail WHERE ordernumber = ?";
                    $piKlarnaDelivered = Shopware()->Db()->fetchAll($sql, array($piKlarnaOrderNumber));
                    for ($i = 0; $i < sizeof($piKlarnaDelivered); $i++) {
                        if ($piKlarnaDelivered[$i]['geliefert'] > 0) {
                            $piKlarnaTotalFlag = false;
                        }
                    }
                    $piKlarnaAcceptedStatusId=piKlarnaGetAcceptedStatusId();
                    if ($piKlarnaTotalFlag == false) {
                        $sql = "UPDATE s_order SET status = 7, cleared = ? WHERE ordernumber = ?";
                        Shopware()->Db()->query($sql, array($piKlarnaAcceptedStatusId, $piKlarnaOrderNumber));
                        $piKlarnaHistoryEvent = "<b class=\"green\">Bestellung vollst&auml;ndig versendet</b>";
                        $sql = "INSERT INTO Pi_klarna_payment_history (ordernumber, event) VALUES (?, ?)";
                        Shopware()->Db()->query($sql, array($piKlarnaOrderNumber, $piKlarnaHistoryEvent));
                    }
                    else {
                        $piKlarnaCompleteCancelStatusId=piKlarnaGetCompleteCancelStatusId();
                        $piKlarnaReserverationCanceledStatusId=piKlarnaGetReserverationCanceledStatusId();
                        $sql = "UPDATE s_order SET status = ?, cleared = ? WHERE ordernumber = ?";
                        Shopware()->Db()->query($sql, array((int)$piKlarnaCompleteCancelStatusId, (int)$piKlarnaReserverationCanceledStatusId, $piKlarnaOrderNumber));
                        $piKlarnaHistoryEvent = "<b class=\"red\">Bestellung vollst&auml;ndig storniert</b>";
                        $sql = "INSERT INTO Pi_klarna_payment_history (ordernumber, event) VALUES (?, ?)";
                        Shopware()->Db()->query($sql, array($piKlarnaOrderNumber, $piKlarnaHistoryEvent));
                    }
                    piKlarnaCalculateNewAmount($piKlarnaOrderNumber);
                    echo json_encode(array(
                        'articlename' => $piKlarnaFlag,
                        'k_return' => $piKlarnaReturn,
                        'komplett' => true,
                        'geliefert' => $piKlarnaDelivered,
                        'mytotalflag' => $piKlarnaTotalFlag
                    ));
                }
                catch (Exception $e) {
                    $piKlarnaReturn['error'] = true;
                    $piKlarnaReturn['errormessage'] = $e->getMessage() . " (#" . $e->getCode() . ")";
                    echo json_encode(array(
                        'articlename' => $piKlarnaFlag,
                        'k_return' => $piKlarnaReturn,
                        'komplett' => true
                    ));
                }
            }
            else {
                $piKlarnaReturn = piKlarnaChangeReservation($piKlarnaNewRestAmount, $rno, 'deletearticle');
                if ($piKlarnaReturn['error'] == false) {
                    for ($i = 0; $i < sizeof($piKlarnaArticleNr); $i++) {
                        $sql = "UPDATE Pi_klarna_payment_order_detail
                            SET
                                offen = ?,
                                storniert = ?,
                                gesamtpreis = ?
                            WHERE ordernumber = ?
                            AND bestell_nr = ?";
                        Shopware()->Db()->query($sql, array(
                            (int)$piKlarnaNewOpen[$i],
                            (int)$piKlarnaNewCanceled[$i],
                            (double)$piKlarnaTotalAmount[$i],
                            $piKlarnaOrderNumber,
                            $piKlarnaArticleNr[$i]
                        ));
                        $sql = "SELECT (bestellt - storniert)
                                FROM Pi_klarna_payment_order_detail
                                WHERE ordernumber = ?
                                AND bestell_nr = ?";
                        $piKlarnaOrderquantity = Shopware()->Db()->fetchOne($sql, array($piKlarnaOrderNumber, $piKlarnaArticleNr[$i]));
                        $sql = "UPDATE s_order_details
                            SET quantity= ?
                            WHERE ordernumber = ?
                            AND articleordernumber = ?";
                        $piKlarnaOrderquantity = Shopware()->Db()->query($sql, array(
                            (int)$piKlarnaOrderquantity, 
                            $piKlarnaOrderNumber, 
                            $piKlarnaArticleNr[$i]));
                        if ($piKlarnaQuantity[$i] > 0) {
                            if ($piKlarnaArticle[$i]['name'] == 'Versandkosten') {
                                $sql = "UPDATE s_order
                                    SET
                                        invoice_shipping = 0,
                                        invoice_shipping_net = 0,
                                        dispatchID = 0
                                    WHERE ordernumber = ?";
                                
                                Shopware()->Db()->query($sql, array($piKlarnaOrderNumber));
                            }
                            //$piKlarnaArticlename = str_replace("'", "\'", $piKlarnaArticle[$i]['name']);
                            if(!get_magic_quotes_gpc())
                            {
                                $piKlarnaArticlename = str_replace("'", "\'", $piKlarnaArticle[$i]['name']);
                            }
                            $piKlarnaHistoryEvent = "<span class=\"red\">Artikel storniert</span>";
                            $sql = "INSERT INTO Pi_klarna_payment_history
                                (
                                    ordernumber, event, name, bestellnr, anzahl
                                )
                                VALUES
                                (
                                    ?, ?, ?, ?, ?
                                )";
                            Shopware()->Db()->query($sql, array(
                                $piKlarnaOrderNumber,
                                $piKlarnaHistoryEvent,
                                $piKlarnaArticlename,
                                $piKlarnaArticleNr[$i],
                                $piKlarnaQuantity[$i]
                            ));
                            if ($piKlarnaNewOpen[$i] == 0 && $piKlarnaDelivered[$i] == 0) {
                                $piKlarnaCompleteCancelStatusId=piKlarnaGetCompleteCancelStatusId();
                                $piKlarnaReserverationCanceledStatusId=piKlarnaGetReserverationCanceledStatusId();
                                $sql = "UPDATE Pi_klarna_payment_order_detail
                                    SET
                                        bezahlstatus= ?,
                                        versandstatus= ?
                                    WHERE ordernumber = ?
                                    AND bestell_nr = ?";
                                Shopware()->Db()->query($sql, array(
                                    (int)$piKlarnaReserverationCanceledStatusId,
                                    (int)$piKlarnaCompleteCancelStatusId,
                                    $piKlarnaOrderNumber,
                                    $piKlarnaArticleNr[$i]
                                ));
                            }
                            elseif ($piKlarnaNewOpen[$i] == 0 && $piKlarnaDelivered[$i] > 0) {
                                $piKlarnaCompleteReservedStatusId=piKlarnaGetCompleteReservedStatusId();
                                $sql = "UPDATE Pi_klarna_payment_order_detail
                                    SET
                                        bezahlstatus = ?,
                                        versandstatus = 7
                                    WHERE ordernumber = ?
                                    AND bestell_nr = ?";
                                Shopware()->Db()->query($sql, array(
                                    (int)$piKlarnaCompleteReservedStatusId,
                                    $piKlarnaOrderNumber,
                                    $piKlarnaArticleNr[$i]
                                ));
                            }
                            elseif ($piKlarnaNewOpen[$i] > 0 && $piKlarnaDelivered[$i] == 0) {
                                $piKlarnaPartCanceledStatusId=piKlarnaGetPartCanceledStatusId();
                                $sql = "UPDATE Pi_klarna_payment_order_detail
                                    SET
                                        bezahlstatus = 18,
                                        versandstatus = ?
                                    WHERE ordernumber = ?
                                    AND bestell_nr = ?";
                                Shopware()->Db()->query($sql, array(
                                    (int)$piKlarnaPartCanceledStatusId,
                                    $piKlarnaOrderNumber,
                                    $piKlarnaArticleNr[$i]
                                ));
                            }
                        }
                    }
                }
                echo json_encode(array(
                    "total" => $piKlarnaFlag,
                    "k_return" => $piKlarnaReturn,
                    "neuerrestpreis" => $piKlarnaNewRestAmount,
                    "rechnungspreis" => $piKlarnaInvoiceAmount,
                    "geliefertpreis" => $piKlarnaDeliveredPrice
                ));
            }
            piKlarnaCalculateNewAmount($piKlarnaOrderNumber);
        }
    }

    /**
     * Gets articles for the return window. Delivers a JSON String with article details
     *
     * @see templates/backend/plugins/PigmbhKlarnaPayment/index.php
     */
    public function getReturnArticlesAction()
    {
        $this->View()->setTemplate();
        $piKlarnaOrderNumber = $this->Request()->myordernumber;
        $piKlarnaSearchString = "";
        $piKlarnaArticles = array();
        $sql = "SELECT invoice_number FROM Pi_klarna_payment_bills WHERE order_number =?";
        $piKlarnaInvoiceNumber = Shopware()->Db()->fetchAll($sql, array($piKlarnaOrderNumber));
        if (sizeof($piKlarnaInvoiceNumber) > 1) {
            for ($i = 1; $i < sizeof($piKlarnaInvoiceNumber); $i++) {
                $piKlarnaSearchString.= "  OR invoice_number ='" . $piKlarnaInvoiceNumber[$i]['invoice_number'] . "'
				";
            }
        }
        if (isset($this->Request()->search)) {
            $piKlarnaSearch = '%'.$this->Request()->search.'%';
            $sql = "SELECT *
                    FROM Pi_klarna_payment_bills_articles
                    WHERE invoice_number like ?
                    AND order_number = ?
                    AND anzahl > 0";
            $piKlarnaArticles = Shopware()->Db()->fetchAll($sql, array($piKlarnaSearch, $piKlarnaOrderNumber));
        }
        else {
            $sql = "SELECT * FROM Pi_klarna_payment_bills_articles WHERE anzahl > 0 AND order_number = ?";
            $piKlarnaArticles = Shopware()->Db()->fetchAll($sql, array($piKlarnaOrderNumber));
        }
        if (sizeof($piKlarnaArticles) > 0) {
            for ($i = 0; $i < sizeof($piKlarnaArticles); $i++) {
                $piKlarnaArticles[$i]['gesamtpreis'] =
                    number_format($piKlarnaArticles[$i]['einzelpreis'] * $piKlarnaArticles[$i]['anzahl'], 2, ',', '.');
                $piKlarnaArticles[$i]['einzelpreis'] = number_format($piKlarnaArticles[$i]['einzelpreis'], 2, ',', '.');
                $piKlarnaArticles[$i]['name'] = htmlentities($piKlarnaArticles[$i]['name'], ENT_COMPAT | ENT_HTML401,'UTF-8');
                $piKlarnaArticles[$i]['geliefert'] = $piKlarnaArticles[$i]['anzahl'];
            }
        }
        echo json_encode(array("total" => count($piKlarnaArticles), "items" => $piKlarnaArticles));
    }

    /**
     * Returns articles from the order. Delivers a JSON String with article details
     *
     * @see templates/backend/plugins/PigmbhKlarnaPayment/index.php
     * @throws KlarnaException
     */
    public function returnArticlesAction()
    {
        $this->View()->setTemplate();
        $piKlarnaArticleNr = explode(";", $this->Request()->articlenr);
        $piKlarnaQuantity = explode(";", $this->Request()->anzahl);
        $piKlarnaInvoiceNumber = explode(";", $this->Request()->rechnungsnr);
        $piKlarnaOrderNumber = $this->Request()->myordernumber;
        $piKlarnaError = array();
        $piKlarnaQuantityprorechnung = array();
        $piKlarnaNewInvoiceNumber = array();
        $piKlarnaArticlesQuantity = array();
        $myresult = array();
        if (sizeof($piKlarnaArticleNr) > 0) {
            $i = 0;
            $j = 0;
            for ($i = 0; $i < sizeof($piKlarnaArticleNr); $i++) {
                if ($i == 0) {
                    $piKlarnaQuantityprorechnung[$j] = $piKlarnaQuantity[$i];
                    $piKlarnaNewInvoiceNumber[$j] = $piKlarnaInvoiceNumber[$i];
                    $sql = "SELECT SUM(anzahl) FROM Pi_klarna_payment_bills_articles WHERE invoice_number = ?";
                    $piKlarnaArticlesQuantity[$j] = Shopware()->Db()->fetchOne($sql, array($piKlarnaInvoiceNumber[$i]));
                }
                else {
                    if ($piKlarnaInvoiceNumber[$i] == $piKlarnaNewInvoiceNumber[$j]) {
                        $piKlarnaQuantityprorechnung[$j]+=$piKlarnaQuantity[$i];
                    }
                    else {
                        $j++;
                        $piKlarnaNewInvoiceNumber[$j] = $piKlarnaInvoiceNumber[$i];
                        $piKlarnaQuantityprorechnung[$j] = $piKlarnaQuantity[$i];
                        $sql = "SELECT SUM(anzahl) FROM Pi_klarna_payment_bills_articles WHERE invoice_number = ?";
                        $piKlarnaArticlesQuantity[$j] = Shopware()->Db()->fetchOne($sql, array($piKlarnaInvoiceNumber[$i]));
                    }
                }
            }
            $p = 0;
            $k = piKlarnaCreateKlarnaInstance($piKlarnaOrderNumber);
            for ($i = 0; $i < sizeof($piKlarnaNewInvoiceNumber); $i++) {
                if ($piKlarnaArticlesQuantity[$i] == $piKlarnaQuantityprorechnung[$i]) {
                    try {
                        $credNo = '';
                        $result = $k->creditInvoice(
                            $piKlarnaNewInvoiceNumber[$i], $credNo = ''
                        );
                        $piKlarnaError['errormessage'] = "";
                        $piKlarnaError['error'] = false;
                        $myresult[$p] = $result;
                        $p++;
                    }
                    catch (Exception $e) {
                        $piKlarnaError['error'] = true;
                        $piKlarnaError['errormessage'] = $e->getMessage() . " (#" . $e->getCode() . ")";
                    }
                }
                else {
                    for ($j = 0; $j < sizeof($piKlarnaArticleNr); $j++) {
                        if ($piKlarnaInvoiceNumber[$j] == $piKlarnaNewInvoiceNumber[$i]) {
                            if ($piKlarnaQuantity[$j] > 0) {
                                $qty = $piKlarnaQuantity[$j];
                                $artNo = $piKlarnaArticleNr[$j];
                                $result = array();
                                $credNo = '';
                                $k->addArtNo(
                                    $qty = $piKlarnaQuantity[$j],
                                    $artNo = $piKlarnaArticleNr[$j]); 
                                try {
                                    $result = $k->creditPart(
                                        $piKlarnaNewInvoiceNumber[$i], $credNo = '' 
                                    );
                                    $piKlarnaError['errormessage'] = "";
                                    $piKlarnaError['error'] = false;
                                }
                                catch (Exception $e) {
                                    $piKlarnaError['error'] = true;
                                    $piKlarnaError['errormessage'] = $e->getMessage() . " (#" . $e->getCode() . ")";
                                }
                            }
                        }
                    }
                }
            }
            if ($piKlarnaError['errormessage'] == "") {
                for ($i = 0; $i < sizeof($piKlarnaArticleNr); $i++) {
                    if ($piKlarnaQuantity[$i] > 0) {
                        $sql = "UPDATE Pi_klarna_payment_order_detail
                                SET
                                    retourniert = retourniert + ?,
                                    geliefert = geliefert - ?
                                WHERE ordernumber = ?
                                AND bestell_nr = ?";
                        Shopware()->Db()->query($sql,array(
                            (int)$piKlarnaQuantity[$i], 
                            (int)$piKlarnaQuantity[$i], 
                            $piKlarnaOrderNumber, 
                            $piKlarnaArticleNr[$i]
                        ));
                        
                        $sql = "UPDATE Pi_klarna_payment_order_detail 
                                SET gesamtpreis = geliefert * einzelpreis
                                WHERE ordernumber = ?
                                AND bestell_nr = ?";
                        Shopware()->Db()->query($sql,array(
                            $piKlarnaOrderNumber, 
                            $piKlarnaArticleNr[$i]
                        ));
                        $sql = "UPDATE s_order_details SET quantity = quantity - ? WHERE ordernumber = ? AND articleordernumber = ?";
                        Shopware()->Db()->query($sql,array(
                            (int)$piKlarnaQuantity[$i], 
                            $piKlarnaOrderNumber, 
                            $piKlarnaArticleNr[$i]
                        ));
                        $sql = "UPDATE Pi_klarna_payment_bills_articles
                                SET anzahl = anzahl - ?
                                WHERE order_number = ?
                                AND bestell_nr = ?
                                AND invoice_number = ?";
                        Shopware()->Db()->query($sql,array(
                            (int)$piKlarnaQuantity[$i], 
                            $piKlarnaOrderNumber, 
                            $piKlarnaArticleNr[$i],
                            $piKlarnaInvoiceNumber[$i]
                        ));
                        $sql = "SELECT bestellt
                                FROM Pi_klarna_payment_order_detail
                                WHERE ordernumber = ?
                                AND bestell_nr = ?
                        ";
                        $piKlarnaOrdered = Shopware()->Db()->fetchOne($sql,array($piKlarnaOrderNumber, $piKlarnaArticleNr[$i]));
                        $sql = "SELECT retourniert
                            FROM Pi_klarna_payment_order_detail
                            WHERE ordernumber = ?
                            AND bestell_nr= ?
                        ";
                        $piKlarnaReturned = Shopware()->Db()->fetchOne($sql,array($piKlarnaOrderNumber, $piKlarnaArticleNr[$i]));
                        if ($piKlarnaOrdered == $piKlarnaReturned) {
                            $piKlarnaCompleteReturnStatusId=piKlarnaGetCompleteReturnStatusId();
                            $sql = "UPDATE Pi_klarna_payment_order_detail
                                    SET versandstatus = ?
                                    WHERE ordernumber = ?
                                    AND bestell_nr = ?";
                            Shopware()->Db()->query($sql, array(
                                (int)$piKlarnaCompleteReturnStatusId, 
                                $piKlarnaOrderNumber, 
                                $piKlarnaArticleNr[$i]
                            ));
                        }
                        if ($piKlarnaArticleNr[$i] == 'versand_' . $piKlarnaOrderNumber) {
                            $sql = "UPDATE s_order
                                    SET
                                        invoice_shipping = 0,
                                        invoice_shipping_net = 0,
                                        dispatchID = 0
                                    WHERE ordernumber = ?";
                            Shopware()->Db()->query($sql, array($piKlarnaOrderNumber));
                        }
                        $sql = "SELECT name FROM Pi_klarna_payment_order_detail WHERE ordernumber = ? AND bestell_nr= ?";
                        $piKlarnaArticlename = Shopware()->Db()->fetchOne($sql, array($piKlarnaOrderNumber, $piKlarnaArticleNr[$i]));
//                        $piKlarnaArticlename = str_replace("'", "\'", $piKlarnaArticlename);
			$piKlarnaArticlename = $piKlarnaArticlename;
                        $piKlarnaHistoryEvent = "<span class=\"red\">Artikel retourniert</span>";
                        $sql = "INSERT INTO Pi_klarna_payment_history
                                (
                                    ordernumber, event, name, bestellnr, anzahl
                                )
                                VALUES
                                (
                                    ?, ?, ?, ?, ?
                                )";
                        Shopware()->Db()->query($sql, array(
                            $piKlarnaOrderNumber,
                            $piKlarnaHistoryEvent,
                            $piKlarnaArticlename,
                            $piKlarnaArticleNr[$i],
                            $piKlarnaQuantity[$i] 
                        ));
                    }
                }
            }
        }
        piKlarnaCalculateNewAmount($piKlarnaOrderNumber);
        $sql = "SELECT invoice_amount FROM s_order WHERE ordernumber = ?";
        $piKlarnaOrderAmount = Shopware()->Db()->fetchOne($sql, array($piKlarnaOrderNumber));
        if ($piKlarnaOrderAmount == 0) {
            $piKlarnaCompleteReturnStatusId=piKlarnaGetCompleteReturnStatusId($piKlarnaOrderNumber);
            $sql = "UPDATE s_order SET status = ? WHERE ordernumber = ?";
            Shopware()->Db()->query($sql, array((int)$piKlarnaCompleteReturnStatusId, $piKlarnaOrderNumber));
            $sql = "INSERT INTO Pi_klarna_payment_history (ordernumber, event) VALUES (?, ?)";
            Shopware()->Db()->query($sql, array($piKlarnaOrderNumber, '<b class=\'red\'>Bestellung vollst&auml;ndig retourniert</b>'));
        }
        echo json_encode(array(
            "items" => $piKlarnaError,
            "articlesquantity" => $piKlarnaArticlesQuantity,
            "anzahlprorechnung" => $piKlarnaQuantityprorechnung,
            "rechnungsnummer" => $piKlarnaNewInvoiceNumber,
            "rechnungsnr" => $piKlarnaInvoiceNumber
        ));
    }

    /**
     * Gets invoices for the invoice window. Delivers a JSON String with invoice details
     *
     * @see templates/backend/plugins/PigmbhKlarnaPayment/index.php
     */
    public function getInvoicesAction()
    {
        $this->View()->setTemplate();
        $piKlarnaOrderNumber = $this->Request()->myordernumber;
        $sql = "SELECT * FROM `Pi_klarna_payment_bills` WHERE order_number = ?";
        $piKlarnaBills = Shopware()->Db()->fetchAll($sql, array($piKlarnaOrderNumber));
        for ($i = 0; $i < sizeof($piKlarnaBills); $i++) {
            if ($piKlarnaBills[$i]['method'] == 'Letzte Rechnung') {
                $piKlarnaBills[$i]['method'] = '<span class="green">Letzte Rechnung</span>';
            }
            elseif ($piKlarnaBills[$i]['method'] == 'Komplette Rechnung') {
                $piKlarnaBills[$i]['method'] = '<span class="green">Komplette Rechnung</span>';
            }
            $piKlarnaBills[$i]['invoice_amount'] = number_format($piKlarnaBills[$i]['invoice_amount'], 2, ',', '.');
            $piKlarnaBills[$i]['id'] = $i + 1;
            $piKlarnaBills[$i]['open'] = Shopware()->DocPath() . "files/documents/" . $piKlarnaBills[$i]['invoice_number'] . ".pdf";
        }
        echo json_encode(array("total" => count($piKlarnaBills), "items" => $piKlarnaBills));
    }

    /**
     * Advises Klarna to send the invoice per e-mail to the customer.
     *
     * @see templates/backend/plugins/PigmbhKlarnaPayment/index.php
     * @throws KlarnaException
     */
    public function sendInvoiceMailAction()
    {
        $this->View()->setTemplate();
        $piKlarnaOrderNumber = $this->Request()->ordernumber;
        $piKlarnaInvoice = $this->Request()->invoice;
        $result= array();
        $k = piKlarnaCreateKlarnaInstance($piKlarnaOrderNumber);
        try {
            $result = $k->emailInvoice($piKlarnaInvoice);
        }
        catch (Exception $e) {
            echo $e->getMessage() . " (#" . $e->getCode() . ")";
        }
        echo json_encode(array("total" => count($result), "items" => $result));
    }

    /**
     * Advises Klarna to send the invoice per post to the customer.
     *
     * @see templates/backend/plugins/PigmbhKlarnaPayment/index.php
     * @throws KlarnaException
     */
    public function sendInvoicePostAction()
    {
        $this->View()->setTemplate();
        $piKlarnaOrderNumber = $this->Request()->ordernumber;
        $piKlarnaInvoice = $this->Request()->invoice;
        $result= array();
        $k = piKlarnaCreateKlarnaInstance($piKlarnaOrderNumber);
        try {
            $result = $k->sendInvoice($piKlarnaInvoice);
        }
        catch (Exception $e) {
            echo $e->getMessage() . " (#" . $e->getCode() . ")";
        }
        echo json_encode(array("total" => count($result), "items" => $result));
    }

    /**
     * deletes invoice.
     *
     * @see templates/backend/plugins/PigmbhKlarnaPayment/index.php
     * @throws KlarnaException
     */
    public function deleteInvoiceAction()
    {
        $this->View()->setTemplate();
        $piKlarnaInvoice = $this->Request()->invoice;
        $sql = "DELETE FROM Pi_klarna_payment_bills WHERE invoice_number = ?";
        Shopware()->Db()->query($sql, array($piKlarnaInvoice));
        echo json_encode(array("total" => count($piKlarnaInvoice), "items" => $piKlarnaInvoice));
    }

     /**
     * fetch pClass .     *
     * @see templates/backend/plugins/PigmbhKlarnaPayment/index.php
     */
    public function piKlarnaFetchPClassAction()
    {
        $this->View()->setTemplate();
        $piKlarnaEidKey = $this->Request()->pi_klarna_eidKey;
        $piKlarnaSecret = $this->Request()->pi_klarna_secret;
        $piKlarnaError = piKlarnaFetchKlarnaPClass($piKlarnaEidKey, $piKlarnaSecret);
        if(substr($piKlarnaError, 0, 12)=="Ratenzahlung")
            $total=1;
        else
            $total=0; 
        echo json_encode(array("total" => $total, "pi_klarna_error" => $piKlarnaError));
    }
    
    public function showPdfAction()
    {
        $this->View()->setTemplate();
        
        $documentsPath = Shopware()->DocPath('files_documents');
        $piKlarnaInvoiceId = $this->Request()->pdf;
        $targetFile = $documentsPath.$piKlarnaInvoiceId.".pdf";
        if (!is_file( $targetFile)){
            die("FAIL");
        }
        header("Cache-Control: public");
        header("Content-Description: File Transfer");
        header('Content-disposition: attachment; filename='.basename($targetFile));
        header("Content-Type: application/pdf");
        header("Content-Transfer-Encoding: binary");
        header('Content-Length: '. filesize($targetFile)); 
        echo readfile($targetFile);
        
    }
}

