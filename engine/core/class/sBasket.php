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
 */
class sBasket
{
    /**
     * @var sSystem
     */
    public $sSYSTEM;

    public $sBASKET;

    /**
     * @var Shopware_Components_Snippet_Namespace
     */
    public $snippetObject;

    public function __construct()
    {
        $this->snippetObject = Shopware()->Snippets()->getNamespace('frontend/basket/internalMessages');
    }
    /**
     * Get total value of current user's cart
     * Used in multiple locations
     *
     * @deprecated
     * @return array Total amount of the user's cart
     */
    public function sGetAmount()
    {
        return $this->sSYSTEM->sDB_CONNECTION->GetRow(
            "SELECT SUM(quantity*(floor(price * 100 + .55)/100)) AS totalAmount
                FROM s_order_basket
                WHERE sessionID = ? GROUP BY sessionID",
            array($this->sSYSTEM->sSESSION_ID)
        );
    }

    /**
     * Get total value of current user's cart (only products)
     * Used only internally in sBasket
     * 
     * @deprecated
     * @return array Total amount of the user's cart (only products)
     */
    private function sGetAmountArticles()
    {
        return $this->sSYSTEM->sDB_CONNECTION->GetRow(
            "SELECT SUM(quantity*(floor(price * 100 + .55)/100)) AS totalAmount
                FROM s_order_basket
                WHERE sessionID = ? AND modus = 0
                GROUP BY sessionID",
            array($this->sSYSTEM->sSESSION_ID)
        );
    }

    /**
     * Check if all positions in cart are available
     * Used in CheckoutController
     * 
     * @deprecated
     * @return array
     */
    public function sCheckBasketQuantities()
    {
        $result = $this->sSYSTEM->sDB_CONNECTION->GetAll(
            'SELECT (d.instock - b.quantity) as diffStock, b.ordernumber,
                a.laststock, IF(a.active=1, d.active, 0) as active
            FROM s_order_basket b
            LEFT JOIN s_articles_details d
              ON d.ordernumber = b.ordernumber
              AND d.articleID = b.articleID
            LEFT JOIN s_articles a
              ON a.id = d.articleID
            WHERE b.sessionID = ?
              AND b.modus = 0
            GROUP BY b.ordernumber',
            array($this->sSYSTEM->sSESSION_ID)
        );
        $hideBasket = false;
        foreach ($result as $article) {
            if (empty($article['active'])
              || (!empty($article['laststock']) && $article['diffStock'] < 0)
            ) {
                $hideBasket = true;
                $articles[$article['ordernumber']]['OutOfStock'] = true;
            } else {
                $articles[$article['ordernumber']]['OutOfStock'] = false;
            }
        }
        return array('hideBasket' => $hideBasket, 'articles' => $articles);
    }

    /**
     * Get cart amount for certain products / suppliers
     * Used only internally in sBasket
     *
     * @deprecated
     * @param array $articles Articles numbers to filter
     * @param int $supplier Supplier id to filter
     * @return array Amount of articles in current basket that match the current filter
     */
    private function sGetAmountRestrictedArticles($articles, $supplier)
    {
        if (!is_array($articles) && empty($supplier)) {
            return $this->sGetAmountArticles();
        }
        if (is_array($articles)) {
            foreach ($articles as $article) {
                $article = $article;
                $newArticles[] = $article;
            }
            $in = implode(",", $newArticles);
            $articleSQL = "ordernumber IN ($in) ";
        }
        if (!empty($supplier)) {
            if (empty($articleSQL)) {
                $articleSQL = "1 != 1 ";
            }
            $supplierSQL = "OR s_articles.supplierID = $supplier ";
        }
        return $this->sSYSTEM->sDB_CONNECTION->GetRow(
            "SELECT SUM(quantity*(floor(price * 100 + .55)/100)) AS totalAmount
                FROM s_order_basket, s_articles
                WHERE sessionID = ? AND modus = 0 AND s_order_basket.articleID = s_articles.id
                AND
                (
                    $articleSQL
                    $supplierSQL
                )
                GROUP BY sessionID",
            array($this->sSYSTEM->sSESSION_ID)
        );
    }

    /**
     * Update vouchers in cart
     * Used only internally in sBasket
     * 
     * @deprecated
     * @return null
     */
    private function sUpdateVoucher()
    {
        $voucher = $this->sSYSTEM->sDB_CONNECTION->GetRow(
            'SELECT id basketID, ordernumber, articleID as voucherID
                FROM s_order_basket
                WHERE modus=2 AND sessionID=?',
            array($this->sSYSTEM->sSESSION_ID)
        );
        if (!empty($voucher)) {
            $voucher['code'] = $this->sSYSTEM->sDB_CONNECTION->GetOne(
                'SELECT vouchercode FROM s_emarketing_vouchers WHERE ordercode=?',
                array($voucher['ordernumber'])
            );
            if (empty($voucher['code'])) {
                $voucher['code'] = $this->sSYSTEM->sDB_CONNECTION->GetOne(
                    'SELECT code FROM s_emarketing_voucher_codes WHERE id=?',
                    array($voucher['voucherID'])
                );
            }
            $this->sDeleteArticle($voucher['basketID']);
            $this->sAddVoucher($voucher['code']);
        }
    }

    /**
     * Insert basket discount
     * Used only internally in sBasket::sGetBasket()
     *
     * @deprecated
     * @return Enlight_Components_Adodb_Statement|null
     */
    private function sInsertDiscount()
    {
        // Get possible discounts
        $getDiscounts = $this->sSYSTEM->sDB_CONNECTION->GetAll('
            SELECT basketdiscount, basketdiscountstart
                FROM s_core_customergroups_discounts
                WHERE groupID=?
                ORDER BY basketdiscountstart ASC',
            array($this->sSYSTEM->sUSERGROUPDATA["id"])
        );


        $this->sSYSTEM->sDB_CONNECTION->Execute(
            'DELETE FROM s_order_basket WHERE sessionID = ? AND modus = 3',
            array($this->sSYSTEM->sSESSION_ID)
        );

        // No discounts
        if (!count($getDiscounts)) {
            return;
        }

        $sql = 'SELECT SUM(quantity*(floor(price * 100 + .55)/100)) AS totalAmount
              FROM s_order_basket
              WHERE sessionID = ? AND modus != 4
              GROUP BY sessionID';
        $params = array($this->sSYSTEM->sSESSION_ID);

        $sql = Enlight()->Events()->filter(
            'Shopware_Modules_Basket_InsertDiscount_FilterSql_BasketAmount',
            $sql,
            array('subject' => $this, 'params' => $params)
        );
        $basketAmount = Shopware()->Db()->fetchOne($sql, $params);

        // If no articles in basket, return
        if (!$basketAmount) {
            return;
        }

        // Iterate through discounts and find nearly one
        foreach ($getDiscounts as $discountRow) {
            if ($basketAmount<$discountRow["basketdiscountstart"]) {
                break;
            } else {
                $basketDiscount = $discountRow["basketdiscount"];
            }
        }

        if (!$basketDiscount) {
            return;
        }

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
        if (!$tax) {
            $tax = 19;
        }

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

        $this->sSYSTEM->sDB_CONNECTION->Execute($sql, $params);
    }

    /**
     * Check if any discount is in the cart
     * Used only internally in sBasket
     * 
     * @deprecated
     * @return void
     */
    private function sCheckForDiscount()
    {
        $rs = $this->sSYSTEM->sDB_CONNECTION->GetRow(
            'SELECT id FROM s_order_basket WHERE sessionID = ? AND modus = 3',
            array($this->sSYSTEM->sSESSION_ID)
        );

        return (bool) ($rs["id"]);
    }

