<?php

/**
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 *
 * @package pi_ratepay_rate_calculator
 * Code by PayIntelligent GmbH  <http://www.payintelligent.de/>
 */
require_once "PiRatepayRateCalcDataInterface.php";

/**
 * Developer needs to specify how the Calculator gets the Data
 */
class PiRatepayRateCalcData implements PiRatepayRateCalcDataInterface
{
    /**
     * This method get the RatePAY profile-id and has to be rewritten
     *
     * @return string
     */
    public function getProfileId() {
        return Shopware()->Plugins()->Frontend()->PigmbhRatePAYPayment()->Config()->profile_id_rate;
    }

    /**
     * This method get the RatePAY security-code and has to be rewritten
     * If you only have the hashed security-code, return an empty string.
     *
     * @return string
     */
    public function getSecurityCode() {
            return '';
    }

    /**
     * This method get the security-code md5 hashed and has to be rewritten
     * If you only have the non hashed security-code, return an empty string.
     *
     * @return string
     */
    public function getSecurityCodeHashed()
    {
        return  Shopware()->Plugins()->Frontend()->PigmbhRatePAYPayment()->Config()->security_code_rate;
    }

    /**
     * This method get the status live or sandbox and has to be rewritten
     *
     * @return boolean
     */
    public function isLive() {
        return !Shopware()->Plugins()->Frontend()->PigmbhRatePAYPayment()->Config()->sandbox_mode_rate;
    }

    /**
     * This method get the transaction-id and has to be rewritten
     * Optional - Return Empty String - If empty String, it will not be sended to RatePAY.
     *
     * @return string
     */
    public function getTransactionId(){
            return Shopware()->Session()->pi_ratepay_transactionID;
    }

    /**
     * This method get the transaction-short-id and has to be rewritten
     * Optional - Return Empty String - If empty String, it will not be sended to RatePAY.
     *
     * @return string
     */
    public function getTransactionShortId(){
            return  Shopware()->Session()->pi_ratepay_transactionShortID ;
    }

    /**
     * This method get the order-id and has to be rewritten
     * Optional - Return Empty String - If empty String, it will not be sended to RatePAY.
     *
     * @return string
     */
    public function getOrderId() {
            return '';
    }

    /**
     * This method get the merchant-consumer-id and has to be rewritten
     * Optional - Return Empty String - If empty String, it will not be sended to RatePAY.
     *
     * @return string
     */
    public function getMerchantConsumerId() {
            return '';
    }

    /**
     * This method get the merchant-cosnumer-classification and has to be rewritten
     * Optional - Return Empty String - If empty String, it will not be sended to RatePAY.
     *
     * @return string
     */
    public function getMerchantConsumerClassification() {
            return '';
    }

    /**
     * This method get the total basket amount and has to be rewritten
     *
     * @return string
     */
    public function getAmount() {
            $basket_amount = Shopware()->Session()->pi_ratepay_Warenkorbbetrag;
            return $basket_amount;
    }

    /**
     * This method get the config of payment firstday
     * Optional - Return false - If false, it will not be displayed
     *
     * @return bool
     */
    public function getPaymentFirstdayConfig(){        
            return Shopware()->Plugins()->Frontend()->PigmbhRatePAYPayment()->Config()->payment_firstday_rate;
    }

    /**
     * This method get the selected languange and has to be rewritten
     * return DE for German Calculator. Everything else will be English.
     *
     * @return string
     */
    public function getLanguage() {
            return 'DE';
    }

    /**
     * This method get the interest rate and has to be rewritten
     * return '' for default.
     *
     * @return string
     */
    public function getInterestRate() {
            return '';
    }

