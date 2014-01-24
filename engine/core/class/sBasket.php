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
 * Deprecated Shopware Class that handles cart operations
 * Will be replaced towards to shopware 4.n
 */
class sBasket
{
    public $sSYSTEM;
    public $sBASKET;

    /**
     * @var Shopware_Components_Snippet_Manager
     */
    public $snippetObject;

    public function __construct()
    {
        $this->snippetObject = Shopware()->Snippets()->getNamespace('frontend/basket/internalMessages');
    }
    /**
     * Get total turn-over of current users cart
     * @access public
     * @deprecated
     * @return array
     */
    public function sGetAmount()
    {
        return  $this->sSYSTEM->sDB_CONNECTION->GetRow("SELECT SUM(quantity*(floor(price * 100 + .55)/100))
        AS totalAmount FROM s_order_basket WHERE sessionID=? GROUP BY sessionID",array($this->sSYSTEM->sSESSION_ID));
    }

    /**
     * Get total turn-over of current users cart (only products)
     * @access public
     * @deprecated
     * @return array
     */
    public function sGetAmountArticles()
    {
        return  $this->sSYSTEM->sDB_CONNECTION->GetRow("SELECT SUM(quantity*(floor(price * 100 + .55)/100))
        AS totalAmount FROM s_order_basket WHERE sessionID=? AND modus=0 GROUP BY sessionID",array($this->sSYSTEM->sSESSION_ID));
    }

    /**
     * Check if all positions in cart are available
     * @access public
     * @deprecated
     * @return array
     */
    public function sCheckBasketQuantities()
    {
        $sql = "
            SELECT
              (d.instock - b.quantity) as diffStock, b.ordernumber,
              a.laststock, IF(a.active=1, d.active, 0) as active
            FROM s_order_basket b
            LEFT JOIN s_articles_details d
            ON d.ordernumber = b.ordernumber
            AND d.articleID = b.articleID
            LEFT JOIN s_articles a
            ON a.id = d.articleID
            WHERE b.sessionID = ?
            AND b.modus = 0
            GROUP BY b.ordernumber
        ";
        $result = $this->sSYSTEM->sDB_CONNECTION->GetAll($sql,array($this->sSYSTEM->sSESSION_ID));
        $hideBasket = false;
        foreach ($result as $article) {
            if (empty($article['active'])
              || (!empty($article['laststock']) && $article["diffStock"] < 0)){
                $hideBasket = true;
                $articles[$article["ordernumber"]]["OutOfStock"] = true;
            } else {
                $articles[$article["ordernumber"]]["OutOfStock"] = false;
            }
        }
        return array("hideBasket"=>$hideBasket,"articles"=>$articles);
    }

    /**
     * Get cart-amount for certain products / suppliers
     * @access public
     * @deprecated
     * @return array
     */
    public function sGetAmountRestrictedArticles($articles,$supplier)
    {
        if (!is_array($articles) && empty($supplier)) return $this->sGetAmountArticles();
        if (is_array($articles)) {
            foreach ($articles as $article) {
                $article = $article;
                $newArticles[] = $article;
            }
            $in = implode(",",$newArticles);
            $articleSQL = "ordernumber IN ($in) ";
        }
        if (!empty($supplier)) {
            if (empty($articleSQL)) {
                $articleSQL = "1 != 1 ";
            }
            $supplierSQL = "OR s_articles.supplierID = $supplier ";
        }
        return  $this->sSYSTEM->sDB_CONNECTION->GetRow("SELECT SUM(quantity*(floor(price * 100 + .55)/100))
        AS totalAmount FROM s_order_basket, s_articles WHERE sessionID=? AND modus=0 AND s_order_basket.articleID=s_articles.id
        AND
        (
        $articleSQL
        $supplierSQL
        )
        GROUP BY sessionID",array($this->sSYSTEM->sSESSION_ID));
    }

    /**
     * Update vouchers in cart
     * @access public
     * @deprecated
     * @return array
     */
    public function sUpdateVoucher()
    {
        $sql = 'SELECT id basketID, ordernumber, articleID as voucherID FROM s_order_basket WHERE modus=2 AND sessionID=?';
        $voucher = $this->sSYSTEM->sDB_CONNECTION->GetRow($sql, array($this->sSYSTEM->sSESSION_ID));
        if (!empty($voucher)) {
            $sql = 'SELECT vouchercode FROM s_emarketing_vouchers WHERE ordercode=?';
            $voucher['code'] = $this->sSYSTEM->sDB_CONNECTION->GetOne($sql, array($voucher['ordernumber']));
            if (empty($voucher['code'])) {
                $sql = 'SELECT code FROM s_emarketing_voucher_codes WHERE id=?';
                $voucher['code'] = $this->sSYSTEM->sDB_CONNECTION->GetOne($sql, array($voucher['voucherID']));
            }
            $this->sDeleteArticle($voucher['basketID']);
            $this->sAddVoucher($voucher['code']);
        }
    }
    /**
     * Insert basket discount
     * @deprecated
     * @access public
     * @return void
     */
    public function sInsertDiscount()
    {
        // Get possible discounts
        $getDiscounts = $this->sSYSTEM->sDB_CONNECTION->GetAll("
        SELECT basketdiscount, basketdiscountstart FROM s_core_customergroups_discounts
        WHERE groupID=?
        ORDER BY basketdiscountstart ASC
        ",array($this->sSYSTEM->sUSERGROUPDATA["id"]));


        $rs = $this->sSYSTEM->sDB_CONNECTION->Execute("
        DELETE FROM s_order_basket WHERE sessionID=? AND modus=3
        ",array($this->sSYSTEM->sSESSION_ID));

        // No discounts
        if (!count($getDiscounts)) {
            return;
        }

        $basketAmount = $this->sSYSTEM->sDB_CONNECTION->GetOne("SELECT SUM(quantity*(floor(price * 100 + .55)/100))
        AS totalAmount FROM s_order_basket WHERE sessionID=? AND modus!=4 GROUP BY sessionID",array($this->sSYSTEM->sSESSION_ID));

        if (!$basketAmount) return;	// No articles in basket, return

        if ($this->sSYSTEM->sCurrency["factor"]) {
        } else {
            $factor = 1;
        }


        // Iterate through discounts and find nearly one
        foreach ($getDiscounts as $discountRow) {
            if ($basketAmount<$discountRow["basketdiscountstart"]) {
                break;
            } else {
                $basketDiscount = $discountRow["basketdiscount"];
            }
        }

        if (!$basketDiscount) return;

        $insertTime = date("Y-m-d H:i:s");
        $discount = $basketAmount / 100 * $basketDiscount;
        $discount = $discount * -1;
        $discount = round($discount,2);

        if (!empty($this->sSYSTEM->sCONFIG["sTAXAUTOMODE"])) {
            $tax = $this->sSYSTEM->sMODULES['sBASKET']->getMaxTax();
        } else {
            $tax = $this->sSYSTEM->sCONFIG['sDISCOUNTTAX'];
        }

        if (!empty($this->sSYSTEM->sCONFIG["sTAXAUTOMODE"])) {
            $tax = $this->sSYSTEM->sMODULES['sBASKET']->getMaxTax();
        }
        if (!$tax) $tax = 19;

        if (!$this->sSYSTEM->sUSERGROUPDATA["tax"] && $this->sSYSTEM->sUSERGROUPDATA["id"]) {
            $discountNet = $discount;
        } else {
            $discountNet = round($discount / (100+$tax) * 100,3);
        }

        // Add discount - info to user-account
        $this->sSYSTEM->sUSERGROUPDATA["basketdiscount"] = $basketDiscount;
        // --
        $name = isset($this->sSYSTEM->sCONFIG['sDISCOUNTNUMBER']) ? $this->sSYSTEM->sCONFIG['sDISCOUNTNUMBER']: "DISCOUNT";

        $discountName = - $basketDiscount . ' % ' . $this->sSYSTEM->sCONFIG["sDISCOUNTNAME"];

        $params = array(
            $this->sSYSTEM->sSESSION_ID,
            $discountName,
            0,
            $name,
            1,
            $discount,
            $discountNet,
            $tax,
            $insertTime,
            3,
            $this->sSYSTEM->sCurrency["factor"]
        );
        $sql = "
        INSERT INTO s_order_basket (
            sessionID,
            articlename,
            articleID,
            ordernumber,
            quantity,
            price,
            netprice,
            tax_rate,
            datum,
            modus,
            currencyFactor
        )
        VALUES (
            ?,?,?,?,?,?,?,?,?,?,?)";

        $insertDiscount = $this->sSYSTEM->sDB_CONNECTION->Execute($sql, $params);

    }

    /**
     * Check if any discount is in cart
     * @access public
     * @deprecated
     * @return void
     */
    public function sCheckForDiscount()
    {
        $rs = $this->sSYSTEM->sDB_CONNECTION->GetRow("
        SELECT id FROM s_order_basket WHERE sessionID=? AND modus=3
        ",array($this->sSYSTEM->sSESSION_ID));

        if ($rs["id"]) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Add premium products to cart
     * @access public
     * @deprecated
     * @return bool|int
     */
    public function sInsertPremium()
    {
        static $last_premium;

        $sBasketAmount = $this->sGetAmount();
        $sBasketAmount = empty($sBasketAmount["totalAmount"]) ? 0 :$sBasketAmount["totalAmount"];
        $sBasketAmount = (float) $sBasketAmount;

        if (empty($this->sSYSTEM->_GET["sAddPremium"])) {
            $sql = "
                SELECT b.id
                FROM s_order_basket b

                LEFT JOIN s_addon_premiums p
                ON p.id=b.articleID
                AND p.startprice <= ?

                WHERE b.modus =1
                AND p.id IS NULL
                AND b.sessionID = ?
            ";

            $deletePremium = Shopware()->Db()->fetchCol($sql, array($sBasketAmount, $this->sSYSTEM->sSESSION_ID));
            if (empty($deletePremium)) {
                return true;
            }

            $deletePremium = Shopware()->Db()->quote($deletePremium);
            $sql= "DELETE FROM s_order_basket WHERE id IN ($deletePremium)";
            Shopware()->Db()->query($sql);
            return true;
        }

        if (empty($this->sSYSTEM->_GET["sAddPremium"]))
            return false;

        if (isset($last_premium) && $last_premium == $this->sSYSTEM->_GET["sAddPremium"])
            return false;

        $last_premium = $this->sSYSTEM->_GET["sAddPremium"];

        $this->sSYSTEM->sDB_CONNECTION->Execute("
            DELETE FROM s_order_basket WHERE sessionID='".$this->sSYSTEM->sSESSION_ID."' AND modus=1
        ");

        $ordernumber = $this->sSYSTEM->sDB_CONNECTION->qstr($this->sSYSTEM->_GET["sAddPremium"]);

        $sql = "
            SELECT p.id, d.ordernumber, a.id as articleID, a.name, d.additionaltext, p.ordernumber_export, a.configurator_set_id
            FROM
                s_addon_premiums p,
                s_articles_details d,
                s_articles a,
                s_articles_details d2
            WHERE d.ordernumber=$ordernumber
            AND p.startprice<=$sBasketAmount
            AND p.ordernumber=d2.ordernumber
            AND d2.articleID=d.articleID
            AND d.articleID=a.id
        ";

        $premium = $this->sSYSTEM->sDB_CONNECTION->GetRow($sql);
        if (empty($premium)) {
            return false;
        }

        $premium = $this->sSYSTEM->sMODULES['sArticles']->sGetTranslation($premium,$premium["articleID"],"article",$this->sSYSTEM->sLanguage);
        if (!empty($premium['configurator_set_id'])) {
            $number = $premium['ordernumber'];
        } else {
            $number = $premium['ordernumber_export'];
        }

        $sql = "
            INSERT INTO s_order_basket (
                sessionID, articlename, articleID, ordernumber, quantity, price, netprice,tax_rate, datum, modus, currencyFactor
            ) VALUES (
                  ?, ?, ?, ?, ?, ?, ?, ?, NOW(), ?, ?
            )
        ";

        $data = array(
            $this->sSYSTEM->sSESSION_ID,
            trim($premium["name"] . " " . $premium["additionaltext"]),
            $premium['id'],
            $number,
            1,
            0,
            0,
            0,
            1,
            $this->sSYSTEM->sCurrency["factor"]
        );
        return Shopware()->Db()->query($sql, $data);
    }

    /**
     * Get count of different positions from current cart
     * @access public
     * @deprecated
     * @return int - Anzahl Positionen
     */
    public function sCountArticles()
    {
        $sql = 'SELECT COUNT(*) FROM s_order_basket WHERE modus=0 AND sessionID=?';
        return $this->sSYSTEM->sDB_CONNECTION->GetOne($sql, array($this->sSYSTEM->sSESSION_ID));
    }

    /**
     * Get the max used tax-rate in basket in percent
     * @deprecated
     * @return
     */
    public function getMaxTax()
    {
        $sql = "
            SELECT
                MAX(tax_rate) as max_tax
            FROM s_order_basket b
            WHERE b.sessionID=? AND b.modus=0
            GROUP BY b.sessionID
        ";
        $taxRate = $this->sSYSTEM->sDB_CONNECTION->GetOne($sql,array(empty($this->sSYSTEM->sSESSION_ID) ? session_id() : $this->sSYSTEM->sSESSION_ID));
        return $taxRate;
    }
    /**
     * Add voucher to cart
     * @param string $sTicket - voucher code
     * @access public
     * @deprecated
     * @return array
     */
    public function sAddVoucher($sTicket,$BASKET='')
    {
        if (Enlight()->Events()->notifyUntil('Shopware_Modules_Basket_AddVoucher_Start', array('subject'=>$this,'code'=>$sTicket,"basket"=>$BASKET))) {
            return false;
        }

        $sTicket = stripslashes($sTicket);
        $sTicket = strtolower($sTicket);
        $sql = "
        SELECT * FROM s_emarketing_vouchers WHERE modus != 1 AND LOWER(vouchercode)=?
        AND ((valid_to>=now() AND valid_from<=now()) OR valid_to is NULL)
        ";

        $ticketResult = $this->sSYSTEM->sDB_CONNECTION->GetRow($sql,array($sTicket));

        // Check if voucher was already cashed
        if ($this->sSYSTEM->_SESSION["sUserId"] && $ticketResult["id"]) {
            $userid = $this->sSYSTEM->_SESSION["sUserId"];
            $sql = "
            SELECT s_order_details.id AS id FROM s_order, s_order_details
            WHERE s_order.userID = $userid AND s_order_details.orderID=s_order.id
            AND s_order_details.articleordernumber = '{$ticketResult["ordercode"]}'
            AND s_order_details.ordernumber!='0'
            ";

            $queryVoucher = $this->sSYSTEM->sDB_CONNECTION->GetAll($sql);
            if (count($queryVoucher)>=$ticketResult["numorder"] && !$ticketResult["modus"]) {

                $sErrorMessages[] = $this->snippetObject->get('VoucherFailureAlreadyUsed','This voucher was used in an previous order');

                return array("sErrorFlag"=>true,"sErrorMessages"=>$sErrorMessages);
            }
        }

        if ($ticketResult["id"]) {
            //echo "NO INDIVIDUAL CODE $sTicket";
            // Check if ticket is available anymore
            $countTicket = $this->sSYSTEM->sDB_CONNECTION->GetRow("
            SELECT COUNT(id) AS vouchers FROM s_order_details WHERE articleordernumber='{$ticketResult["ordercode"]}'
            AND s_order_details.ordernumber!='0'
            ");
        } else {
            // Check for individual voucher - code
            $sql = "
            SELECT s_emarketing_voucher_codes.id AS id, s_emarketing_voucher_codes.code AS vouchercode,description, numberofunits,customergroup, value,restrictarticles, minimumcharge, shippingfree, bindtosupplier,
            taxconfig,
            valid_from,valid_to,ordercode, modus,percental,strict,subshopID FROM s_emarketing_vouchers, s_emarketing_voucher_codes
            WHERE
                modus = 1
            AND
                s_emarketing_vouchers.id = s_emarketing_voucher_codes.voucherID
            AND
                LOWER(code) = ?
            AND
                cashed != 1
            AND ((s_emarketing_vouchers.valid_to>=now() AND s_emarketing_vouchers.valid_from<=now()) OR s_emarketing_vouchers.valid_to is NULL)
            ";
            $ticketResult = $this->sSYSTEM->sDB_CONNECTION->GetRow($sql,array($sTicket));
            if ($ticketResult["description"]) {
                $indivualCode = true;
            } else {
                $indivualCode = false;
            }
        }


        // Check if ticket exists
        if (!count($ticketResult) || !$sTicket || ($ticketResult["numberofunits"]<=$countTicket["vouchers"] && !$indivualCode)) {
            $sErrorMessages[] = $this->snippetObject->get('VoucherFailureNotFound','Voucher could not be found or is not valid anymore');
            return array("sErrorFlag"=>true,"sErrorMessages"=>$sErrorMessages);
        }

        if (!empty($ticketResult["strict"])) {
            $restrictDiscount = true;
        } else {
            $restrictDiscount = false;
        }

        if (!empty($ticketResult["subshopID"])) {
            if ($this->sSYSTEM->sSubShop["id"] != $ticketResult["subshopID"]) {
                $sErrorMessages[] = $this->snippetObject->get('VoucherFailureNotFound','Voucher could not be found or is not valid anymore');
                return array("sErrorFlag"=>true,"sErrorMessages"=>$sErrorMessages);
            }
        }

        // Check if any voucher is already in basket
        $chkBasket = $this->sSYSTEM->sDB_CONNECTION->GetRow("
        SELECT id FROM s_order_basket WHERE sessionID='".$this->sSYSTEM->sSESSION_ID."' AND modus=2
        ");


        if (count($chkBasket)) {
            $sErrorMessages[] = $this->snippetObject->get('VoucherFailureOnlyOnes','Only one voucher can be processed in order');
            return array("sErrorFlag"=>true,"sErrorMessages"=>$sErrorMessages);
        }
        // Restrict to customergroup
        if (!empty($ticketResult["customergroup"])) {
            $userid = $this->sSYSTEM->_SESSION["sUserId"];

            if (!empty($userid)) {
                // Get customergroup
                $queryCustomergroup = $this->sSYSTEM->sDB_CONNECTION->GetRow("
                SELECT s_core_customergroups.id, customergroup FROM s_user, s_core_customergroups WHERE s_user.id=$userid
                AND s_user.customergroup = s_core_customergroups.groupkey
                ");
            }
            $customergroup = $queryCustomergroup["customergroup"];
            if ($customergroup != $ticketResult["customergroup"] && $ticketResult["customergroup"] != $queryCustomergroup["id"] && $ticketResult["customergroup"] != $this->sSYSTEM->sUSERGROUPDATA["id"]) {
                $sErrorMessages[] = $this->snippetObject->get('VoucherFailureCustomerGroup','This voucher is not available for your customergroup');
                return array("sErrorFlag" => true, "sErrorMessages" => $sErrorMessages);
            }
        }

        // Restrict to articles
        if (!empty($ticketResult["restrictarticles"]) && strlen($ticketResult["restrictarticles"])>5) {
            $restrictedArticles = explode(";",$ticketResult["restrictarticles"]);
            if (count($restrictedArticles)==0) $restrictedArticles[] = $ticketResult["restrictarticles"];
            foreach ($restrictedArticles as $k => $restrictedArticle) $restrictedArticles[$k] = (string) $this->sSYSTEM->sDB_CONNECTION->qstr($restrictedArticle);

            $sql = "
            SELECT id FROM s_order_basket WHERE sessionID='".$this->sSYSTEM->sSESSION_ID."' AND modus=0
            AND ordernumber IN (".implode(",",$restrictedArticles).")
            ";

            $getOrdernumbers = $this->sSYSTEM->sDB_CONNECTION->GetOne($sql);
            $foundMatchingArticle = false;

            if (!empty($getOrdernumbers)) $foundMatchingArticle = true;

            if (empty($foundMatchingArticle)) {

                $sErrorMessages[] = $this->snippetObject->get('VoucherFailureProducts','This voucher is only available in combination with certain products');
                return array("sErrorFlag"=>true,"sErrorMessages"=>$sErrorMessages);
            }
        }
        // Restrict to supplier
        if ($ticketResult["bindtosupplier"]) {
            $searchHersteller = $ticketResult["bindtosupplier"];
            $sql = "
            SELECT s_order_basket.id FROM s_order_basket, s_articles, s_articles_supplier WHERE
            s_order_basket.articleID=s_articles.id AND s_articles.supplierID=$searchHersteller
            AND s_order_basket.sessionID='".$this->sSYSTEM->sSESSION_ID."'
            ";

            $chkHersteller = $this->sSYSTEM->sDB_CONNECTION->GetRow($sql);

            if (!count($chkHersteller)) {
                // Name des Herstellers abfragen
                $queryHersteller = $this->sSYSTEM->sDB_CONNECTION->GetRow("
                SELECT name FROM s_articles_supplier WHERE id=$searchHersteller
                ");

                $sErrorMessages[] = str_replace("{sSupplier}",$queryHersteller["name"],$this->snippetObject->get('VoucherFailureSupplier','This voucher is only available for products from {sSupplier}'));
                return array("sErrorFlag"=>true,"sErrorMessages"=>$sErrorMessages);
            }
        }

        if (!empty($restrictDiscount) && (!empty($restrictedArticles) || !empty($searchHersteller))) {
            $amount =  $this->sGetAmountRestrictedArticles($restrictedArticles,$searchHersteller);
        } else {
            $amount =  $this->sGetAmountArticles();
        }
        if ($this->sSYSTEM->sCurrency["factor"] && empty($ticketResult["percental"])) {
            $factor = $this->sSYSTEM->sCurrency["factor"];
            $ticketResult["value"] *= $factor;
        } else {
            $factor = 1;
        }

        if (($amount["totalAmount"]/$factor) < $ticketResult["minimumcharge"]) {

            $sErrorMessages[] = str_replace("{sMinimumCharge}",$ticketResult["minimumcharge"],$this->snippetObject->get('VoucherFailureMinimumCharge','The minimum charge for this voucher is {sMinimumCharge}'));
            return array("sErrorFlag"=>true,"sErrorMessages"=>$sErrorMessages);
        }

        $timeInsert = date("Y-m-d H:i:s");

        $vouchername = $this->sSYSTEM->sCONFIG['sVOUCHERNAME'];
        if ($ticketResult["percental"]) {
            $value = $ticketResult["value"];
            $vouchername .= " ".$value." %";
            $ticketResult["value"] = ($amount["totalAmount"] / 100) * floatval($value);
        }


        // Free tax configuration for vouchers
        // Trac ticket 4708 st.hamann
        $taxRate = 0;
        if ((!$this->sSYSTEM->sUSERGROUPDATA["tax"] && $this->sSYSTEM->sUSERGROUPDATA["id"]) || $ticketResult["taxconfig"]=="none") {
            // if net customergroup - calculate without tax
            $tax = $ticketResult["value"] * -1;
            if ($ticketResult["taxconfig"] == "default" || empty($ticketResult["taxconfig"])) {
                $taxRate = $this->sSYSTEM->sCONFIG['sVOUCHERTAX'];
            } elseif ($ticketResult["taxconfig"]=="auto") {
                $taxRate =$this->getMaxTax();

            } elseif (intval($ticketResult["taxconfig"])) {
               $temporaryTax =$ticketResult["taxconfig"];
               $getTaxRate = $this->sSYSTEM->sDB_CONNECTION->getOne("
               SELECT tax FROM s_core_tax WHERE id = ?
               ",array($temporaryTax));
               $taxRate = $getTaxRate;
            }
        } else {
            if ($ticketResult["taxconfig"] == "default" || empty($ticketResult["taxconfig"])) {
                $tax = round($ticketResult["value"]/(100+$this->sSYSTEM->sCONFIG['sVOUCHERTAX'])*100,3)*-1;
                $taxRate = $this->sSYSTEM->sCONFIG['sVOUCHERTAX'];
                // Pre 3.5.4 behaviour
            } elseif ($ticketResult["taxconfig"]=="auto") {
                // Check max. used tax-rate from basket
                $tax = $this->getMaxTax();
                $taxRate = $tax;
                $tax = round($ticketResult["value"]/(100+$tax)*100,3)*-1;
            } elseif (intval($ticketResult["taxconfig"])) {
                // Fix defined tax
                $temporaryTax =$ticketResult["taxconfig"];
                $getTaxRate = $this->sSYSTEM->sDB_CONNECTION->getOne("
                SELECT tax FROM s_core_tax WHERE id = ?
                ",array($temporaryTax));
                $taxRate = $getTaxRate;
                $tax = round($ticketResult["value"]/(100+$getTaxRate)*100,3)*-1;
            } else {
                // No tax
                $tax = $ticketResult["value"] * -1;
            }
        }

        $ticketResult["value"] = $ticketResult["value"] * -1;

        if ($ticketResult["shippingfree"]) {
            $shippingfree = "1";
        } else {
            $shippingfree = "0";
        }

        $sql = "
        INSERT INTO s_order_basket (sessionID, articlename, articleID, ordernumber, shippingfree, quantity, price, netprice,tax_rate, datum, modus, currencyFactor)
        VALUES (?,?,?,?,?,1,?,?,?,?,2,?)
        ";
        $params = array (
            $this->sSYSTEM->sSESSION_ID,
            $vouchername,
            $ticketResult["id"],
            $ticketResult["ordercode"],
            $shippingfree,
            $ticketResult["value"],
            $tax,
            $taxRate,
            $timeInsert,
            $this->sSYSTEM->sCurrency["factor"]
        );
        $sql = Enlight()->Events()->filter('Shopware_Modules_Basket_AddVoucher_FilterSql',$sql, array('subject'=>$this,"params"=>$params,"voucher"=>$ticketResult,"name"=>$vouchername,"shippingfree"=>$shippingfree,"tax"=>$tax));


        if (!$this->sSYSTEM->sDB_CONNECTION->Execute($sql,$params)) {
            return false;
        }

        return true;
    }

    /**
     * Get total weight of products in cart
     * @access public
     * @deprecated
     * @return double - weight in kilogram
     */
    public function sGetBasketWeight()
    {
        $sql = '
            SELECT SUM(d.weight*b.quantity) as weight
            FROM s_order_basket b

            LEFT JOIN s_articles a
            ON b.articleID=a.id
            AND b.modus=0
            AND b.esdarticle=0

            LEFT JOIN s_articles_details d
            ON (d.ordernumber=b.ordernumber)
            AND d.articleID=a.id

            WHERE b.sessionID=?
        ';
        $weight = $this->sSYSTEM->sDB_CONNECTION->GetOne($sql, array($this->sSYSTEM->sSESSION_ID));
        return $weight;
    }


    /**
     * Get articleId of all products from cart
     * @access public
     * @deprecated
     * @return array
     */
    public function sGetBasketIds()
    {
        $getArticles = $this->sSYSTEM->sDB_CONNECTION->GetAll("SELECT DISTINCT articleID FROM s_order_basket WHERE sessionID=?
        AND modus=0
        ORDER BY modus ASC, datum DESC",array($this->sSYSTEM->sSESSION_ID));

        foreach ($getArticles as $article) {
            $articles[] = $article["articleID"];
        }

        return $articles;
    }

    /**
     * Check if minimum charging is reached
     * @access public
     * @deprecated
     * @return void
     */

    public function sCheckMinimumCharge()
    {
        if ($this->sSYSTEM->sUSERGROUPDATA["minimumorder"] && !$this->sSYSTEM->sUSERGROUPDATA["minimumordersurcharge"]) {
            $amount = $this->sGetAmount();
            if ($amount["totalAmount"]<($this->sSYSTEM->sUSERGROUPDATA["minimumorder"]*$this->sSYSTEM->sCurrency["factor"])) {
                return ($this->sSYSTEM->sUSERGROUPDATA["minimumorder"]*$this->sSYSTEM->sCurrency["factor"]);
            } else {
                return false;
            }
        }
        return false;
    }

    /**
     * Add surcharge for payment-means to cart
     * @access public
     * @deprecated
     * @return array
     */
    public function sInsertSurcharge()
    {
        $name = isset($this->sSYSTEM->sCONFIG['sSURCHARGENUMBER']) ? $this->sSYSTEM->sCONFIG['sSURCHARGENUMBER']: "SURCHARGE";

        // Delete previous inserted discounts
        $this->sSYSTEM->sDB_CONNECTION->Execute("
        DELETE FROM s_order_basket WHERE sessionID=? AND ordernumber=?
        ",array($this->sSYSTEM->sSESSION_ID,$name));

        if (!$this->sCountArticles()) return false;

        if ($this->sSYSTEM->sUSERGROUPDATA["minimumorder"] && $this->sSYSTEM->sUSERGROUPDATA["minimumordersurcharge"]) {

            $amount = $this->sGetAmount();

            if ($amount["totalAmount"]<$this->sSYSTEM->sUSERGROUPDATA["minimumorder"]) {

                if (!empty($this->sSYSTEM->sCONFIG["sTAXAUTOMODE"])) {
                    $tax = $this->sSYSTEM->sMODULES['sBASKET']->getMaxTax();
                } else {
                    $tax = $this->sSYSTEM->sCONFIG['sDISCOUNTTAX'];
                }

                if (empty($tax)) $tax = 19;

                if ((!$this->sSYSTEM->sUSERGROUPDATA["tax"] && $this->sSYSTEM->sUSERGROUPDATA["id"])) {
                    $discountNet = $this->sSYSTEM->sUSERGROUPDATA["minimumordersurcharge"];
                } else {
                    $discountNet = round($this->sSYSTEM->sUSERGROUPDATA["minimumordersurcharge"] / (100+$tax) * 100,3);
                }

                if ($this->sSYSTEM->sCurrency["factor"]) {
                    $factor = $this->sSYSTEM->sCurrency["factor"];
                    $discountNet /= $factor;
                } else {
                    $factor = 1;
                }

                $surcharge = $this->sSYSTEM->sUSERGROUPDATA["minimumordersurcharge"]/$factor;

                $params = array(
                    $this->sSYSTEM->sSESSION_ID,
                    $this->sSYSTEM->sCONFIG["sSURCHARGENAME"],
                    $name,
                    $surcharge,
                    $discountNet,
                    $tax,
                    $this->sSYSTEM->sCurrency["factor"]
                );

                $this->sSYSTEM->sDB_CONNECTION->Execute("
                INSERT INTO s_order_basket (sessionID, articlename, articleID, ordernumber, quantity,price,netprice,tax_rate,datum,modus,currencyFactor)
                VALUES (
                ?,
                ?,
                0,
                ?,
                1,
                ?,
                ?,
                ?,
                now(),
                4,
                ?
                )
                ",$params);

            }
        }
    }

    /**
     * Add percentual surcharge
     * @access public
     * @deprecated
     * @return void
     */
    public function sInsertSurchargePercent()
    {
        if (!$this->sSYSTEM->_SESSION["sUserId"]) {
            if (!$this->sSYSTEM->_SESSION["sPaymentID"]) {
                return false;
            } else {
                $paymentInfo = $this->sSYSTEM->sDB_CONNECTION->GetRow("
                SELECT debit_percent FROM s_core_paymentmeans WHERE id=?",array($this->sSYSTEM->_SESSION["sPaymentID"]));
            }
        } else {
            $userData =  $this->sSYSTEM->sDB_CONNECTION->GetRow("SELECT paymentID FROM s_user WHERE id=?",array(intval($this->sSYSTEM->_SESSION["sUserId"])));
            $paymentInfo = $this->sSYSTEM->sDB_CONNECTION->GetRow("
            SELECT debit_percent FROM s_core_paymentmeans WHERE id=?",array($userData["paymentID"]));

        }

        $name = isset($this->sSYSTEM->sCONFIG['sPAYMENTSURCHARGENUMBER']) ? $this->sSYSTEM->sCONFIG['sPAYMENTSURCHARGENUMBER']: "PAYMENTSURCHARGE";
        // Depends on payment-mean
        $percent = $paymentInfo["debit_percent"];

        $rs = $this->sSYSTEM->sDB_CONNECTION->Execute("
        DELETE FROM s_order_basket WHERE sessionID=? AND ordernumber=?
        ",array($this->sSYSTEM->sSESSION_ID,$name));

        if (!$this->sCountArticles()) return false;

        if (!empty($percent)) {

            $amount = $this->sGetAmount();

            if ($percent>=0) {
                $surchargename = $this->sSYSTEM->sCONFIG["sPAYMENTSURCHARGEADD"];
            } else {
                $surchargename = $this->sSYSTEM->sCONFIG["sPAYMENTSURCHARGEDEV"];
            }
            //print_r($amount); exit;
            $surcharge = $amount["totalAmount"] / 100 * $percent;
            //echo $amount["totalAmount"]." - ".$surcharge." <br />";
            if (!empty($this->sSYSTEM->sCONFIG["sTAXAUTOMODE"])) {
                $tax = $this->getMaxTax();
            } else {
                $tax = $this->sSYSTEM->sCONFIG['sDISCOUNTTAX'];
            }

            if (!$tax) $tax = 119;

            if ((!$this->sSYSTEM->sUSERGROUPDATA["tax"] && $this->sSYSTEM->sUSERGROUPDATA["id"])) {
                $discountNet = $surcharge;
            } else {
                $discountNet = round($surcharge / (100+$tax) * 100,3);
            }

            if ($this->sSYSTEM->sCurrency["factor"]) {
                $factor = $this->sSYSTEM->sCurrency["factor"];
                /*$discountNet /= $factor;
                $surcharge /= $factor;*/
            } else {
                $factor = 1;
            }


            $params = array(
                $this->sSYSTEM->sSESSION_ID,
                $surchargename,
                $name,
                $surcharge,
                $discountNet,
                $tax,
                $this->sSYSTEM->sCurrency["factor"]

            );

            $this->sSYSTEM->sDB_CONNECTION->Execute("
                INSERT INTO s_order_basket (sessionID, articlename, articleID, ordernumber, quantity,price,netprice,tax_rate, datum,modus,currencyFactor)
                VALUES (?,
                ?,
                0,
                ?,
                1,
                ?,
                ?,
                ?,
                now(),
                4,
                ?
                )
                ",$params);


        }
    }

    /**
     * Fetch count of products in basket
     * @access public
     * @deprecated
     * @return array
     */
    public function sCountBasket()
    {
        $getArticles = $this->sSYSTEM->sDB_CONNECTION->GetAll("SELECT id FROM s_order_basket WHERE sessionID=? AND modus=0
        ",array($this->sSYSTEM->sSESSION_ID));
        return count($getArticles);
    }

    /**
     * Get all basket positions
     * @access public
     * @deprecated
     * @return array
     */
    public function sGetBasket()
    {
        // Init variables
        $discount = 0; $totalAmount = 0; $totalAmountWithTax = 0; $totalAmountNet = 0; $totalCount = 0;

        // Refresh basket-prices
        $basketData = $this->sSYSTEM->sDB_CONNECTION->GetAll("
        SELECT id,modus, quantity FROM s_order_basket
        WHERE sessionID=?",array($this->sSYSTEM->sSESSION_ID));
        foreach ($basketData as $basketContent) {
            if (empty($basketContent["modus"])) {
                $this->sUpdateArticle ($basketContent["id"],$basketContent["quantity"]);
            }
        }


        // Check, if we have some free products for the client
        $this->sInsertPremium();

        // Delete previous given discounts
        if (empty($this->sSYSTEM->sCONFIG['sPREMIUMSHIPPIUNG'])) {
            $rs = $this->sSYSTEM->sDB_CONNECTION->Execute("
            DELETE FROM s_order_basket WHERE sessionID=? AND modus=3
            ",array($this->sSYSTEM->sSESSION_ID));
        }
        // Check for surcharges
        $this->sInsertSurcharge();
        // Check for skonto / percent surcharges
        $this->sInsertSurchargePercent();

        // Calculate global basket discount
        $this->sInsertDiscount();

        $sql = "
        SELECT s_order_basket.*, ad.packunit, ad.minpurchase,taxID,ad.instock AS `instock`,
                suppliernumber,
                ad.maxpurchase,
                ad.purchasesteps,
                ad.purchaseunit,
                ad.unitID,
                laststock,
                ad.shippingtime,
                ad.releasedate,
                ad.releasedate AS sReleaseDate,stockmin, su.description AS itemUnit,
               s_order_basket_attributes.attribute1 as ob_attr1,
               s_order_basket_attributes.attribute2 as ob_attr2,
               s_order_basket_attributes.attribute3 as ob_attr3,
               s_order_basket_attributes.attribute4 as ob_attr4,
               s_order_basket_attributes.attribute5 as ob_attr5,
               s_order_basket_attributes.attribute6 as ob_attr6
        FROM s_order_basket
        LEFT JOIN s_articles_details AS ad ON ad.ordernumber = s_order_basket.ordernumber
        LEFT JOIN s_articles a ON (a.id = ad.articleID)
        LEFT JOIN s_core_units su ON su.id = ad.unitID
        LEFT JOIN s_order_basket_attributes ON s_order_basket.id = s_order_basket_attributes.basketID
        WHERE sessionID=?
        ORDER BY id ASC, datum DESC
        ";
        $sql = Enlight()->Events()->filter('Shopware_Modules_Basket_GetBasket_FilterSQL', $sql, array('subject'=>$this));


        $getArticles = $this->sSYSTEM->sDB_CONNECTION->GetAll($sql,array($this->sSYSTEM->sSESSION_ID));
        $countItems = count($getArticles);

        if (!empty($countItems)) {
            // Reformating data, add additional datafields to array
            foreach ($getArticles as $key => $value) {
                $getArticles[$key] = Enlight()->Events()->filter('Shopware_Modules_Basket_GetBasket_FilterItemStart', $getArticles[$key], array('subject'=>$this,'getArticles'=>$getArticles));

                if (empty($getArticles[$key]["modus"])) {
                    $getArticles[$key]["shippinginfo"] = true;
                } else {
                    $getArticles[$key]["shippinginfo"] = false;
                }
                if (!empty($getArticles[$key]["releasedate"]) && strtotime($getArticles[$key]["releasedate"]) <= time()) {
                    $getArticles[$key]["sReleaseDate"] = $getArticles[$key]["releasedate"] = "";
                }
                $getArticles[$key]["esd"] = $getArticles[$key]["esdarticle"];
                if (empty($getArticles[$key]["minpurchase"])) $getArticles[$key]["minpurchase"] = 1;
                if (empty($getArticles[$key]["purchasesteps"])) $getArticles[$key]["purchasesteps"] = 1;
                if ($getArticles[$key]["purchasesteps"]<=0) unset($getArticles[$key]["purchasesteps"]);

                if (empty($getArticles[$key]["maxpurchase"])) {
                    $getArticles[$key]["maxpurchase"] = $this->sSYSTEM->sCONFIG['sMAXPURCHASE'];
                }
                if(!empty($getArticles[$key]["laststock"])
                && $getArticles[$key]["instock"] < $getArticles[$key]["maxpurchase"]) {
                    $getArticles[$key]["maxpurchase"] = $getArticles[$key]["instock"];
                }

                // Get additional basket meta data for each product
                if ($getArticles[$key]["modus"] == 0) {
                    /**
                     {$sBasketItem.additional_details.properties} --> Eigenschaften/Filter
                     {$sBasketItem.additional_details.description} --> Artikel Kurzbeschreibung
                     {$sBasketItem.additional_details.description_long} --> Artikel Langbeschreibung
                     {$sBasketItem.additional_details.attrX} --> Artikel Attribut (X ist zu ersetzen durch das gewünschte Attribut 1-20)
                     {$sBasketItem.additional_details.purchaseunit} --> Inhalt (Grundpreisberechnung)
                     {$sBasketItem.additional_details.sUnit.unit} --> Einheit (Grundpreisberechnung)
                     {$sBasketItem.additional_details.referenceprice|currency} --> Grundpreis
                     * SELECT a.description, a.description_long, at.*, ad.purchaseunit,
                     */
                    $tempArticle = Shopware()->Modules()->Articles()->sGetProductByOrdernumber($value['ordernumber']);

                    if (empty($tempArticle)) {
                       $getArticles[$key]["additional_details"] = array("properties"=>array());
                    } else {
                       $getArticles[$key]['additional_details'] = $tempArticle;
                       $properties = '';
                       foreach ($getArticles[$key]['additional_details']['sProperties'] as $property) {
                           $properties .= $property['name'] . ':&nbsp;' . $property['value'] . ',&nbsp;';
                       }
                       $getArticles[$key]['additional_details']['properties'] = substr($properties, 0, -7);
                    }
                }

                // If unitID is set, query it
                if (!empty($getArticles[$key]["unitID"])) {
                    $getUnitData = $this->sSYSTEM->sMODULES['sArticles']->sGetUnit($getArticles[$key]["unitID"]);
                    $getArticles[$key]["itemUnit"] = $getUnitData["description"];
                } else {
                    unset($getArticles[$key]["unitID"]);
                }

                if (!empty($getArticles[$key]["packunit"])) {
                    $getPackUnit = $this->sSYSTEM->sMODULES['sArticles']->sGetTranslation(array(),$getArticles[$key]["articleID"],"article",$this->sSYSTEM->sLanguage);
                    if (!empty($getPackUnit["packunit"])) {
                        $getArticles[$key]["packunit"] = $getPackUnit["packunit"];
                    }
                }

                $quantity = $getArticles[$key]["quantity"];
                $price = $getArticles[$key]["price"];
                $netprice = $getArticles[$key]["netprice"];

                if ($value["modus"]==2) {
                    $sql = "
                        SELECT vouchercode,taxconfig FROM s_emarketing_vouchers WHERE ordercode='{$getArticles[$key]["ordernumber"]}'
                        ";

                    $ticketResult = $this->sSYSTEM->sDB_CONNECTION->GetRow($sql);

                    if (!$ticketResult["vouchercode"]) {
                        // Query Voucher-Code
                        $queryVoucher = $this->sSYSTEM->sDB_CONNECTION->GetRow("
                            SELECT code FROM s_emarketing_voucher_codes WHERE id = {$getArticles[$key]["articleID"]}
                            AND cashed!=1
                            ");
                        $ticketResult["vouchercode"] = $queryVoucher["code"];
                    }
                    $this->sDeleteArticle($getArticles[$key]["id"]);

                    //if voucher was deleted, do not restore
                    if ($this->sSYSTEM->_GET['sDelete'] != 'voucher') {
                        $this->sAddVoucher($ticketResult["vouchercode"]);

                    }
                }

                $tax = $value["tax_rate"];

                // If shop is in net mode, we have to consider
                // the tax separately
                if (($this->sSYSTEM->sCONFIG['sARTICLESOUTPUTNETTO'] && !$this->sSYSTEM->sUSERGROUPDATA["tax"]) || (!$this->sSYSTEM->sUSERGROUPDATA["tax"] && $this->sSYSTEM->sUSERGROUPDATA["id"])) {
                    if (empty($value["modus"])) {

                        $priceWithTax = round($netprice,2) / 100 * (100+$tax);

                        $getArticles[$key]["amountWithTax"] = $quantity * $priceWithTax;
                        // If basket comprised any discount, calculate brutto-value for the discount
                        if ($this->sSYSTEM->sUSERGROUPDATA["basketdiscount"] && $this->sCheckForDiscount()) {
                            $discount += ($getArticles[$key]["amountWithTax"]/100*$this->sSYSTEM->sUSERGROUPDATA["basketdiscount"]);
                        }

                    } elseif ($value["modus"]==3) {
                        $getArticles[$key]["amountWithTax"] = round(1 * (round($price,2) / 100 * (100+$tax)),2);
                        // Basket discount
                    } elseif ($value["modus"]==2) {
                        $getArticles[$key]["amountWithTax"] = round(1 * (round($price,2) / 100 * (100+$tax)),2);

                        if ($this->sSYSTEM->sUSERGROUPDATA["basketdiscount"] && $this->sCheckForDiscount()) {
                            $discount += ($getArticles[$key]["amountWithTax"]/100*($this->sSYSTEM->sUSERGROUPDATA["basketdiscount"]));
                        }
                    } elseif ($value["modus"]==4 || $value["modus"]==10) {
                        $getArticles[$key]["amountWithTax"] = round(1 * ($price / 100 * (100+$tax)),2);
                        if ($this->sSYSTEM->sUSERGROUPDATA["basketdiscount"] && $this->sCheckForDiscount()) {
                            $discount += ($getArticles[$key]["amountWithTax"]/100*$this->sSYSTEM->sUSERGROUPDATA["basketdiscount"]);
                        }
                    }
                }

                $getArticles[$key]["amount"] = $quantity * round($price,2);

                //+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++

                //reset purchaseunit and save the original value in purchaseunitTemp
                if ($getArticles[$key]["purchaseunit"]>0) {
                    $getArticles[$key]["purchaseunitTemp"] = $getArticles[$key]["purchaseunit"];
                    $getArticles[$key]["purchaseunit"] = 1;
                }

                //+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++


                // If price per unit is not referering to 1, calculate base-price
                // Choose 1000, quantity refers to 500, calculate price / 1000 * 500 as reference
                if ($getArticles[$key]["purchaseunit"]>0) {
                    $getArticles[$key]["itemInfo"] = $getArticles[$key]["purchaseunit"]." {$getUnitData["description"]} � ".$this->sSYSTEM->sMODULES['sArticles']->sFormatPrice($getArticles[$key]["amount"] / $quantity * $getArticles[$key]["purchaseunit"]);
                    $getArticles[$key]["itemInfoArray"]["reference"] = $getArticles[$key]["purchaseunit"];
                    $getArticles[$key]["itemInfoArray"]["unit"] = $getUnitData;
                    $getArticles[$key]["itemInfoArray"]["price"] = $this->sSYSTEM->sMODULES['sArticles']->sFormatPrice($getArticles[$key]["amount"] / $quantity * $getArticles[$key]["purchaseunit"]);
                }


                if ($value["modus"]==2) {
                    // Gutscheine
                    if (!$this->sSYSTEM->sUSERGROUPDATA["tax"] && $this->sSYSTEM->sUSERGROUPDATA["id"]) {
                        $getArticles[$key]["amountnet"] = $quantity * round($price,2);
                    } else {

                        $getArticles[$key]["amountnet"] = $quantity * round($netprice,2);
                    }

                } else {
                    if (!$this->sSYSTEM->sUSERGROUPDATA["tax"] && $this->sSYSTEM->sUSERGROUPDATA["id"]) {
                        $getArticles[$key]["amountnet"] = $quantity * round($netprice,2);
                    } else {
                        $getArticles[$key]["amountnet"] = $quantity * $netprice;
                    }
                }

                $totalAmount += round($getArticles[$key]["amount"],2);
                // Needed if shop is in net-mode
                $totalAmountWithTax += round($getArticles[$key]["amountWithTax"],2);
                // Ignore vouchers and premiums by counting articles
                if (!$getArticles[$key]["modus"]) {
                    $totalCount++;
                }

                $totalAmountNet += $getArticles[$key]["amountnet"];

                $getArticles[$key]["priceNumeric"] = $getArticles[$key]["price"];
                $getArticles[$key]["price"] = $this->sSYSTEM->sMODULES['sArticles']->sFormatPrice($getArticles[$key]["price"]);
                $getArticles[$key]["amount"] =  $this->sSYSTEM->sMODULES['sArticles']->sFormatPrice($getArticles[$key]["amount"]);
                $getArticles[$key]["amountnet"] = $this->sSYSTEM->sMODULES['sArticles']->sFormatPrice($getArticles[$key]["amountnet"]);

                //+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++

                if (!empty($getArticles[$key]["purchaseunitTemp"])) {
                    $getArticles[$key]["purchaseunit"] = $getArticles[$key]["purchaseunitTemp"];
                    $getArticles[$key]["itemInfo"] = $getArticles[$key]["purchaseunit"]." {$getUnitData["description"]} � ".$this->sSYSTEM->sMODULES['sArticles']->sFormatPrice(str_replace(",",".",$getArticles[$key]["amount"]) / $quantity);
                }

                //+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++


                if (empty($value["modus"])) {
                    // Article-Image
                    if (!empty($getArticles[$key]["ob_attr1"])) {

                        $getArticles[$key]["image"] = $this->sSYSTEM->sMODULES['sArticles']->sGetConfiguratorImage($this->sSYSTEM->sMODULES['sArticles']->sGetArticlePictures($getArticles[$key]["articleID"],false,$this->sSYSTEM->sCONFIG['sTHUMBBASKET'],false,true),$getArticles[$key]["ob_attr1"]);
                    } else {
                        $getArticles[$key]["image"] = $this->sSYSTEM->sMODULES['sArticles']->sGetArticlePictures($getArticles[$key]["articleID"],true,$this->sSYSTEM->sCONFIG['sTHUMBBASKET'],$getArticles[$key]["ordernumber"]);
                    }
                }
                // Links to details, basket
                $getArticles[$key]["linkDetails"] = $this->sSYSTEM->sCONFIG['sBASEFILE']."?sViewport=detail&sArticle=".$getArticles[$key]["articleID"];
                if ($value["modus"]==2) {
                    $getArticles[$key]["linkDelete"] = $this->sSYSTEM->sCONFIG['sBASEFILE']."?sViewport=basket&sDelete=voucher";
                } else {
                    $getArticles[$key]["linkDelete"] = $this->sSYSTEM->sCONFIG['sBASEFILE']."?sViewport=basket&sDelete=".$getArticles[$key]["id"];
                }

                $getArticles[$key]["linkNote"] = $this->sSYSTEM->sCONFIG['sBASEFILE']."?sViewport=note&sAdd=".$getArticles[$key]["ordernumber"];

                $getArticles[$key] = Enlight()->Events()->filter('Shopware_Modules_Basket_GetBasket_FilterItemEnd', $getArticles[$key], array('subject'=>$this,'getArticles'=>$getArticles));
            }

            if ($totalAmount < 0 || empty($totalCount)) {
                /*
                $deleteBasket = $this->sSYSTEM->sDB_CONNECTION->Execute("
                DELETE FROM s_order_basket WHERE sessionID='".$this->sSYSTEM->sSESSION_ID."'
                ");
                */
                return array();
            }

            // Total-Amount brutto (or netto if shop-mode is to show net-prices)
            $totalAmountNumeric = $totalAmount;
            $totalAmount = $this->sSYSTEM->sMODULES['sArticles']->sFormatPrice($totalAmount);

            // Total-Amount brutto (in any case)
            $totalAmountWithTaxNumeric = $totalAmountWithTax;
            $totalAmountWithTax = $this->sSYSTEM->sMODULES['sArticles']->sFormatPrice($totalAmountWithTax);

            // Total-Amount netto
            $totalAmountNetNumeric = $totalAmountNet;

            $totalAmountNet = $this->sSYSTEM->sMODULES['sArticles']->sFormatPrice($totalAmountNet);

            $result = array("content"=>$getArticles,"Amount"=>$totalAmount,"AmountNet"=>$totalAmountNet,"Quantity"=>$totalCount,"AmountNumeric"=>$totalAmountNumeric,"AmountNetNumeric"=>$totalAmountNetNumeric,"AmountWithTax"=>$totalAmountWithTax,"AmountWithTaxNumeric"=>$totalAmountWithTaxNumeric);


            if (!empty($this->sSYSTEM->_SESSION["sLastArticle"])) {	// r302, sth
                $result["sLastActiveArticle"] = array("id"=>$this->sSYSTEM->_SESSION["sLastArticle"],"link"=> $this->sSYSTEM->sCONFIG['sBASEFILE']."?sViewport=detail&sDetails=".$this->sSYSTEM->_SESSION["sLastArticle"]);
            }

            if (!empty($result["content"])) {	// r302, sth

                foreach ($result["content"] as $key => $value) {
                    if (!empty($value['amountWithTax'])) {
                        $t = round(str_replace(",",".",$value['amountWithTax']),2);
                    } else {
                        $t = str_replace(",",".",$value["price"]);
                        $t = floatval(round($t*$value["quantity"],2));
                    }
                    if (!$this->sSYSTEM->sUSERGROUPDATA["tax"] && $this->sSYSTEM->sUSERGROUPDATA["id"]) {
                        $p = floatval($this->sSYSTEM->sMODULES['sArticles']->sRound($this->sSYSTEM->sMODULES['sArticles']->sRound(round($value["netprice"],2)*$value["quantity"])));
                    } else {
                        $p = floatval($this->sSYSTEM->sMODULES['sArticles']->sRound($this->sSYSTEM->sMODULES['sArticles']->sRound($value["netprice"]*$value["quantity"])));
                    }
                    $calcDifference = $this->sSYSTEM->sMODULES['sArticles']->sFormatPrice($t - $p);
                    $result["content"][$key]["tax"] = $calcDifference;
                }
            }
            $result = Enlight()->Events()->filter('Shopware_Modules_Basket_GetBasket_FilterResult', $result, array('subject'=>$this));

            return $result;
        } else {
            return array();
        }
    }

    /**
     * Add product to bookmarks
     * @param int $articleID
     * @param string $articleName
     * @param string $articleOrdernumber
     * @access public
     * @deprecated
     * @return void
     */
    public function sAddNote($articleID, $articleName, $articleOrdernumber)
    {
        $datum = date("Y-m-d H:i:s");

        if (!empty($this->sSYSTEM->_COOKIE)&&empty($this->sSYSTEM->_COOKIE["sUniqueID"])) {
            $this->sSYSTEM->_COOKIE["sUniqueID"] = md5(uniqid(rand()));
            setcookie("sUniqueID", $this->sSYSTEM->_COOKIE["sUniqueID"], Time()+(86400*360), '/');
        }

        // Check if this article is already noted
        $checkForArticle = $this->sSYSTEM->sDB_CONNECTION->GetRow("
        SELECT id FROM s_order_notes WHERE sUniqueID=? AND ordernumber=?
        ",array($this->sSYSTEM->_COOKIE["sUniqueID"],$articleOrdernumber));

        if (!$checkForArticle["id"]) {
            $queryNewPrice = $this->sSYSTEM->sDB_CONNECTION->Execute("
            INSERT INTO s_order_notes (sUniqueID, userID,articlename, articleID, ordernumber, datum)
            VALUES (?,?,?,?,?,?)
            ",array(empty($this->sSYSTEM->_COOKIE["sUniqueID"]) ? $this->sSYSTEM->sSESSION_ID : $this->sSYSTEM->_COOKIE["sUniqueID"],$this->sSYSTEM->_SESSION['sUserId'] ?$this->sSYSTEM->_SESSION['sUserId'] : "0" ,$articleName,$articleID,$articleOrdernumber,$datum));

            if (!$queryNewPrice) {
                $this->sSYSTEM->E_CORE_WARNING ("sBasket##sAddNote##01","Error in SQL-query");
                return false;
            }
        }
        return true;
    }

    /**
     * Get all products current on bookmark
     * @deprecated
     * @return array
     */
    public function sGetNotes()
    {
        $getArticles = $this->sSYSTEM->sDB_CONNECTION->GetAll('
            SELECT n.* FROM s_order_notes n, s_articles a
            WHERE (sUniqueID=? OR (userID!=0 AND userID=?))
            AND a.id = n.articleID AND a.active = 1
            ORDER BY n.datum DESC
        ', array(
            empty($this->sSYSTEM->_COOKIE['sUniqueID']) ? $this->sSYSTEM->sSESSION_ID : $this->sSYSTEM->_COOKIE['sUniqueID'],
            isset($this->sSYSTEM->_SESSION['sUserId']) ? $this->sSYSTEM->_SESSION['sUserId'] : 0
        ));

        if (empty($getArticles)) {
            return $getArticles;
        }

        // Reformating data, add additional datafields to array
        foreach ($getArticles as $key => $value) {
            // Article-Image
            $getArticles[$key] = $this->sSYSTEM->sMODULES['sArticles']->sGetPromotionById("fix", 0, (int) $value["articleID"]);
            if (empty($getArticles[$key])) {
                $this->sDeleteNote($value["id"]);
                unset($getArticles[$key]);
                continue;
            }
            $getArticles[$key]["articlename"] = $getArticles[$key]["articleName"];
            $getArticles[$key]["image"] = $this->sSYSTEM->sMODULES['sArticles']->getArticleListingCover($value["articleID"], Shopware()->Config()->get('forceArticleMainImageInListing'));
            // Links to details, basket
            $getArticles[$key]["id"] = $value["id"];
            $getArticles[$key]["linkBasket"] = $this->sSYSTEM->sCONFIG['sBASEFILE']."?sViewport=basket&sAdd=".$value["ordernumber"];
            $getArticles[$key]["linkDelete"] = $this->sSYSTEM->sCONFIG['sBASEFILE']."?sViewport=note&sDelete=".$value["id"];
            $getArticles[$key]["datum_add"] = $value["datum"];
        }
        return $getArticles;
    }

    /**
     * Returns the number of notepad entries
     * @deprecated
     * @return int
     */
    public function sCountNotes()
    {
        $count = (int) $this->sSYSTEM->sDB_CONNECTION->GetOne('
            SELECT COUNT(*) FROM s_order_notes n, s_articles a
            WHERE (sUniqueID=? OR (userID!=0 AND userID = ?))
            AND a.id = n.articleID AND a.active = 1
        ', array(
            empty($this->sSYSTEM->_COOKIE['sUniqueID']) ? $this->sSYSTEM->sSESSION_ID : $this->sSYSTEM->_COOKIE['sUniqueID'],
            isset($this->sSYSTEM->_SESSION['sUserId']) ? $this->sSYSTEM->_SESSION['sUserId'] : 0
        ));
        return $count;
    }



    /**
     * Update quantity / price of a certain cart position
     * @param int $id - s_order_basket.id
     * @param int $quantity - Neue Menge
     * @access public
     * @deprecated
     * @return boolean
     */
    public function sUpdateArticle($id,$quantity)
    {
        // Int values should be int values ;)
        $quantity = intval($quantity);
        $id = intval($id);

        if (Enlight()->Events()->notifyUntil('Shopware_Modules_Basket_UpdateArticle_Start', array('subject'=>$this,'id'=>$id,"quantity"=>$quantity))) {
            return false;
        }

        if (empty($id)) return false;

        // Query to get minimum surcharge
        $queryAdditionalInfo = $this->sSYSTEM->sDB_CONNECTION->GetRow("
        SELECT s_articles_details.minpurchase, s_articles_details.purchasesteps, s_articles_details.maxpurchase, s_articles_details.purchaseunit, pricegroupID,pricegroupActive, s_order_basket.ordernumber, s_order_basket.articleID
        FROM s_articles, s_order_basket, s_articles_details

        WHERE s_order_basket.articleID = s_articles.id
        AND s_order_basket.ordernumber = s_articles_details.ordernumber
        AND s_order_basket.id=?
        AND
        s_order_basket.sessionID=?
        ",array($id,$this->sSYSTEM->sSESSION_ID));

        // Check if quantity matches minimum-purchase
        if (!$queryAdditionalInfo["minpurchase"]) {
            $queryAdditionalInfo["minpurchase"] = 1;
        }

        if ($quantity<$queryAdditionalInfo["minpurchase"]) {
            $quantity = $queryAdditionalInfo["minpurchase"];
        }

        // Check if quantity matches the step-requirements
        if (!$queryAdditionalInfo["purchasesteps"]) {
            $queryAdditionalInfo["purchasesteps"] = 1;
        }

        if (($quantity/$queryAdditionalInfo["purchasesteps"])!=intval($quantity / $queryAdditionalInfo["purchasesteps"])) {
            $quantity = intval($quantity / $queryAdditionalInfo["purchasesteps"])*$queryAdditionalInfo["purchasesteps"];
        }

        if (empty($queryAdditionalInfo["maxpurchase"]) && !empty($this->sSYSTEM->sCONFIG['sMAXPURCHASE'])) {
            $queryAdditionalInfo["maxpurchase"] = $this->sSYSTEM->sCONFIG['sMAXPURCHASE'];
        }

        // Check if quantity matches max-purchase
        if ($quantity>$queryAdditionalInfo["maxpurchase"] && !empty($queryAdditionalInfo["maxpurchase"])) {
            $quantity = $queryAdditionalInfo["maxpurchase"];
        }


        if (!$this->sSYSTEM->sSESSION_ID || !$id) {
            return false;
        }

        /*
        SW 2.1 Pricegroups
        */
        if ($queryAdditionalInfo["pricegroupActive"]) {
            $quantitySQL = "AND s_articles_prices.from = 1 LIMIT 1";
        } else {
            $quantitySQL = "AND s_articles_prices.from <= $quantity AND (s_articles_prices.to >= $quantity OR s_articles_prices.to=0)";
        }

        // Get the ordernumber
        $sql = "SELECT s_articles_prices.price AS price,taxID,s_core_tax.tax AS tax,tax_rate,s_articles_details.id AS articleDetailsID, s_articles_details.articleID, s_order_basket.config, s_order_basket.ordernumber FROM s_articles_details, s_articles_prices, s_order_basket,
        s_articles, s_core_tax
        WHERE s_order_basket.id=$id AND s_order_basket.sessionID='".$this->sSYSTEM->sSESSION_ID."'
        AND s_order_basket.ordernumber = s_articles_details.ordernumber
        AND s_articles_details.id=s_articles_prices.articledetailsID
        AND s_articles_details.articleID = s_articles.id
        AND s_articles.taxID = s_core_tax.id
        AND s_articles_prices.pricegroup='".$this->sSYSTEM->sUSERGROUP."'
        $quantitySQL
        ";

        //+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++

        if (!empty($queryAdditionalInfo["purchaseunit"])) {
            $queryAdditionalInfo["purchaseunit"] = 1;
        }

        //+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++


        $queryNewPrice = $this->sSYSTEM->sDB_CONNECTION->GetRow($sql);

        /*
        SW 2.1 - Load prices from defaultgroup if no own prices defined
        */
        if (!$queryNewPrice["price"]) {
            // In the case no price is available for this customergroup, use price of default customergroup
            $sql = "SELECT s_articles_prices.price AS price,taxID,s_core_tax.tax AS tax,s_articles_details.id AS articleDetailsID, s_articles_details.articleID, s_order_basket.config, s_order_basket.ordernumber FROM s_articles_details, s_articles_prices, s_order_basket,
            s_articles, s_core_tax
            WHERE s_order_basket.id=$id AND s_order_basket.sessionID='".$this->sSYSTEM->sSESSION_ID."'
            AND s_order_basket.ordernumber = s_articles_details.ordernumber
            AND s_articles_details.id=s_articles_prices.articledetailsID
            AND s_articles_details.articleID = s_articles.id
            AND s_articles.taxID = s_core_tax.id
            AND s_articles_prices.pricegroup='EK'
            $quantitySQL
            ";
            $queryNewPrice = $this->sSYSTEM->sDB_CONNECTION->GetRow($sql);
        }

        if (empty($queryNewPrice["price"])&&empty($queryNewPrice["config"])) {
            // If no price is set for default customergroup, delete article from basket
            $this->sDeleteArticle($id);
            return false;
        }

        // Determinate tax-rate for this cart position
        $taxRate = $this->sSYSTEM->sMODULES['sArticles']->getTaxRateByConditions($queryNewPrice["taxID"]);

        $netprice = $queryNewPrice["price"];

        /*
        Recalculate Price if purchaseunit is set
        */
        $brutto = $this->sSYSTEM->sMODULES['sArticles']->sCalculatingPriceNum($netprice,$queryNewPrice["tax"],false,false,$queryNewPrice["taxID"],false, $queryNewPrice);

        // Check if tax-free
        if (($this->sSYSTEM->sCONFIG['sARTICLESOUTPUTNETTO'] && !$this->sSYSTEM->sUSERGROUPDATA["tax"]) || (!$this->sSYSTEM->sUSERGROUPDATA["tax"] && $this->sSYSTEM->sUSERGROUPDATA["id"])) {
            // Brutto is equal to net - price
            $netprice = round($brutto,2);

        } else {
            // Round to right value, if no purchase-unit is set
            if ($queryAdditionalInfo["purchaseunit"] == 1) {
                $brutto = round($brutto, 2);
            }
            // Consider global discount for net price
            $netprice = $brutto / (100 + $taxRate) * 100;
        }

        // Recalculate price per item, if purchase-unit is set
        if ($queryAdditionalInfo["purchaseunit"]>0) {
            $brutto = $brutto / $queryAdditionalInfo["purchaseunit"];
            $netprice = $netprice / $queryAdditionalInfo["purchaseunit"];
        }

        if (empty($this->sSYSTEM->sCurrency["factor"])) $this->sSYSTEM->sCurrency["factor"] = 1;

        if ($queryAdditionalInfo["pricegroupActive"]) {

            $brutto = $this->sSYSTEM->sMODULES['sArticles']->sGetPricegroupDiscount($this->sSYSTEM->sUSERGROUP,$queryAdditionalInfo["pricegroupID"],$brutto,$quantity,false);
            $brutto = $this->sSYSTEM->sMODULES['sArticles']->sRound($brutto);
            if (($this->sSYSTEM->sCONFIG['sARTICLESOUTPUTNETTO'] && !$this->sSYSTEM->sUSERGROUPDATA["tax"]) || (!$this->sSYSTEM->sUSERGROUPDATA["tax"] && $this->sSYSTEM->sUSERGROUPDATA["id"])) {
                $netprice = $this->sSYSTEM->sMODULES['sArticles']->sRound($this->sSYSTEM->sMODULES['sArticles']->sGetPricegroupDiscount($this->sSYSTEM->sUSERGROUP,$queryAdditionalInfo["pricegroupID"],$netprice,$quantity,false));

            } else {
                $netprice = $brutto / (100 + $taxRate)* 100;//$this->sSYSTEM->sMODULES['sArticles']->sGetPricegroupDiscount($this->sSYSTEM->sUSERGROUP,$queryAdditionalInfo["pricegroupID"],$netprice,$quantity,false);
                $netprice = number_format($netprice,3,".","");
            }
        }

        $sql = "
            UPDATE s_order_basket SET quantity=?, price=?, netprice=?, currencyFactor=?,
            tax_rate = ?
            WHERE id=? AND sessionID=? AND modus=0
            ";
        $sql = Enlight()->Events()->filter('Shopware_Modules_Basket_UpdateArticle_FilterSqlDefault',$sql, array('subject'=>$this,'id'=>$id,"quantity"=>$quantity,"price"=>$brutto,"netprice"=>$netprice,"currencyFactor"=>$this->sSYSTEM->sCurrency["factor"]));

        if ($taxRate === false) {
            $taxRate = $brutto == $netprice ? 0.00 : $queryNewPrice["tax"];
        }

        $params = array(
            $quantity,
            $brutto,
            $netprice,
            $this->sSYSTEM->sCurrency["factor"],
            $taxRate,
            $id,
            $this->sSYSTEM->sSESSION_ID
        );

        $update = $this->sSYSTEM->sDB_CONNECTION->Execute($sql,$params);

        $this->sUpdateVoucher();

        if (!$update || !$queryNewPrice) {
            $this->sSYSTEM->E_CORE_WARNING("Basket Update ##01","Could not update quantity".$sql);
        }
        return;
    }

    /**
     * Check if any esd article is in cart
     * @access public
     * @deprecated
     * @return void
     */

    public function sCheckForESD()
    {
        $sql = "SELECT id FROM s_order_basket WHERE sessionID=? AND esdarticle=1 LIMIT 1
        ";

        $getArticles = $this->sSYSTEM->sDB_CONNECTION->GetRow($sql,array($this->sSYSTEM->sSESSION_ID));

        if ($getArticles["id"]) {
            return true;
        } else {
            return false;
        }

    }

    /**
     * Truncate cart
     * @access public
     * @deprecated
     * @return void
     */
    public function sDeleteBasket()
    {
        if (empty($this->sSYSTEM->sSESSION_ID)) return false;
        $sql = "
        DELETE FROM s_order_basket WHERE sessionID=?";
        $delete = $this->sSYSTEM->sDB_CONNECTION->Execute($sql,array($this->sSYSTEM->sSESSION_ID));
    }


    /**
     * Delete a certain position from cart
     * @param int $id s_order_basket.id
     * @access public
     * @deprecated
     * @return void
     */
    public function sDeleteArticle($id)
    {
        $id = (int) $id;
        $modus = $this->sSYSTEM->sDB_CONNECTION->GetOne('SELECT `modus` FROM `s_order_basket` WHERE `id`=?', array($id));

        if ($id && $id != "voucher") {
            $sql = "
            DELETE FROM s_order_basket WHERE sessionID=? AND id=?
            ";
            $delete = $this->sSYSTEM->sDB_CONNECTION->Execute($sql,array($this->sSYSTEM->sSESSION_ID,$id));
            if (!$delete) {
                $this->sSYSTEM->E_CORE_WARNING("Basket Delete ##01","Could not delete item ($sql)");
            }
            if (empty($modus)) {
                $this->sUpdateVoucher();
            }
            return;
        } else {
            return;
        }
    }

    /**
     * Delete a certain position from note
     * @param int $id s_order_notes.id
     * @access public
     * @deprecated
     * @return void
     */
    public function sDeleteNote($id)
    {
        $id = (int) $id;

        if (!empty($id)) {
            $sql = "
            DELETE FROM s_order_notes WHERE (sUniqueID=? OR (userID = ?  AND userID != 0)) AND id=?
            ";
            $delete = $this->sSYSTEM->sDB_CONNECTION->Execute($sql,array($this->sSYSTEM->_COOKIE["sUniqueID"],$this->sSYSTEM->_SESSION['sUserId'],$id));
            if (!$delete) {
                $this->sSYSTEM->E_CORE_WARNING("Basket sDeleteNote ##01","Could not delete item ($sql)");
            }
            return true;
        } else {
            return false;
        }
    }
    /**
     * Add product to cart
     * @param int $id ordernumber (s_order_details.ordernumber)
     * @param int $quantity amount
     * @access public
     * @deprecated
     * @return void
     */
    public function sAddArticle($id, $quantity=1)
    {
        if ($this->sSYSTEM->sBotSession) return false;
        if (empty($this->sSYSTEM->sSESSION_ID)) return false;


        $quantity = (empty($quantity)||!is_numeric($quantity)) ? 1 : (int) $quantity;
        if ($quantity<=0) $quantity = 1;


        if (Enlight()->Events()->notifyUntil('Shopware_Modules_Basket_AddArticle_Start', array('subject'=>$this,'id'=>$id,"quantity"=>$quantity))) {
            return false;
        }

        $sql = "
            SELECT s_articles.id AS articleID, name AS articleName, taxID, additionaltext, s_articles_details.shippingfree,laststock,instock, s_articles_details.id as articledetailsID, ordernumber
            FROM s_articles, s_articles_details
            WHERE s_articles_details.ordernumber=?
            AND s_articles_details.articleID=s_articles.id
            AND s_articles.active = 1
            AND (
                SELECT articleID
                FROM s_articles_avoid_customergroups
                WHERE articleID = s_articles.id AND customergroupID = ".$this->sSYSTEM->sUSERGROUPDATA["id"]."
            ) IS NULL
        ";
        $getArticle = $this->sSYSTEM->sDB_CONNECTION->GetRow($sql,array($id));

        $getName = $this->sSYSTEM->sMODULES["sArticles"]->sGetArticleNameByOrderNumber($getArticle["ordernumber"],true);

        if (!empty($getName)) {
            $getArticle["articleName"] = $getName["articleName"];
            $getArticle["additionaltext"] = $getName["additionaltext"];
        }

        if (!count($getArticle)) {

            //$this->sSYSTEM->E_CORE_WARNING ("BASKET-INSERT #00","Article $id not found");
            //unset($this->sSYSTEM->_GET["sAdd"]);
            return false;
        } else {

            // Check if article is already in basket
            $sql = "
            SELECT id, quantity FROM s_order_basket WHERE articleID=? AND sessionID=? AND
            ordernumber=?";

            $chkBasketForArticle = $this->sSYSTEM->sDB_CONNECTION->GetRow($sql,array($getArticle["articleID"],$this->sSYSTEM->sSESSION_ID,$getArticle["ordernumber"]));

            // Shopware 3.5.0 / sth / laststock - instock check
            if (!empty($chkBasketForArticle["id"])) {

                if ($getArticle["laststock"] == true && $getArticle["instock"] < ($chkBasketForArticle["quantity"] + $quantity) ) {
                    $quantity -= $chkBasketForArticle["quantity"];

                }
            } else {
                if ($getArticle["laststock"] == true && $getArticle["instock"] <= $quantity) {
                    $quantity = $getArticle["instock"];
                    if ($quantity <= 0) {
                        return;
                    }
                }
            }
            // --

            $insertTime = date("Y-m-d H:i:s");

            if ($chkBasketForArticle&&empty($sUpPriceValues)) {

                // Article is already in basket, update quantity
                $quantity += $chkBasketForArticle["quantity"];

                $this->sUpdateArticle($chkBasketForArticle["id"],$quantity);
                return $chkBasketForArticle["id"];
            } else {

                // Read price from default-price-table
                $sql = "SELECT price,s_core_tax.tax AS tax FROM s_articles_prices,s_core_tax WHERE
                s_articles_prices.pricegroup=?
                AND s_articles_prices.articledetailsID=?
                AND s_core_tax.id=?
                ";
                $getPrice = $this->sSYSTEM->sDB_CONNECTION->GetRow($sql,array($this->sSYSTEM->sUSERGROUP,$getArticle["articledetailsID"],$getArticle["taxID"]));

                if (empty($getPrice["price"])) {
                    $sql = "SELECT price,s_core_tax.tax AS tax FROM s_articles_prices,s_core_tax WHERE
                    s_articles_prices.pricegroup='EK'
                    AND s_articles_prices.articledetailsID=?
                    AND s_core_tax.id=?
                    ";
                    $getPrice = $this->sSYSTEM->sDB_CONNECTION->GetRow($sql,array($getArticle["articledetailsID"],$getArticle["taxID"]));
                }


                if (!$getPrice["price"] && !$getArticle["free"]) {
                    // No price could acquired
                    $this->sSYSTEM->E_CORE_WARNING ("BASKET-INSERT #01","No price acquired");
                    return;
                } else {

                    // If configuration article
                    if (($this->sSYSTEM->sCONFIG['sARTICLESOUTPUTNETTO'] && !$this->sSYSTEM->sUSERGROUPDATA["tax"]) || (!$this->sSYSTEM->sUSERGROUPDATA["tax"] && $this->sSYSTEM->sUSERGROUPDATA["id"])) {
                        // If netto set both values to net-price
                        $getPrice["price"] = $this->sSYSTEM->sMODULES['sArticles']->sCalculatingPriceNum($getPrice["price"],$getPrice["tax"],false,false,$getArticle["taxID"],false,$getArticle);
                        $getPrice["netprice"] = $getPrice["price"];
                    } else {
                        // If brutto, save net
                        $getPrice["netprice"] = $getPrice["price"];
                        $getPrice["price"] = $this->sSYSTEM->sMODULES['sArticles']->sCalculatingPriceNum($getPrice["price"],$getPrice["tax"],false,false,$getArticle["taxID"],false, $getArticle);
                    }
                    // For variants, extend the article-name
                    if ($getArticle["additionaltext"]) {
                        $getArticle["articleName"].= " ".$getArticle["additionaltext"];
                    }

                    if (!$getArticle["shippingfree"]) $getArticle["shippingfree"] = "0";

                    // Check if article is an esd-article
                    // - add flag to basket
                    $sqlGetEsd = "
                    SELECT s_articles_esd.id AS id, serials FROM s_articles_esd,s_articles_details WHERE s_articles_esd.articleID=?
                    AND s_articles_esd.articledetailsID=s_articles_details.id AND s_articles_details.ordernumber=?
                    ";
                    $getEsd = $this->sSYSTEM->sDB_CONNECTION->GetRow($sqlGetEsd,array($getArticle["articleID"],$getArticle["ordernumber"]));
                    if ($getEsd["id"]) {
                        $sEsd = "1";
                    } else {
                        $sEsd = "0";
                    }

                    $quantity = (int) $quantity;
                    $sql = "
                        INSERT INTO s_order_basket (id,sessionID,userID,articlename,articleID,
                        ordernumber, shippingfree, quantity, price, netprice, datum, esdarticle, partnerID, config)
                        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ? )
                    ";

                    $params = array(
                        '',
                        (string) $this->sSYSTEM->sSESSION_ID,
                        (string) $this->sSYSTEM->_SESSION['sUserId'],
                        $getArticle["articleName"],
                        $getArticle["articleID"],
                        (string) $getArticle["ordernumber"],
                        $getArticle["shippingfree"],
                        $quantity,
                        $getPrice["price"],
                        $getPrice["netprice"],
                        (string) $insertTime,
                        $sEsd,
                        (string) $this->sSYSTEM->_SESSION["sPartner"],
                        (empty($sUpPriceValues) ? "" : serialize($sUpPriceValues))
                    );

                    $sql = Enlight()->Events()->filter('Shopware_Modules_Basket_AddArticle_FilterSql',$sql, array('subject'=>$this,"article"=>$getArticle,"price"=>$getPrice,"esd"=>$sEsd,"quantity"=>$quantity,"partner"=>$this->sSYSTEM->_SESSION["sPartner"]));

                    $rs = $this->sSYSTEM->sDB_CONNECTION->Execute($sql, $params);

                    if (!$rs) {
                        $this->sSYSTEM->E_CORE_WARNING ("BASKET-INSERT #02","SQL-Error".$sql);
                    }
                    $insertId = $this->sSYSTEM->sDB_CONNECTION->Insert_ID();

                    $sql = "INSERT INTO s_order_basket_attributes (basketID, attribute1) VALUES (". $insertId .", ".$this->sSYSTEM->sDB_CONNECTION->qstr(implode($pictureRelations,"$$")).")";
                    $this->sSYSTEM->sDB_CONNECTION->Execute($sql);

                    $this->sUpdateArticle($insertId,$quantity);

                } // If - Price was found
            } // If - Article is not in basket
        } // If - Article was found

        return $insertId;
    }

    /**
     * Refresh basket after login / currency change
     * @deprecated
     */
    public function sRefreshBasket()
    {
        $session = Shopware()->Session();
        $admin = Shopware()->Modules()->Admin();

        // Update basket data
        $admin->sGetUserData();
        $this->sGetBasket();
        $admin->sGetShippingcosts();

        // Update basket data in session
        $session->sBasketCurrency = Shopware()->Shop()->getCurrency()->getId();
        $session->sBasketQuantity = $this->sCountBasket();
        $amount = $this->sGetAmount();
        $session->sBasketAmount = empty($amount) ? 0 : array_shift($amount);
    }
}