    /**
     * Add premium products to cart
     * Used internally in sBasket and in CheckoutController
     * 
     * @deprecated
     * @return bool|int
     */
    public function sInsertPremium()
    {
        static $last_premium;

        $sBasketAmount = $this->sGetAmount();
        $sBasketAmount = empty($sBasketAmount["totalAmount"]) ? 0 : $sBasketAmount["totalAmount"];
        $sBasketAmount = (float) $sBasketAmount;

        if (empty($this->sSYSTEM->_GET["sAddPremium"])) {
            $deletePremium = Shopware()->Db()->fetchCol(
                'SELECT basket.id
                FROM s_order_basket basket
                LEFT JOIN s_addon_premiums premium
                ON premium.id = basket.articleID
                AND premium.startprice <= ?
                WHERE basket.modus = 1
                AND premium.id IS NULL
                AND basket.sessionID = ?',
                array($sBasketAmount, $this->sSYSTEM->sSESSION_ID)
            );
            if (empty($deletePremium)) {
                return true;
            }

            $deletePremium = Shopware()->Db()->quote($deletePremium);
            Shopware()->Db()->query("DELETE FROM s_order_basket WHERE id IN ($deletePremium)");
            return true;
        }

        // This is dead, as its the same as previous 'id', remove after testing
        if (empty($this->sSYSTEM->_GET["sAddPremium"])) {
            return false;
        }

        if (isset($last_premium) && $last_premium == $this->sSYSTEM->_GET["sAddPremium"]) {
            return false;
        }

        $last_premium = $this->sSYSTEM->_GET["sAddPremium"];

        $this->sSYSTEM->sDB_CONNECTION->Execute("
            DELETE FROM s_order_basket WHERE sessionID='".$this->sSYSTEM->sSESSION_ID."' AND modus = 1
        ");

        $orderNumber = $this->sSYSTEM->sDB_CONNECTION->qstr($this->sSYSTEM->_GET["sAddPremium"]);

        $premium = $this->sSYSTEM->sDB_CONNECTION->GetRow("
            SELECT premium.id, detail.ordernumber, article.id as articleID, article.name,
            detail.additionaltext, premium.ordernumber_export, article.configurator_set_id
            FROM
                s_addon_premiums premium,
                s_articles_details detail,
                s_articles article,
                s_articles_details detail2
            WHERE detail.ordernumber = $orderNumber
            AND premium.startprice <= $sBasketAmount
            AND premium.ordernumber = detail2.ordernumber
            AND detail2.articleID = detail.articleID
            AND detail.articleID = article.id
        ");

        if (empty($premium)) {
            return false;
        }

        $premium = $this->sSYSTEM->sMODULES['sArticles']->sGetTranslation(
            $premium, $premium["articleID"], "article", $this->sSYSTEM->sLanguage
        );
        if (!empty($premium['configurator_set_id'])) {
            $number = $premium['ordernumber'];
        } else {
            $number = $premium['ordernumber_export'];
        }

        $sql = "
            INSERT INTO s_order_basket (
                sessionID, articlename, articleID, ordernumber, quantity,
                price, netprice,tax_rate, datum, modus, currencyFactor
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
     * REPLACE WITH sCountBasket AFTER TESTING IS DONE
     *
     * Get the number of elements in the current cart
     * Used only internally in sBasket
     *
     * @deprecated
     * @return int Number of items in the current cart
     */
    private function sCountArticles()
    {
        return $this->sSYSTEM->sDB_CONNECTION->GetOne(
            'SELECT COUNT(*) FROM s_order_basket WHERE modus = 0 AND sessionID = ?',
            array($this->sSYSTEM->sSESSION_ID)
        );
    }

    /**
     * Get the max tax rate in applied in the current basket
     * Used in several places
     *
     * @deprecated
     * @return int|false May tax value, or false if none found
     */
    public function getMaxTax()
    {
        return $this->sSYSTEM->sDB_CONNECTION->GetOne(
            'SELECT MAX(tax_rate) as max_tax
                FROM s_order_basket b
                WHERE b.sessionID = ? AND b.modus = 0
                GROUP BY b.sessionID',
            array(empty($this->sSYSTEM->sSESSION_ID) ? session_id() : $this->sSYSTEM->sSESSION_ID)
        );
    }

    /**
     * Add voucher to cart
     * Used in several places
     *
     * @deprecated
     * @param string $voucherCode Voucher code
     * @param string $basket
     * @return array|bool True if successful, false if stopped by an event, array with error data if one occurred
     */
    public function sAddVoucher($voucherCode, $basket = '')
    {
        if (Enlight()->Events()->notifyUntil(
            'Shopware_Modules_Basket_AddVoucher_Start',
            array('subject' => $this, 'code' => $voucherCode, 'basket' => $basket)
        )) {
            return false;
        }

        $voucherCode = stripslashes($voucherCode);
        $voucherCode = strtolower($voucherCode);

        // Load the voucher details
        $voucherDetails = $this->sSYSTEM->sDB_CONNECTION->GetRow(
            'SELECT * FROM s_emarketing_vouchers
            WHERE modus != 1 AND LOWER(vouchercode)=?
            AND ((valid_to >= now() AND valid_from <= now()) OR valid_to is NULL)',
            array($voucherCode)
        );

        // Check if voucher has already been cashed
        if ($this->sSYSTEM->_SESSION["sUserId"] && $voucherDetails["id"]) {
            $userId = $this->sSYSTEM->_SESSION["sUserId"];
            $sql = "
            SELECT s_order_details.id AS id
            FROM s_order, s_order_details
            WHERE s_order.userID = $userId
            AND s_order_details.orderID=s_order.id
            AND s_order_details.articleordernumber = '{$voucherDetails["ordercode"]}'
            AND s_order_details.ordernumber!='0'
            ";

            $queryVoucher = $this->sSYSTEM->sDB_CONNECTION->GetAll($sql);
            if (count($queryVoucher) >= $voucherDetails["numorder"] && !$voucherDetails["modus"]) {

                $sErrorMessages[] = $this->snippetObject->get(
                    'VoucherFailureAlreadyUsed',
                    'This voucher was used in an previous order'
                );

                return array("sErrorFlag" => true, "sErrorMessages" => $sErrorMessages);
            }
        }

        if ($voucherDetails["id"]) {
            // If we have voucher details, its a reusable code
            // We need to check how many times it has already been used
            $usedVoucherCount = $this->sSYSTEM->sDB_CONNECTION->GetRow("
                SELECT COUNT(id) AS vouchers
                FROM s_order_details
                WHERE articleordernumber='{$voucherDetails["ordercode"]}'
                AND s_order_details.ordernumber!='0'
            ");
        } else {
            // If we don't have voucher details yet, need to check if its a one-time code
            $sql = "
            SELECT s_emarketing_voucher_codes.id AS id, s_emarketing_voucher_codes.code AS vouchercode,
            description, numberofunits, customergroup, value, restrictarticles,
            minimumcharge, shippingfree, bindtosupplier, taxconfig, valid_from,
            valid_to, ordercode, modus, percental, strict, subshopID
            FROM s_emarketing_vouchers, s_emarketing_voucher_codes
            WHERE modus = 1
            AND s_emarketing_vouchers.id = s_emarketing_voucher_codes.voucherID
            AND LOWER(code) = ?
            AND cashed != 1
            AND (
                  (s_emarketing_vouchers.valid_to >= now()
                      AND s_emarketing_vouchers.valid_from<=now()
                  )
                  OR s_emarketing_vouchers.valid_to is NULL
            )
            ";
            $voucherDetails = $this->sSYSTEM->sDB_CONNECTION->GetRow($sql, array($voucherCode));
            $individualCode = (bool) $voucherDetails["description"];
        }

        // Interrupt the operation if one of the following occurs:
        // 1 - No voucher details were found (individual or reusable)
        // 2 - No voucher code
        // 3 - Voucher is reusable and has already been used to the limit
        if (!count($voucherDetails)
            || !$voucherCode
            || ($voucherDetails["numberofunits"] <= $usedVoucherCount["vouchers"] && !$individualCode)
        ) {
            $sErrorMessages[] = $this->snippetObject->get(
                'VoucherFailureNotFound',
                'Voucher could not be found or is not valid anymore'
            );
            return array("sErrorFlag" => true, "sErrorMessages" => $sErrorMessages);
        }

        $restrictDiscount = !empty($voucherDetails["strict"]);

        // If voucher is limited to a specific subshop, filter that and return on failure
        if (!empty($voucherDetails["subshopID"])) {
            if ($this->sSYSTEM->sSubShop["id"] != $voucherDetails["subshopID"]) {
                $sErrorMessages[] = $this->snippetObject->get(
                    'VoucherFailureNotFound',
                    'Voucher could not be found or is not valid anymore'
                );
                return array("sErrorFlag" => true, "sErrorMessages" => $sErrorMessages);
            }
        }

        // Check if the basket already has a voucher, and break if it does
        $chkBasket = $this->sSYSTEM->sDB_CONNECTION->GetRow("
        SELECT id FROM s_order_basket WHERE sessionID='".$this->sSYSTEM->sSESSION_ID."' AND modus = 2
        ");
        if (count($chkBasket)) {
            $sErrorMessages[] = $this->snippetObject->get(
                'VoucherFailureOnlyOnes',
                'Only one voucher can be processed in order'
            );
            return array("sErrorFlag" => true, "sErrorMessages" => $sErrorMessages);
        }

        // Check if the voucher is limited to a certain customer group, and validate that
        if (!empty($voucherDetails["customergroup"])) {
            $userId = $this->sSYSTEM->_SESSION["sUserId"];

            if (!empty($userId)) {
                // Get customer group
                $queryCustomerGroup = $this->sSYSTEM->sDB_CONNECTION->GetRow("
                SELECT s_core_customergroups.id, customergroup
                FROM s_user, s_core_customergroups WHERE s_user.id=$userId
                AND s_user.customergroup = s_core_customergroups.groupkey
                ");
            }
            $customerGroup = $queryCustomerGroup["customergroup"];
            if ($customerGroup != $voucherDetails["customergroup"]
                && $voucherDetails["customergroup"] != $queryCustomerGroup["id"]
                && $voucherDetails["customergroup"] != $this->sSYSTEM->sUSERGROUPDATA["id"]
            ) {
                $sErrorMessages[] = $this->snippetObject->get(
                    'VoucherFailureCustomerGroup',
                    'This voucher is not available for your customer group'
                );
                return array("sErrorFlag" => true, "sErrorMessages" => $sErrorMessages);
            }
        }

        // Check if the voucher is limited to certain articles, and validate that
        if (!empty($voucherDetails["restrictarticles"]) && strlen($voucherDetails["restrictarticles"]) > 5) {
            $restrictedArticles = explode(";",$voucherDetails["restrictarticles"]);
            if (count($restrictedArticles)==0) {
                $restrictedArticles[] = $voucherDetails["restrictarticles"];
            }
            foreach ($restrictedArticles as $k => $restrictedArticle) {
                $restrictedArticles[$k] = (string) $this->sSYSTEM->sDB_CONNECTION->qstr($restrictedArticle);
            }

            $sql = "
            SELECT id FROM s_order_basket
            WHERE sessionID='".$this->sSYSTEM->sSESSION_ID."'
            AND modus=0
            AND ordernumber IN (".implode(",",$restrictedArticles).")
            ";

            $allowedBasketEntriesByArticle = $this->sSYSTEM->sDB_CONNECTION->GetOne($sql);
            $foundMatchingArticle = false;

            if (!empty($allowedBasketEntriesByArticle)) {
                $foundMatchingArticle = true;
            }

            if (empty($foundMatchingArticle)) {

                $sErrorMessages[] = $this->snippetObject->get(
                    'VoucherFailureProducts',
                    'This voucher is only available in combination with certain products'
                );
                return array("sErrorFlag" => true, "sErrorMessages" => $sErrorMessages);
            }
        }
        // Check if the voucher is limited to certain supplier, and validate that
        if ($voucherDetails["bindtosupplier"]) {
            $allowedSupplierId = $voucherDetails["bindtosupplier"];
            $sql = "
            SELECT s_order_basket.id
            FROM s_order_basket, s_articles, s_articles_supplier
            WHERE s_order_basket.articleID = s_articles.id
            AND s_articles.supplierID = $allowedSupplierId
            AND s_order_basket.sessionID = '".$this->sSYSTEM->sSESSION_ID."'
            ";

            $allowedBasketEntriesBySupplier = $this->sSYSTEM->sDB_CONNECTION->GetRow($sql);

            if (!count($allowedBasketEntriesBySupplier)) {
                $allowedSupplierName = $this->sSYSTEM->sDB_CONNECTION->GetRow(
                    "SELECT name FROM s_articles_supplier WHERE id = $allowedSupplierId"
                );

                $sErrorMessages[] = str_replace(
                    "{sSupplier}",
                    $allowedSupplierName["name"],
                    $this->snippetObject->get(
                        'VoucherFailureSupplier',
                        'This voucher is only available for products from {sSupplier}'
                    )
                );
                return array("sErrorFlag" => true, "sErrorMessages" => $sErrorMessages);
            }
        }

        // Calculate the amount in the basket
        if (!empty($restrictDiscount) && (!empty($restrictedArticles) || !empty($allowedSupplierId))) {
            $amount = $this->sGetAmountRestrictedArticles($restrictedArticles, $allowedSupplierId);
        } else {
            $amount = $this->sGetAmountArticles();
        }

        // Including currency factor
        if ($this->sSYSTEM->sCurrency["factor"] && empty($voucherDetails["percental"])) {
            $factor = $this->sSYSTEM->sCurrency["factor"];
            $voucherDetails["value"] *= $factor;
        } else {
            $factor = 1;
        }

        // Check if the basket's value is above the voucher's
        if (($amount["totalAmount"]/$factor) < $voucherDetails["minimumcharge"]) {
            $sErrorMessages[] = str_replace(
                "{sMinimumCharge}",
                $voucherDetails["minimumcharge"],
                $this->snippetObject->get(
                    'VoucherFailureMinimumCharge',
                    'The minimum charge for this voucher is {sMinimumCharge}'
                )
            );
            return array( "sErrorFlag" => true, "sErrorMessages" => $sErrorMessages);
        }

        $timeInsert = date("Y-m-d H:i:s");

        $voucherName = $this->sSYSTEM->sCONFIG['sVOUCHERNAME'];
        if ($voucherDetails["percental"]) {
            $value = $voucherDetails["value"];
            $voucherName .= " ".$value." %";
            $voucherDetails["value"] = ($amount["totalAmount"] / 100) * floatval($value);
        }

        // Tax calculation for vouchers
        $taxRate = 0;
        if (
            (!$this->sSYSTEM->sUSERGROUPDATA["tax"] && $this->sSYSTEM->sUSERGROUPDATA["id"])
            || $voucherDetails["taxconfig"] == "none"
        ) {
            // if net customer group - calculate without tax
            $tax = $voucherDetails["value"] * -1;
            if ($voucherDetails["taxconfig"] == "default" || empty($voucherDetails["taxconfig"])) {
                $taxRate = $this->sSYSTEM->sCONFIG['sVOUCHERTAX'];
            } elseif ($voucherDetails["taxconfig"] == "auto") {
                $taxRate =$this->getMaxTax();

            } elseif (intval($voucherDetails["taxconfig"])) {
               $temporaryTax = $voucherDetails["taxconfig"];
               $getTaxRate = $this->sSYSTEM->sDB_CONNECTION->getOne(
                   'SELECT tax FROM s_core_tax WHERE id = ?',
                   array($temporaryTax)
               );
               $taxRate = $getTaxRate;
            }
        } else {
            if ($voucherDetails["taxconfig"] == "default" || empty($voucherDetails["taxconfig"])) {
                $tax = round($voucherDetails["value"]/(100+$this->sSYSTEM->sCONFIG['sVOUCHERTAX'])*100,3)*-1;
                $taxRate = $this->sSYSTEM->sCONFIG['sVOUCHERTAX'];
                // Pre 3.5.4 behaviour
            } elseif ($voucherDetails["taxconfig"] == "auto") {
                // Check max. used tax-rate from basket
                $tax = $this->getMaxTax();
                $taxRate = $tax;
                $tax = round($voucherDetails["value"]/(100+$tax)*100,3)*-1;
            } elseif (intval($voucherDetails["taxconfig"])) {
                // Fix defined tax
                $temporaryTax = $voucherDetails["taxconfig"];
                $getTaxRate = $this->sSYSTEM->sDB_CONNECTION->getOne(
                    'SELECT tax FROM s_core_tax WHERE id = ?',
                    array($temporaryTax)
                );
                $taxRate = $getTaxRate;
                $tax = round($voucherDetails["value"]/(100+$getTaxRate)*100,3)*-1;
            } else {
                // No tax
                $tax = $voucherDetails["value"] * -1;
            }
        }

        $voucherDetails["value"] = $voucherDetails["value"] * -1;

        if ($voucherDetails["shippingfree"]) {
            $freeShipping = "1";
        } else {
            $freeShipping = "0";
        }

        // Finally, add the discount entry to the basket
        $sql = "
        INSERT INTO s_order_basket (
          sessionID, articlename, articleID, ordernumber, shippingfree,
          quantity, price, netprice,tax_rate, datum, modus, currencyFactor
        )
        VALUES (?,?,?,?,?,1,?,?,?,?,2,?)
        ";
        $params = array (
            $this->sSYSTEM->sSESSION_ID,
            $voucherName,
            $voucherDetails["id"],
            $voucherDetails["ordercode"],
            $freeShipping,
            $voucherDetails["value"],
            $tax,
            $taxRate,
            $timeInsert,
            $this->sSYSTEM->sCurrency["factor"]
        );
        $sql = Enlight()->Events()->filter(
            'Shopware_Modules_Basket_AddVoucher_FilterSql',
            $sql,
            array(
                'subject' => $this,
                "params" => $params,
                "voucher" => $voucherDetails,
                "name" => $voucherName,
                "shippingfree" => $freeShipping,
                "tax" => $tax
            )
        );

        return (bool) ($this->sSYSTEM->sDB_CONNECTION->Execute($sql, $params));
    }

    /**
     * Get articleId of all products from cart
     * Used in CheckoutController
     * 
     * @deprecated
     * @return array|null List of article ids in current basket, or null if none
     */
    public function sGetBasketIds()
    {
        $getArticles = $this->sSYSTEM->sDB_CONNECTION->GetAll(
            'SELECT DISTINCT articleID
                FROM s_order_basket
                WHERE sessionID=?
                AND modus=0
                ORDER BY modus ASC, datum DESC',
            array($this->sSYSTEM->sSESSION_ID)
        );

        foreach ($getArticles as $article) {
            $articles[] = $article["articleID"];
        }

        return $articles;
    }

    /**
     * Check if minimum charging is reached
     * Used only in CheckoutController::getMinimumCharge()
     * 
     * @deprecated
     * @return double|false Minimum order value in current currency, or false
     */
    public function sCheckMinimumCharge()
    {
        if (
            $this->sSYSTEM->sUSERGROUPDATA["minimumorder"]
            && !$this->sSYSTEM->sUSERGROUPDATA["minimumordersurcharge"]
        ) {
            $amount = $this->sGetAmount();
            if ($amount["totalAmount"] < ($this->sSYSTEM->sUSERGROUPDATA["minimumorder"] * $this->sSYSTEM->sCurrency["factor"])) {
                return ($this->sSYSTEM->sUSERGROUPDATA["minimumorder"] * $this->sSYSTEM->sCurrency["factor"]);
            }
        }
        return false;
    }

    /**
     * Add surcharge for payment means to cart
     * Used only internally in sBasket::sGetBasket
     * 
     * @deprecated
     * @return null|false False on failure, null on success
     */
    private function sInsertSurcharge()
    {
        $name = isset($this->sSYSTEM->sCONFIG['sSURCHARGENUMBER']) ? $this->sSYSTEM->sCONFIG['sSURCHARGENUMBER']: "SURCHARGE";

        // Delete previous inserted discounts
        $this->sSYSTEM->sDB_CONNECTION->Execute(
            'DELETE FROM s_order_basket WHERE sessionID=? AND ordernumber=?',
            array($this->sSYSTEM->sSESSION_ID, $name)
        );

        if (!$this->sCountArticles()) {
            return false;
        }

        if ($this->sSYSTEM->sUSERGROUPDATA["minimumorder"] && $this->sSYSTEM->sUSERGROUPDATA["minimumordersurcharge"]) {

            $amount = $this->sGetAmount();

            if ($amount["totalAmount"] < $this->sSYSTEM->sUSERGROUPDATA["minimumorder"]) {

                if (!empty($this->sSYSTEM->sCONFIG["sTAXAUTOMODE"])) {
                    $tax = $this->sSYSTEM->sMODULES['sBASKET']->getMaxTax();
                } else {
                    $tax = $this->sSYSTEM->sCONFIG['sDISCOUNTTAX'];
                }

                if (empty($tax)) {
                    $tax = 19;
                }

                if ((!$this->sSYSTEM->sUSERGROUPDATA["tax"] && $this->sSYSTEM->sUSERGROUPDATA["id"])) {
                    $discountNet = $this->sSYSTEM->sUSERGROUPDATA["minimumordersurcharge"];
                } else {
                    $discountNet = round($this->sSYSTEM->sUSERGROUPDATA["minimumordersurcharge"] / (100+$tax) * 100,3);
                }

                if ($this->sSYSTEM->sCurrency["factor"]) {
                    $factor = $this->sSYSTEM->sCurrency["factor"];
                    $discountNet *= $factor;
                } else {
                    $factor = 1;
                }

                $surcharge = $this->sSYSTEM->sUSERGROUPDATA["minimumordersurcharge"] * $factor;

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
                INSERT INTO s_order_basket (sessionID, articlename, articleID, ordernumber, quantity, price, netprice, tax_rate, datum, modus, currencyFactor)
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
     * Used only internally in sBasket::sGetBasket
     * 
     * @deprecated
     * @return void|false False on failure, null on success
     */
    private function sInsertSurchargePercent()
    {
        if (!$this->sSYSTEM->_SESSION["sUserId"]) {
            if (!$this->sSYSTEM->_SESSION["sPaymentID"]) {
                return false;
            } else {
                $paymentInfo = $this->sSYSTEM->sDB_CONNECTION->GetRow(
                    'SELECT debit_percent
                    FROM s_core_paymentmeans
                    WHERE id=?',
                    array($this->sSYSTEM->_SESSION["sPaymentID"])
                );
            }
        } else {
            $userData =  $this->sSYSTEM->sDB_CONNECTION->GetRow(
                'SELECT paymentID FROM s_user WHERE id=?',
                array(intval($this->sSYSTEM->_SESSION["sUserId"]))
            );
            $paymentInfo = $this->sSYSTEM->sDB_CONNECTION->GetRow(
                'SELECT debit_percent FROM s_core_paymentmeans WHERE id=?',
                array($userData["paymentID"])
            );

        }

        $name = isset($this->sSYSTEM->sCONFIG['sPAYMENTSURCHARGENUMBER']) ? $this->sSYSTEM->sCONFIG['sPAYMENTSURCHARGENUMBER']: "PAYMENTSURCHARGE";
        // Depends on payment mean
        $percent = $paymentInfo["debit_percent"];

        $this->sSYSTEM->sDB_CONNECTION->Execute(
            'DELETE FROM s_order_basket WHERE sessionID=? AND ordernumber=?',
            array($this->sSYSTEM->sSESSION_ID,$name)
        );

        if (!$this->sCountArticles()) {
            return false;
        }

        if (!empty($percent)) {
            $amount = $this->sGetAmount();

            if ($percent >= 0) {
                $surchargename = $this->sSYSTEM->sCONFIG["sPAYMENTSURCHARGEADD"];
            } else {
                $surchargename = $this->sSYSTEM->sCONFIG["sPAYMENTSURCHARGEDEV"];
            }

            $surcharge = $amount["totalAmount"] / 100 * $percent;

            if (!empty($this->sSYSTEM->sCONFIG["sTAXAUTOMODE"])) {
                $tax = $this->getMaxTax();
            } else {
                $tax = $this->sSYSTEM->sCONFIG['sDISCOUNTTAX'];
            }

            if (!$tax) {
                $tax = 119;
            }

            if ((!$this->sSYSTEM->sUSERGROUPDATA["tax"] && $this->sSYSTEM->sUSERGROUPDATA["id"])) {
                $discountNet = $surcharge;
            } else {
                $discountNet = round($surcharge / (100+$tax) * 100,3);
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
     * Used in multiple locations
     * 
     * @deprecated
     * @return array Number
     */
    public function sCountBasket()
    {
        $getArticles = $this->sSYSTEM->sDB_CONNECTION->GetAll(
            'SELECT id FROM s_order_basket WHERE sessionID = ? AND modus = 0',
            array($this->sSYSTEM->sSESSION_ID)
        );
        return count($getArticles);
    }

    /**
     * Get all basket positions
     * Used in multiple location
     * 
     * @deprecated
     * @return array Basket content
     */
    public function sGetBasket()
    {
        $discount = 0;
        $totalAmount = 0;
        $totalAmountWithTax = 0;
        $totalAmountNet = 0;
        $totalCount = 0;

        // Refresh basket prices
        $basketData = $this->sSYSTEM->sDB_CONNECTION->GetAll(
            'SELECT id, modus, quantity FROM s_order_basket
            WHERE sessionID = ?',
            array($this->sSYSTEM->sSESSION_ID)
        );
        foreach ($basketData as $basketContent) {
            if (empty($basketContent["modus"])) {
                $this->sUpdateArticle($basketContent["id"], $basketContent["quantity"]);
            }
        }

        // Check, if we have some free products for the client
        $this->sInsertPremium();

        // Delete previous given discounts
        if (empty($this->sSYSTEM->sCONFIG['sPREMIUMSHIPPIUNG'])) {
            $this->sSYSTEM->sDB_CONNECTION->Execute(
                'DELETE FROM s_order_basket WHERE sessionID=? AND modus=3',
                array($this->sSYSTEM->sSESSION_ID)
            );
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
        $sql = Enlight()->Events()->filter(
            'Shopware_Modules_Basket_GetBasket_FilterSQL',
            $sql,
            array('subject' => $this)
        );

        $getArticles = $this->sSYSTEM->sDB_CONNECTION->GetAll($sql, array($this->sSYSTEM->sSESSION_ID));
        $countItems = count($getArticles);

        if (!empty($countItems)) {
            // Reformatting data, add additional data fields to array
            foreach ($getArticles as $key => $value) {
                $getArticles[$key] = Enlight()->Events()->filter(
                    'Shopware_Modules_Basket_GetBasket_FilterItemStart',
                    $getArticles[$key],
                    array('subject' => $this,'getArticles' => $getArticles)
                );

                $getArticles[$key]["shippinginfo"] = (empty($getArticles[$key]["modus"]));

                if (
                    !empty($getArticles[$key]["releasedate"])
                    && strtotime($getArticles[$key]["releasedate"]) <= time()
                ) {
                    $getArticles[$key]["sReleaseDate"] = $getArticles[$key]["releasedate"] = "";
                }
                $getArticles[$key]["esd"] = $getArticles[$key]["esdarticle"];

                if (empty($getArticles[$key]["minpurchase"])) {
                    $getArticles[$key]["minpurchase"] = 1;
                }
                if (empty($getArticles[$key]["purchasesteps"])) {
                    $getArticles[$key]["purchasesteps"] = 1;
                }
                if ($getArticles[$key]["purchasesteps"]<=0) {
                    unset($getArticles[$key]["purchasesteps"]);
                }

                if (empty($getArticles[$key]["maxpurchase"])) {
                    $getArticles[$key]["maxpurchase"] = $this->sSYSTEM->sCONFIG['sMAXPURCHASE'];
                }
                if(
                    !empty($getArticles[$key]["laststock"])
                    && $getArticles[$key]["instock"] < $getArticles[$key]["maxpurchase"]
                ) {
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
                    $getPackUnit = $this->sSYSTEM->sMODULES['sArticles']->sGetTranslation(
                        array(),
                        $getArticles[$key]["articleID"],
                        "article",
                        $this->sSYSTEM->sLanguage
                    );
                    if (!empty($getPackUnit["packunit"])) {
                        $getArticles[$key]["packunit"] = $getPackUnit["packunit"];
                    }
                }

                $quantity = $getArticles[$key]["quantity"];
                $price = $getArticles[$key]["price"];
                $netprice = $getArticles[$key]["netprice"];

                if ($value["modus"] == 2) {
                    $sql = "
                        SELECT vouchercode,taxconfig
                        FROM s_emarketing_vouchers
                        WHERE ordercode='{$getArticles[$key]["ordernumber"]}'
                        ";

                    $ticketResult = $this->sSYSTEM->sDB_CONNECTION->GetRow($sql);

                    if (!$ticketResult["vouchercode"]) {
                        // Query Voucher-Code
                        $queryVoucher = $this->sSYSTEM->sDB_CONNECTION->GetRow("
                            SELECT code FROM s_emarketing_voucher_codes
                            WHERE id = {$getArticles[$key]["articleID"]}
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
                if (
                    ($this->sSYSTEM->sCONFIG['sARTICLESOUTPUTNETTO'] && !$this->sSYSTEM->sUSERGROUPDATA["tax"])
                    || (!$this->sSYSTEM->sUSERGROUPDATA["tax"] && $this->sSYSTEM->sUSERGROUPDATA["id"])
                ) {
                    if (empty($value["modus"])) {

                        $priceWithTax = round($netprice, 2) / 100 * (100+$tax);

                        $getArticles[$key]["amountWithTax"] = $quantity * $priceWithTax;
                        // If basket comprised any discount, calculate brutto-value for the discount
                        if ($this->sSYSTEM->sUSERGROUPDATA["basketdiscount"] && $this->sCheckForDiscount()) {
                            $discount += ($getArticles[$key]["amountWithTax"]/100*$this->sSYSTEM->sUSERGROUPDATA["basketdiscount"]);
                        }

                    } elseif ($value["modus"] == 3) {
                        $getArticles[$key]["amountWithTax"] = round(1 * (round($price,2) / 100 * (100+$tax)),2);
                        // Basket discount
                    } elseif ($value["modus"] == 2) {
                        $getArticles[$key]["amountWithTax"] = round(1 * (round($price,2) / 100 * (100+$tax)),2);

                        if ($this->sSYSTEM->sUSERGROUPDATA["basketdiscount"] && $this->sCheckForDiscount()) {
                            $discount += ($getArticles[$key]["amountWithTax"]/100*($this->sSYSTEM->sUSERGROUPDATA["basketdiscount"]));
                        }
                    } elseif ($value["modus"] == 4 || $value["modus"] == 10) {
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


                if ($value["modus"] == 2) {
                    // Gutscheine
                    if (!$this->sSYSTEM->sUSERGROUPDATA["tax"] && $this->sSYSTEM->sUSERGROUPDATA["id"]) {
                        $getArticles[$key]["amountnet"] = $quantity * round($price, 2);
                    } else {
                        $getArticles[$key]["amountnet"] = $quantity * round($netprice, 2);
                    }

                } else {
                    if (!$this->sSYSTEM->sUSERGROUPDATA["tax"] && $this->sSYSTEM->sUSERGROUPDATA["id"]) {
                        $getArticles[$key]["amountnet"] = $quantity * round($netprice, 2);
                    } else {
                        $getArticles[$key]["amountnet"] = $quantity * $netprice;
                    }
                }

                $totalAmount += round($getArticles[$key]["amount"], 2);
                // Needed if shop is in net-mode
                $totalAmountWithTax += round($getArticles[$key]["amountWithTax"], 2);
                // Ignore vouchers and premiums by counting articles
                if (!$getArticles[$key]["modus"]) {
                    $totalCount++;
                }

                $totalAmountNet += round($getArticles[$key]["amountnet"], 2);

                $getArticles[$key]["priceNumeric"] = $getArticles[$key]["price"];
                $getArticles[$key]["price"] = $this->sSYSTEM->sMODULES['sArticles']
                    ->sFormatPrice($getArticles[$key]["price"]);
                $getArticles[$key]["amount"] =  $this->sSYSTEM->sMODULES['sArticles']
                    ->sFormatPrice($getArticles[$key]["amount"]);
                $getArticles[$key]["amountnet"] = $this->sSYSTEM->sMODULES['sArticles']
                    ->sFormatPrice($getArticles[$key]["amountnet"]);

                //+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++

                if (!empty($getArticles[$key]["purchaseunitTemp"])) {
                    $getArticles[$key]["purchaseunit"] = $getArticles[$key]["purchaseunitTemp"];
                    $getArticles[$key]["itemInfo"] = $getArticles[$key]["purchaseunit"]." {$getUnitData["description"]} � ".$this->sSYSTEM->sMODULES['sArticles']->sFormatPrice(str_replace(",",".",$getArticles[$key]["amount"]) / $quantity);
                }

                //+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++


                if (empty($value["modus"])) {
                    // Article-Image
                    if (!empty($getArticles[$key]["ob_attr1"])) {
                        $getArticles[$key]["image"] = $this->sSYSTEM->sMODULES['sArticles']
                            ->sGetConfiguratorImage($this->sSYSTEM->sMODULES['sArticles']->sGetArticlePictures($getArticles[$key]["articleID"],false,$this->sSYSTEM->sCONFIG['sTHUMBBASKET'],false,true),$getArticles[$key]["ob_attr1"]);
                    } else {
                        $getArticles[$key]["image"] = $this->sSYSTEM->sMODULES['sArticles']
                            ->sGetArticlePictures($getArticles[$key]["articleID"],true,$this->sSYSTEM->sCONFIG['sTHUMBBASKET'],$getArticles[$key]["ordernumber"]);
                    }
                }
                // Links to details, basket
                $getArticles[$key]["linkDetails"] = $this->sSYSTEM->sCONFIG['sBASEFILE']."?sViewport=detail&sArticle=".$getArticles[$key]["articleID"];
                if ($value["modus"] == 2) {
                    $getArticles[$key]["linkDelete"] = $this->sSYSTEM->sCONFIG['sBASEFILE']."?sViewport=basket&sDelete=voucher";
                } else {
                    $getArticles[$key]["linkDelete"] = $this->sSYSTEM->sCONFIG['sBASEFILE']."?sViewport=basket&sDelete=".$getArticles[$key]["id"];
                }

                $getArticles[$key]["linkNote"] = $this->sSYSTEM->sCONFIG['sBASEFILE']."?sViewport=note&sAdd=".$getArticles[$key]["ordernumber"];

                $getArticles[$key] = Enlight()->Events()->filter(
                    'Shopware_Modules_Basket_GetBasket_FilterItemEnd',
                    $getArticles[$key],
                    array('subject' => $this, 'getArticles' => $getArticles)
                );
            }

            if ($totalAmount < 0 || empty($totalCount)) {
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

            $result = array(
                "content" => $getArticles,
                "Amount" => $totalAmount,
                "AmountNet" => $totalAmountNet,
                "Quantity" => $totalCount,
                "AmountNumeric" => $totalAmountNumeric,
                "AmountNetNumeric" => $totalAmountNetNumeric,
                "AmountWithTax" => $totalAmountWithTax,
                "AmountWithTaxNumeric" => $totalAmountWithTaxNumeric
            );


            if (!empty($this->sSYSTEM->_SESSION["sLastArticle"])) {
                $result["sLastActiveArticle"] = array(
                    "id" => $this->sSYSTEM->_SESSION["sLastArticle"],
                    "link" => $this->sSYSTEM->sCONFIG['sBASEFILE']."?sViewport=detail&sDetails=".$this->sSYSTEM->_SESSION["sLastArticle"]
                );
            }

            if (!empty($result["content"])) {
                foreach ($result["content"] as $key => $value) {
                    if (!empty($value['amountWithTax'])) {
                        $t = round(str_replace(",",".",$value['amountWithTax']),2);
                    } else {
                        $t = str_replace(",",".",$value["price"]);
                        $t = floatval(round($t*$value["quantity"],2));
                    }
                    if (!$this->sSYSTEM->sUSERGROUPDATA["tax"] && $this->sSYSTEM->sUSERGROUPDATA["id"]) {
                        $p = floatval($this->sSYSTEM->sMODULES['sArticles']->sRound(
                            $this->sSYSTEM->sMODULES['sArticles']->sRound(
                                round($value["netprice"], 2) * $value["quantity"])
                            )
                        );
                    } else {
                        $p = floatval($this->sSYSTEM->sMODULES['sArticles']->sRound(
                            $this->sSYSTEM->sMODULES['sArticles']->sRound(
                                $value["netprice"] * $value["quantity"])
                            )
                        );
                    }
                    $calcDifference = $this->sSYSTEM->sMODULES['sArticles']->sFormatPrice($t - $p);
                    $result["content"][$key]["tax"] = $calcDifference;
                }
            }
            $result = Enlight()->Events()->filter(
                'Shopware_Modules_Basket_GetBasket_FilterResult',
                $result,
                array('subject' => $this)
            );

            return $result;
        } else {
            return array();
        }
    }

    /**
     * Add product to wishlist
     * Used only in NoteController::addAction()
     *
     * @deprecated
     * @param int $articleID
     * @param string $articleName
     * @param string $articleOrderNumber
     * @return bool If operation was successful
     */
    public function sAddNote($articleID, $articleName, $articleOrderNumber)
    {
        $datum = date("Y-m-d H:i:s");

        $cookieData = $this->sSYSTEM->_COOKIE->toArray();
        if (!empty($cookieData) && empty($this->sSYSTEM->_COOKIE["sUniqueID"])) {
            $cookieId = md5(uniqid(rand()));
            setcookie("sUniqueID", $cookieId, Time()+(86400*360), '/');
            $_COOKIE["sUniqueID"] = $cookieId;
        } elseif (!empty($this->sSYSTEM->_COOKIE["sUniqueID"])) {
            $cookieId = $this->sSYSTEM->_COOKIE["sUniqueID"];
        }

        // Check if this article is already noted
        $checkForArticle = $this->sSYSTEM->sDB_CONNECTION->GetRow(
            'SELECT id FROM s_order_notes WHERE sUniqueID = ? AND ordernumber = ?',
            array($cookieId, $articleOrderNumber)
        );

        if (!$checkForArticle["id"]) {
            $queryNewPrice = $this->sSYSTEM->sDB_CONNECTION->Execute(
                'INSERT INTO s_order_notes (sUniqueID, userID ,articlename, articleID, ordernumber, datum)
                VALUES (?,?,?,?,?,?)',
                array(
                    empty($cookieId) ? $this->sSYSTEM->sSESSION_ID : $cookieId,
                    $this->sSYSTEM->_SESSION['sUserId'] ? $this->sSYSTEM->_SESSION['sUserId'] : "0" ,
                    $articleName,
                    $articleID,
                    $articleOrderNumber,
                    $datum
                )
            );

            if (!$queryNewPrice) {
                $this->sSYSTEM->E_CORE_WARNING ("sBasket##sAddNote##01","Error in SQL-query");
                return false;
            }
        }
        return true;
    }

    /**
     * Get all products current on wishlist
     * Used in AccountController and NoteController
     *
     * @deprecated
     * @return array Article notes
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

        // Reformatting data, add additional data fields to array
        foreach ($getArticles as $key => $value) {
            // Article image
            $getArticles[$key] = $this->sSYSTEM->sMODULES['sArticles']->sGetPromotionById(
                "fix",
                0,
                (int) $value["articleID"]
            );
            if (empty($getArticles[$key])) {
                $this->sDeleteNote($value["id"]);
                unset($getArticles[$key]);
                continue;
            }
            $getArticles[$key]["articlename"] = $getArticles[$key]["articleName"];
            $getArticles[$key]["image"] = $this->sSYSTEM->sMODULES['sArticles']->getArticleListingCover(
                $value["articleID"],
                Shopware()->Config()->get('forceArticleMainImageInListing')
            );
            // Links to details, basket
            $getArticles[$key]["id"] = $value["id"];
            $getArticles[$key]["linkBasket"] = $this->sSYSTEM->sCONFIG['sBASEFILE']."?sViewport=basket&sAdd=".$value["ordernumber"];
            $getArticles[$key]["linkDelete"] = $this->sSYSTEM->sCONFIG['sBASEFILE']."?sViewport=note&sDelete=".$value["id"];
            $getArticles[$key]["datum_add"] = $value["datum"];
        }
        return $getArticles;
    }

    /**
     * Returns the number of wishlist entries
     * Used in several locations
     *
     * @deprecated
     * @return int
     */
    public function sCountNotes()
    {
        $count = (int) $this->sSYSTEM->sDB_CONNECTION->GetOne('
            SELECT COUNT(*) FROM s_order_notes n, s_articles a
            WHERE (sUniqueID = ? OR (userID != 0 AND userID = ?))
            AND a.id = n.articleID AND a.active = 1
        ', array(
            empty($this->sSYSTEM->_COOKIE['sUniqueID']) ? $this->sSYSTEM->sSESSION_ID : $this->sSYSTEM->_COOKIE['sUniqueID'],
            isset($this->sSYSTEM->_SESSION['sUserId']) ? $this->sSYSTEM->_SESSION['sUserId'] : 0
        ));
        return $count;
    }

    /**
     * Delete a certain position from note
     * Used internally in sBasket and in NoteController
     *
     * @deprecated
     * @param int Id of the wihslist line
     * @return bool if the operation was successful
     */
    public function sDeleteNote($id)
    {
        $id = (int) $id;

        if (!empty($id)) {
            $sql = "
            DELETE FROM s_order_notes WHERE (sUniqueID=? OR (userID = ?  AND userID != 0)) AND id=?
            ";
            $delete = $this->sSYSTEM->sDB_CONNECTION->Execute(
                $sql,
                array(
                    $this->sSYSTEM->_COOKIE["sUniqueID"],
                    $this->sSYSTEM->_SESSION['sUserId'],
                    $id
                )
            );
            if (!$delete) {
                $this->sSYSTEM->E_CORE_WARNING("Basket sDeleteNote ##01","Could not delete item ($sql)");
            }
            return true;
        } else {
            return false;
        }
    }

    /**
     * Update quantity / price of a certain cart position
     * Used in several locations
     *
     * @deprecated
     * @param int $id Basket entry id
     * @param int $quantity Quantity
     * @return boolean
     */
    public function sUpdateArticle($id, $quantity)
    {
        $quantity = intval($quantity);
        $id = intval($id);

        if (
            Enlight()->Events()->notifyUntil(
                'Shopware_Modules_Basket_UpdateArticle_Start',
                array('subject' => $this, 'id' => $id, "quantity" => $quantity)
            )
        ) {
            return false;
        }

        if (empty($id)) {
            return false;
        }

        // Query to get minimum surcharge
        $queryAdditionalInfo = $this->sSYSTEM->sDB_CONNECTION->GetRow("
            SELECT s_articles_details.minpurchase, s_articles_details.purchasesteps,
            s_articles_details.maxpurchase, s_articles_details.purchaseunit,
            pricegroupID,pricegroupActive, s_order_basket.ordernumber, s_order_basket.articleID

            FROM s_articles, s_order_basket, s_articles_details
            WHERE s_order_basket.articleID = s_articles.id
            AND s_order_basket.ordernumber = s_articles_details.ordernumber
            AND s_order_basket.id = ?
            AND s_order_basket.sessionID = ?
            ",
            array($id, $this->sSYSTEM->sSESSION_ID)
        );

        // Check if quantity matches minimum purchase
        if (!$queryAdditionalInfo["minpurchase"]) {
            $queryAdditionalInfo["minpurchase"] = 1;
        }

        if ($quantity < $queryAdditionalInfo["minpurchase"]) {
            $quantity = $queryAdditionalInfo["minpurchase"];
        }

        // Check if quantity matches the step requirements
        if (!$queryAdditionalInfo["purchasesteps"]) {
            $queryAdditionalInfo["purchasesteps"] = 1;
        }

        if (($quantity/$queryAdditionalInfo["purchasesteps"]) != intval($quantity / $queryAdditionalInfo["purchasesteps"])) {
            $quantity = intval($quantity / $queryAdditionalInfo["purchasesteps"])*$queryAdditionalInfo["purchasesteps"];
        }

        if (empty($queryAdditionalInfo["maxpurchase"]) && !empty($this->sSYSTEM->sCONFIG['sMAXPURCHASE'])) {
            $queryAdditionalInfo["maxpurchase"] = $this->sSYSTEM->sCONFIG['sMAXPURCHASE'];
        }

        // Check if quantity matches max purchase
        if ($quantity>$queryAdditionalInfo["maxpurchase"] && !empty($queryAdditionalInfo["maxpurchase"])) {
            $quantity = $queryAdditionalInfo["maxpurchase"];
        }


        if (!$this->sSYSTEM->sSESSION_ID || !$id) {
            return false;
        }

        // Price groups
        if ($queryAdditionalInfo["pricegroupActive"]) {
            $quantitySQL = "AND s_articles_prices.from = 1 LIMIT 1";
        } else {
            $quantitySQL = "AND s_articles_prices.from <= $quantity AND (s_articles_prices.to >= $quantity OR s_articles_prices.to=0)";
        }

        // Get the order number
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

        if (!empty($queryAdditionalInfo["purchaseunit"])) {
            $queryAdditionalInfo["purchaseunit"] = 1;
        }

        $queryNewPrice = $this->sSYSTEM->sDB_CONNECTION->GetRow($sql);

        // Load prices from default group if article prices are not defined
        if (!$queryNewPrice["price"]) {
            // In the case no price is available for this customer group, use price of default customer group
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

        if (empty($queryNewPrice["price"]) && empty($queryNewPrice["config"])) {
            // If no price is set for default customer group, delete article from basket
            $this->sDeleteArticle($id);
            return false;
        }

        // Determinate tax rate for this cart position
        $taxRate = $this->sSYSTEM->sMODULES['sArticles']->getTaxRateByConditions($queryNewPrice["taxID"]);

        $netprice = $queryNewPrice["price"];

        /*
        Recalculate Price if purchase unit is set
        */
        $brutto = $this->sSYSTEM->sMODULES['sArticles']->sCalculatingPriceNum(
            $netprice,
            $queryNewPrice["tax"],
            false,
            false,
            $queryNewPrice["taxID"],
            false,
            $queryNewPrice
        );

        // Check if tax free
        if (
            ($this->sSYSTEM->sCONFIG['sARTICLESOUTPUTNETTO'] && !$this->sSYSTEM->sUSERGROUPDATA["tax"])
            || (!$this->sSYSTEM->sUSERGROUPDATA["tax"] && $this->sSYSTEM->sUSERGROUPDATA["id"])
        ) {
            // Brutto is equal to net - price
            $netprice = round($brutto, 2);

        } else {
            // Round to right value, if no purchase unit is set
            if ($queryAdditionalInfo["purchaseunit"] == 1) {
                $brutto = round($brutto, 2);
            }
            // Consider global discount for net price
            $netprice = $brutto / (100 + $taxRate) * 100;
        }

        // Recalculate price per item, if purchase unit is set
        if ($queryAdditionalInfo["purchaseunit"]>0) {
            $brutto = $brutto / $queryAdditionalInfo["purchaseunit"];
            $netprice = $netprice / $queryAdditionalInfo["purchaseunit"];
        }

        if (empty($this->sSYSTEM->sCurrency["factor"])) {
            $this->sSYSTEM->sCurrency["factor"] = 1;
        }

        if ($queryAdditionalInfo["pricegroupActive"]) {
            $brutto = $this->sSYSTEM->sMODULES['sArticles']->sGetPricegroupDiscount(
                $this->sSYSTEM->sUSERGROUP,
                $queryAdditionalInfo["pricegroupID"],
                $brutto,
                $quantity,
                false
            );
            $brutto = $this->sSYSTEM->sMODULES['sArticles']->sRound($brutto);
            if (
                ($this->sSYSTEM->sCONFIG['sARTICLESOUTPUTNETTO'] && !$this->sSYSTEM->sUSERGROUPDATA["tax"]) ||
                (!$this->sSYSTEM->sUSERGROUPDATA["tax"] && $this->sSYSTEM->sUSERGROUPDATA["id"])
            ) {
                $netprice = $this->sSYSTEM->sMODULES['sArticles']->sRound(
                    $this->sSYSTEM->sMODULES['sArticles']->sGetPricegroupDiscount(
                        $this->sSYSTEM->sUSERGROUP,
                        $queryAdditionalInfo["pricegroupID"],
                        $netprice,
                        $quantity,
                        false
                    )
                );

            } else {
                $netprice = $brutto / (100 + $taxRate)* 100;
                $netprice = number_format($netprice, 3, ".", "");
            }
        }

        $sql = "
            UPDATE s_order_basket
            SET quantity = ?, price = ?, netprice = ?, currencyFactor = ?, tax_rate = ?
            WHERE id = ? AND sessionID = ? AND modus = 0
            ";
        $sql = Enlight()->Events()->filter(
            'Shopware_Modules_Basket_UpdateArticle_FilterSqlDefault',
            $sql, 
            array(
                'subject' => $this,
                'id' => $id,
                "quantity" => $quantity,
                "price" => $brutto,
                "netprice" => $netprice,
                "currencyFactor" => $this->sSYSTEM->sCurrency["factor"]
            )
        );

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

        $update = $this->sSYSTEM->sDB_CONNECTION->Execute($sql, $params);

        $this->sUpdateVoucher();

        if (!$update || !$queryNewPrice) {
            $this->sSYSTEM->E_CORE_WARNING("Basket Update ##01","Could not update quantity".$sql);
        }
    }

    /**
     * Check if the current basket has any ESD article
     * Used in sAdmin and CheckoutController
     * 
     * @deprecated
     * @return bool If an ESD article is present in the current basket
     */
    public function sCheckForESD()
    {
        $getArticles = $this->sSYSTEM->sDB_CONNECTION->GetRow(
            'SELECT id FROM s_order_basket WHERE sessionID = ? AND esdarticle = 1 LIMIT 1;',
            array($this->sSYSTEM->sSESSION_ID)
        );

        return (bool) $getArticles["id"];
    }

    /**
     * Truncate cart
     * Used on sAdmin tests and SwagBonusSystem
     * See @ticket PT-1845
     * 
     * @deprecated
     * @return void|false False on no session, null otherwise
     */
    public function sDeleteBasket()
    {
        if (empty($this->sSYSTEM->sSESSION_ID)) {
            return false;
        }

        $this->sSYSTEM->sDB_CONNECTION->Execute(
            'DELETE FROM s_order_basket WHERE sessionID = ?',
            array($this->sSYSTEM->sSESSION_ID)
        );
    }


    /**
     * Delete a certain position from the basket
     * Used in multiple locations
     *
     * @deprecated
     * @param int Id of the basket line
     * @return null
     */
    public function sDeleteArticle($id)
    {
        $id = (int) $id;
        $modus = $this->sSYSTEM->sDB_CONNECTION->GetOne(
            'SELECT `modus` FROM `s_order_basket` WHERE `id`=?',
            array($id)
        );

        if ($id && $id != "voucher") {
            $sql = 'DELETE FROM s_order_basket WHERE sessionID=? AND id=?';
            $delete = $this->sSYSTEM->sDB_CONNECTION->Execute(
                $sql,
                array($this->sSYSTEM->sSESSION_ID, $id)
            );
            if (!$delete) {
                $this->sSYSTEM->E_CORE_WARNING("Basket Delete ##01","Could not delete item ($sql)");
            }
            if (empty($modus)) {
                $this->sUpdateVoucher();
            }
        }
    }

    /**
     * Add product to cart
     * Used in multiple locations
     *
     * @deprecated
     * @param int $id Order number (s_articles_details.ordernumber)
     * @param int $quantity Amount
     * @return int|false Id of the inserted basket entry, or false on failure
     */
    public function sAddArticle($id, $quantity = 1)
    {
        if ($this->sSYSTEM->sBotSession || empty($this->sSYSTEM->sSESSION_ID)) {
            return false;
        }

        $quantity = (empty($quantity) || !is_numeric($quantity)) ? 1 : (int) $quantity;
        if ($quantity <= 0) {
            $quantity = 1;
        }


        if (
            Enlight()->Events()->notifyUntil(
                'Shopware_Modules_Basket_AddArticle_Start',
                array(
                    'subject' => $this,
                    'id' => $id,
                    "quantity" => $quantity
                )
            )
        ) {
            return false;
        }

        $sql = "
            SELECT s_articles.id AS articleID, name AS articleName, taxID,
            additionaltext, s_articles_details.shippingfree, laststock, instock,
            s_articles_details.id as articledetailsID, ordernumber
            FROM s_articles, s_articles_details
            WHERE s_articles_details.ordernumber = ?
            AND s_articles_details.articleID = s_articles.id
            AND s_articles.active = 1
            AND (
                SELECT articleID
                FROM s_articles_avoid_customergroups
                WHERE articleID = s_articles.id AND customergroupID = ".$this->sSYSTEM->sUSERGROUPDATA["id"]."
            ) IS NULL
        ";
        $getArticle = $this->sSYSTEM->sDB_CONNECTION->GetRow($sql, array($id));

        $getName = $this->sSYSTEM->sMODULES["sArticles"]->sGetArticleNameByOrderNumber(
            $getArticle["ordernumber"],
            true
        );

        if (!empty($getName)) {
            $getArticle["articleName"] = $getName["articleName"];
            $getArticle["additionaltext"] = $getName["additionaltext"];
        }

        if (!count($getArticle)) {
            return false;
        } else {
            // Check if article is already in basket
            $chkBasketForArticle = $this->sSYSTEM->sDB_CONNECTION->GetRow(
                'SELECT id, quantity
                FROM s_order_basket
                WHERE articleID=? AND sessionID=? AND ordernumber = ?',
                array(
                    $getArticle["articleID"],
                    $this->sSYSTEM->sSESSION_ID,
                    $getArticle["ordernumber"]
                )
            );

            // Shopware 3.5.0 / sth / laststock - instock check
            if (!empty($chkBasketForArticle["id"])) {

                if (
                    $getArticle["laststock"] == true
                    && $getArticle["instock"] < ($chkBasketForArticle["quantity"] + $quantity)
                ) {
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

            $insertTime = date("Y-m-d H:i:s");

            if ($chkBasketForArticle && empty($sUpPriceValues)) {
                // Article is already in basket, update quantity
                $quantity += $chkBasketForArticle["quantity"];

                $this->sUpdateArticle($chkBasketForArticle["id"], $quantity);
                return $chkBasketForArticle["id"];
            } else {
                // Read price from default-price-table
                $sql = "SELECT price, s_core_tax.tax AS tax
                FROM s_articles_prices, s_core_tax
                WHERE s_articles_prices.pricegroup = ?
                AND s_articles_prices.articledetailsID = ?
                AND s_core_tax.id=?
                ";
                $getPrice = $this->sSYSTEM->sDB_CONNECTION->GetRow(
                    $sql,
                    array(
                        $this->sSYSTEM->sUSERGROUP,
                        $getArticle["articledetailsID"],
                        $getArticle["taxID"]
                    )
                );

                if (empty($getPrice["price"])) {
                    $sql = "SELECT price,s_core_tax.tax AS tax FROM s_articles_prices,s_core_tax WHERE
                    s_articles_prices.pricegroup='EK'
                    AND s_articles_prices.articledetailsID=?
                    AND s_core_tax.id=?
                    ";
                    $getPrice = $this->sSYSTEM->sDB_CONNECTION->GetRow(
                        $sql,
                        array($getArticle["articledetailsID"], $getArticle["taxID"])
                    );
                }


                if (!$getPrice["price"] && !$getArticle["free"]) {
                    // No price could acquired
                    $this->sSYSTEM->E_CORE_WARNING("BASKET-INSERT #01","No price acquired");
                    return;
                } else {

                    // If configuration article
                    if (
                        ($this->sSYSTEM->sCONFIG['sARTICLESOUTPUTNETTO'] && !$this->sSYSTEM->sUSERGROUPDATA["tax"])
                        || (!$this->sSYSTEM->sUSERGROUPDATA["tax"] && $this->sSYSTEM->sUSERGROUPDATA["id"])
                    ) {
                        // If netto set both values to net-price
                        $getPrice["price"] = $this->sSYSTEM->sMODULES['sArticles']->sCalculatingPriceNum(
                            $getPrice["price"],
                            $getPrice["tax"],
                            false,
                            false,
                            $getArticle["taxID"],
                            false,
                            $getArticle
                        );
                        $getPrice["netprice"] = $getPrice["price"];
                    } else {
                        // If brutto, save net
                        $getPrice["netprice"] = $getPrice["price"];
                        $getPrice["price"] = $this->sSYSTEM->sMODULES['sArticles']->sCalculatingPriceNum(
                            $getPrice["price"],
                            $getPrice["tax"],
                            false,
                            false,
                            $getArticle["taxID"],
                            false,
                            $getArticle
                        );
                    }
                    // For variants, extend the article-name
                    if ($getArticle["additionaltext"]) {
                        $getArticle["articleName"].= " ".$getArticle["additionaltext"];
                    }

                    if (!$getArticle["shippingfree"]) {
                        $getArticle["shippingfree"] = "0";
                    }

                    // Check if article is an esd-article
                    // - add flag to basket
                    $sqlGetEsd = "
                    SELECT s_articles_esd.id AS id, serials
                    FROM s_articles_esd, s_articles_details
                    WHERE s_articles_esd.articleID = ?
                    AND s_articles_esd.articledetailsID = s_articles_details.id
                    AND s_articles_details.ordernumber = ?
                    ";
                    $getEsd = $this->sSYSTEM->sDB_CONNECTION->GetRow(
                        $sqlGetEsd,
                        array($getArticle["articleID"], $getArticle["ordernumber"])
                    );

                    if ($getEsd["id"]) {
                        $sEsd = "1";
                    } else {
                        $sEsd = "0";
                    }

                    $quantity = (int) $quantity;
                    $sql = "
                        INSERT INTO s_order_basket (id, sessionID, userID, articlename, articleID,
                            ordernumber, shippingfree, quantity, price, netprice,
                            datum, esdarticle, partnerID, config)
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

                    $sql = Enlight()->Events()->filter(
                        'Shopware_Modules_Basket_AddArticle_FilterSql',
                        $sql,
                        array(
                            'subject' => $this,
                            'article' => $getArticle,
                            'price' => $getPrice,
                            'esd' => $sEsd,
                            'quantity' => $quantity,
                            'partner' => $this->sSYSTEM->_SESSION['sPartner']
                        )
                    );

                    $rs = $this->sSYSTEM->sDB_CONNECTION->Execute($sql, $params);

                    if (!$rs) {
                        $this->sSYSTEM->E_CORE_WARNING ("BASKET-INSERT #02", "SQL-Error".$sql);
                    }
                    $insertId = $this->sSYSTEM->sDB_CONNECTION->Insert_ID();

                    $sql = "
                        INSERT INTO s_order_basket_attributes (basketID, attribute1)
                        VALUES (". $insertId .",
                        ".$this->sSYSTEM->sDB_CONNECTION->qstr(implode($pictureRelations,"$$")).")";
                    $this->sSYSTEM->sDB_CONNECTION->Execute($sql);

                    $this->sUpdateArticle($insertId, $quantity);

                } // If - Price was found
            } // If - Article is not in basket
        } // If - Article was found

        return $insertId;
    }

    /**
     * Refresh basket after login / currency change
     * Used in multiple locations
     *
     * @deprecated
     * @return null
     */
    public function sRefreshBasket()
    {
        $session = Shopware()->Session();
        $admin = Shopware()->Modules()->Admin();

        // Update basket data
        $admin->sGetUserData();
        $this->sGetBasket();
        $admin->sGetPremiumShippingcosts();

        // Update basket data in session
        $session->sBasketCurrency = Shopware()->Shop()->getCurrency()->getId();
        $session->sBasketQuantity = $this->sCountBasket();
        $amount = $this->sGetAmount();
        $session->sBasketAmount = empty($amount) ? 0 : array_shift($amount);
    }
}