    /**
     * This method set all needed data and has to be rewritten
     * It is for internal Shop usage, like saving the variables in the DB or session etc.
     *
     * @param string $total_amount
     * @param string $amount
     * @param string $interest_amount
     * @param string $service_charge
     * @param string $annual_percentage_rate
     * @param string $monthly_debit_interest
     * @param string $number_of_rates
     * @param string $rate
     * @param string $last_rate
     * @param string $payment_firstday
     */
    public function setData($total_amount,$amount,$interest_rate,$interest_amount,$service_charge,$annual_percentage_rate,$monthly_debit_interest,$number_of_rates,$rate,$last_rate, $payment_firstday){
        /*Saving Data as example in the Session*/
        Shopware()->Session()->pi_ratepay_total_amount = $total_amount;
        Shopware()->Session()->pi_ratepay_amount = $amount;
        Shopware()->Session()->pi_ratepay_interest_rate = $interest_rate;
        Shopware()->Session()->pi_ratepay_interest_amount = $interest_amount;
        Shopware()->Session()->pi_ratepay_service_charge = $service_charge;
        Shopware()->Session()->pi_ratepay_annual_percentage_rate = $annual_percentage_rate;
        Shopware()->Session()->pi_ratepay_monthly_debit_interest = $monthly_debit_interest;
        Shopware()->Session()->pi_ratepay_number_of_rates = $number_of_rates;
        Shopware()->Session()->pi_ratepay_rate = $rate;
        Shopware()->Session()->pi_ratepay_last_rate = $last_rate;
        Shopware()->Session()->dueDate = $payment_firstday;
    }

    /**
     * This method get all needed data and has to be rewritten
     * Optional - Will only be used, if you want to show the result on another page (include_result.html)
     * Needs to return an array with the indexes total_amount, amount, interest_rate,interest_amount, service_charge, annual_percentage_rate, monthly_debit_interest, number_of_rates , rate, last_rate
     *
     * @return array
     */
    public function getData() {
        /*Getting Data as example from the Session*/
        return array('total_amount' => Shopware()->Session()->pi_ratepay_total_amount,
            'amount' => Shopware()->Session()->pi_ratepay_amount,
            'interest_rate' => Shopware()->Session()->pi_ratepay_interest_rate,
            'interest_amount' => Shopware()->Session()->pi_ratepay_interest_amount,
            'service_charge' => Shopware()->Session()->pi_ratepay_service_charge,
            'annual_percentage_rate' => Shopware()->Session()->pi_ratepay_annual_percentage_rate,
            'monthly_debit_interest' => Shopware()->Session()->pi_ratepay_monthly_debit_interest,
            'number_of_rates' => Shopware()->Session()->pi_ratepay_number_of_rates,
            'rate' => Shopware()->Session()->pi_ratepay_rate,
            'lastRate' => Shopware()->Session()->pi_ratepay_last_rate
        );
    }

    /**
     * This method unset the Data and has to be rewritten
     */
    public function unsetData() {
        /*Unsetting the Session Variables as example*/
        unset(Shopware()->Session()->pi_ratepay_total_amount);
        unset(Shopware()->Session()->pi_ratepay_amount);
        unset(Shopware()->Session()->pi_ratepay_interest_rate);
        unset(Shopware()->Session()->pi_ratepay_interest_amount);
        unset(Shopware()->Session()->pi_ratepay_service_charge);
        unset(Shopware()->Session()->pi_ratepay_annual_percentage_rate);
        unset(Shopware()->Session()->pi_ratepay_monthly_debit_interest);
        unset(Shopware()->Session()->pi_ratepay_number_of_rates);
        unset(Shopware()->Session()->pi_ratepay_rate);
        unset(Shopware()->Session()->pi_ratepay_last_rate);
        unset(Shopware()->Session()->dueDate);
    }

    /**
     * Specify how to get Data from GET
     *
     * @param string $var
     * @return string
     */
     public function getGetParameter($var) {
         return Shopware()->Front()->Request()->getQuery($var, '');
    }

    /**
     * Specify how to get Data from POST
     *
     * @param string $var
     * @return string
     */
     public function getPostParameter($var) {
         return Shopware()->Front()->Request()->getPost($var, '');
     }
}