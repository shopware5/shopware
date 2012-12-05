<?php

/**
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 *
 * @package pi_ratepay_rate_calculator
 * Code by PayIntelligent GmbH  <http://www.payintelligent.de/>
 */

/**
 * Connection between System and Calculator
 */
class PiRatepayRateCalcBase
{

    //Request Informations
    private $request_profile_id;
    private $request_security_code;
    private $request_transaction_id;
    private $request_transaction_short_id;
    private $request_order_id;
    private $request_merchant_consumer_id;
    private $request_merchant_consumer_classification;
    private $request_operation;
    private $request_operation_subtype;
    private $request_amount;
    private $request_due_date;
    private $request_calculation_value;
    private $request_interest_rate;
    private $request_payment_firstday;
    private $live;
    //Installment Config
    private $config_interestrate_min;
    private $config_intrestrate_default;
    private $config_intrestrate_max;
    private $config_month_number_min;
    private $config_month_number_max;
    private $config_month_longrun;
    private $config_month_allowed;
    private $config_payment_firstday;
    private $config_payment_amount;
    private $config_payment_lastrate;
    private $config_rate_min_normal;
    private $config_rate_min_longrun;
    private $config_service_charge;
    //Installment Details
    private $details_total_amount;
    private $details_amount;
    private $details_interest_amount;
    private $details_service_charge;
    private $details_annual_percentage_rate;
    private $details_monthly_debit_interest;
    private $details_number_of_rates;
    private $details_rate;
    private $details_last_rate;
    private $details_payment_firstday;
    //Others
    private $language;
    private $request_error_msg;
    private $request_msg;
    private $request_code;

    private $picaldata;

    /**
     * Constructer of PiRatepayRateCalcBase
     */
    public function PiRatepayRateCalcBase()
    {
        require_once 'PiRatepayRateCalcData.php';
        $this->picalcdata = new PiRatepayRateCalcData();

        $this->request_profile_id = $this->picalcdata->getProfileId();

        if ($this->picalcdata->getSecurityCode() == '') {
            $this->request_security_code = $this->picalcdata->getSecurityCodeHashed();
        } else {
            $this->request_security_code = md5($this->picalcdata->getSecurityCode());
        }

        $this->request_transaction_id = $this->picalcdata->getTransactionId();
        $this->request_transaction_short_id = $this->picalcdata->getTransactionShortId();
        $this->request_order_id = $this->picalcdata->getOrderId();
        $this->request_merchant_consumer_id = $this->picalcdata->getMerchantConsumerId();
        $this->request_merchant_consumer_classification = $this->picalcdata->getMerchantConsumerClassification();
        $this->request_amount = $this->picalcdata->getAmount();
        $this->request_live = $this->picalcdata->isLive();

        $this->language = $this->picalcdata->getLanguage();

        $this->request_operation = '';
        $this->request_operation_subtype = '';
        $this->request_calculation_value = '';
        $this->request_due_date = '';
        $this->request_payment_firstday = $this->picalcdata->getPaymentFirstdayConfig();
        $this->request_interest_rate = $this->picalcdata->getInterestRate();

        $this->config_interestrate_min = '';
        $this->config_intrestrate_default = '';
        $this->config_intrestrate_max = '';
        $this->config_month_number_min = '';
        $this->config_month_number_max = '';
        $this->config_month_longrun = '';
        $this->config_month_allowed = '';
        $this->config_payment_firstday = '';
        $this->config_payment_amount = '';
        $this->config_payment_lastrate = '';
        $this->config_rate_min_normal = '';
        $this->config_rate_min_longrun = '';
        $this->config_service_charge = '';

        $this->details_total_amount = '';
        $this->details_amount = '';
        $this->details_interest_rate = '';
        $this->details_interest_amount = '';
        $this->details_service_charge = '';
        $this->details_annual_percentage_rate = '';
        $this->details_monthly_debit_interest = '';
        $this->details_number_of_rates = '';
        $this->details_rate = '';
        $this->details_last_rate = '';
    }

    /*     * ***REQUEST SETTER**** */

    /**
     * This method set the request operation
     *
     * @param string $operation
     */
    public function setRequestOperation($operation)
    {
        $this->request_operation = $operation;
    }

    /**
     * This method set the request operation-subtype
     *
     * @param string $operation_subtype
     */
    public function setRequestOperationSubtype($operation_subtype)
    {
        $this->request_operation_subtype = $operation_subtype;
    }

    /**
     * This method set the request calculation-value
     *
     * @param float $calculation_value
     */
    public function setRequestCalculationValue($calculation_value)
    {
        $this->request_calculation_value = $calculation_value;
    }

    /**
     * This method set the request due day
     *
     * @param float $due_day
     */
    public function setRequestDueDay($due_day)
    {
        $this->request_due_date = $due_day;
    }

    /**
     * This method set the request interest-rate
     *
     * @param float $interest_rate
     */
    public function setRequestInterestRate($interest_rate)
    {
        $this->request_interest_rate = $interest_rate;
    }

    /*     * ***CONFIG SETTER**** */

    /**
     * This method set the config interest-rate-mim
     *
     * @param float $interest_rate_min
     */
    protected function setConfigInterestRateMin($interest_rate_min)
    {
        $this->config_interestrate_min = $interest_rate_min;
    }

    /**
     * This method set the config interest-rate-default
     *
     * @param float $interest_rate_default
     */
    protected function setConfigInterestRateDefault($interest_rate_default)
    {
        $this->config_interestrate_default = $interest_rate_default;
    }

    /**
     * This method set the config interest-rate-max
     *
     * @param float $interest_rate_max
     */
    protected function setConfigInterestRateMax($interest_rate_max)
    {
        $this->config_interestrate_max = $interest_rate_max;
    }

    /**
     * This method set the config month-number-min
     *
     * @param int $month_number_min
     */
    protected function setConfigMonthNumberMin($month_number_min)
    {
        $this->config_month_number_min = $month_number_min;
    }

    /**
     * This method set the config month-number-max
     *
     * @param int $month_number_max
     */
    protected function setConfigMonthNumberMax($month_number_max)
    {
        $this->config_month_number_max = $month_number_max;
    }

    /**
     * This method set the config month-longrun
     *
     * @param int $month_longrun
     */
    protected function setConfigMonthLongrun($month_longrun)
    {
        $this->config_month_longrun = $month_longrun;
    }

    /**
     * This method set the config month-allowed
     *
     * @param int $month_allowed
     */
    protected function setConfigMonthAllowed($month_allowed)
    {
        $this->config_month_allowed = $month_allowed;
    }

    /**
     * This method set the config payment-firstday
     *
     * @param int $payment_firstday
     */
    protected function setConfigPaymentFirstday($payment_firstday)
    {
        $this->config_payment_firstday = $payment_firstday;
    }

    /**
     * This method set the config payment-amount
     *
     * @param float $payment_amount
     */
    protected function setConfigPaymentAmount($payment_amount)
    {
        $this->config_payment_amount = $payment_amount;
    }

    /**
     * This method set the config payment-lastrate
     *
     * @param float $payment_lastrate
     */
    protected function setConfigPaymentLastrate($payment_lastrate)
    {
        $this->config_payment_lastrate = $payment_lastrate;
    }

    /**
     * This method set the config rate-min-normal
     *
     * @param float $rate_min_normal
     */
    protected function setConfigRateMinNormal($rate_min_normal)
    {
        $this->config_rate_min_normal = $rate_min_normal;
    }

    /**
     * This method set the config rate-min-longrun
     *
     * @param float $rate_min_longrun
     */
    protected function setConfigRateMinLongrun($rate_min_longrun)
    {
        $this->config_rate_min_longrun = $rate_min_longrun;
    }

    /**
     * This method set the config service-charge
     *
     * @param float $service_charge
     */
    protected function setConfigServiceCharge($service_charge)
    {
        $this->config_service_charge = $service_charge;
    }

    /*     * ***DETAILS SETTER**** */

    /**
     * This method set the details total-amount
     *
     * @param float $total_amount
     */
    protected function setDetailsTotalAmount($total_amount)
    {
        $this->details_total_amount = $total_amount;
    }

    /**
     * This method set the details amount
     *
     * @param float $amount
     */
    protected function setDetailsAmount($amount)
    {
        $this->details_amount = $amount;
    }

    /**
     * This method set the details interest-amount
     *
     * @param float $interest_amount
     */
    protected function setDetailsInterestAmount($interest_amount)
    {
        $this->details_interest_amount = $interest_amount;
    }

    /**
     * This method set the details service-charge
     *
     * @param float $service_charge
     */
    protected function setDetailsServiceCharge($service_charge)
    {
        $this->details_service_charge = $service_charge;
    }

    /**
     * This method set the details annual-percentage-rate
     *
     * @param float $annual_percentage_rate
     */
    protected function setDetailsAnnualPercentageRate($annual_percentage_rate)
    {
        $this->details_annual_percentage_rate = $annual_percentage_rate;
    }

    /**
     * This method set the details monthly-debit-interest
     *
     * @param float $monthly_debit_interest
     */
    protected function setDetailsMonthlyDebitInterest($monthly_debit_interest)
    {
        $this->details_monthly_debit_interest = $monthly_debit_interest;
    }

    /**
     * This method set the details number-of-rates
     *
     * @param int $number_of_rates
     */
    protected function setDetailsNumberOfRates($number_of_rates)
    {
        $this->details_number_of_rates = $number_of_rates;
    }

    /**
     * This method set the details rates
     *
     * @param int $rate
     */
    protected function setDetailsRate($rate)
    {
        $this->details_rate = $rate;
    }

    /**
     * This method set the details last-rates
     *
     * @param float $last_rate
     */
    protected function setDetailsLastRate($last_rate)
    {
        $this->details_last_rate = $last_rate;
    }

    /**
     * This method set the details payment-firstday
     *
     * @param int $payment_firstday
     */
    protected function setDetailsPaymentFirstday($payment_firstday)
    {
        $this->details_payment_firstday = $payment_firstday;
    }

    /**
     * This method set the details interest-rates
     *
     * @param float $interest_rate
     */
    protected function setDetailsInterestRate($interest_rate)
    {
        $this->details_interest_rate = $interest_rate;
    }

    /*     * ***OTHERS GETTER***** */

    /**
     * This method set a error message
     *
     * @param string $request_error_msg
     */
    public function setErrorMsg($request_error_msg)
    {
        $this->request_error_msg = $request_error_msg;
    }

    /**
     * This method set a message
     *
     * @param string $request_msg
     */
    public function setMsg($request_msg)
    {
        $this->request_msg = $request_msg;
    }

    /**
     * This method set a code
     *
     * @param string $request_code
     */
    public function setCode($request_code)
    {
        $this->request_code = $request_code;
    }

    /*     * ***REQUEST GETTER**** */

    /**
     * This method get the system-id
     *
     * @return string $systemId
     */
    public function getRequestSystemID()
    {
        $systemId = $_SERVER['SERVER_ADDR'];
        return $systemId;
    }

    /**
     * This method get the request profile-id
     *
     * @return string $this->request_profile_id
     */
    public function getRequestProfileId()
    {
        return $this->request_profile_id;
    }

    /**
     * This method get the request security-code
     *
     * @return string $this->request_security_code
     */
    public function getRequestSecurityCode()
    {
        return $this->request_security_code;
    }

    /**
     * This method get the request transaction-id
     *
     * @return string $this->request_transaction_id
     */
    public function getRequestTransactionId()
    {
        return $this->request_transaction_id;
    }

    /**
     * This method get the request transaction-short-id
     *
     * @return string $this->request_transaction_short_id
     */
    public function getRequestTransactionShortId()
    {
        return $this->request_transaction_short_id;
    }

    /**
     * This method get the config for payment firstday
     *
     * @return string $this->request_payment_firstday
     */
    public function getRequestFirstday()
    {
        return $this->request_payment_firstday;
    }

    /**
     * This method get the request order-id
     *
     * @return string $this->request_order_id
     */
    public function getRequestOrderId()
    {
        return $this->request_order_id;
    }

    /**
     * This method get the request merchant-consumer-id
     *
     * @return string $this->request_merchant_consumer_id
     */
    public function getRequestMerchantConsumerId()
    {
        return $this->request_merchant_consumer_id;
    }

    /**
     * This method get the request merchant-consumer-classification
     *
     * @return string $this->request_merchant_consumer_classification
     */
    public function getRequestMerchantConsumerClassification()
    {
        return $this->request_merchant_consumer_classification;
    }

    /**
     * This method get the request operation
     *
     * @return string $this->request_operation
     */
    public function getRequestOperation()
    {
        return $this->request_operation;
    }

    /**
     * This method get the request operation-subtype
     *
     * @return string $this->request_operation_subtype
     */
    public function getRequestOperationSubtype()
    {
        return $this->request_operation_subtype;
    }

    /**
     * This method get the request amount
     *
     * @return float $this->request_amount
     */
    public function getRequestAmount()
    {
        return $this->request_amount;
    }

    /**
     * This method get the request calculation value
     *
     * @return float $this->request_calculation_value
     */
    public function getRequestCalculationValue()
    {
        return $this->request_calculation_value;
    }

    /**
     * This method get the request calculation value
     *
     * @return float $this->request_calculation_value
     */
    public function getRequestDueDate()
    {
        return $this->request_due_date;
    }

    /**
     * This method get the request interest rate
     *
     * @return float $this->request_interest_rate
     */
    public function getRequestInterestRate()
    {
        return $this->request_interest_rate;
    }

    /**
     * This method get the status live or sandbox
     *
     * @return boolean $this->request_live
     */
    public function getLive()
    {
        return $this->request_live;
    }

    /*     * ***CONFIG GETTER**** */

    /**
     * This method get the config interest-rate-min
     *
     * @return float $this->config_interestrate_min
     */
    public function getConfigInterestRateMin()
    {
        return $this->config_interestrate_min;
    }

    /**
     * This method get the config interest-rate-default
     *
     * @return float $this->config_interestrate_default
     */
    public function getConfigInterestRateDefault()
    {
        return $this->config_interestrate_default;
    }

    /**
     * This method get the config interest-rate-max
     *
     * @return float $this->config_interestrate_max
     */
    public function getConfigInterestRateMax()
    {
        return $this->config_interestrate_max;
    }

    /**
     * This method get the config month-number-min
     *
     * @return float $this->config_month_number_min
     */
    public function getConfigMonthNumberMin()
    {
        return $this->config_month_number_min;
    }

    /**
     * This method get the config interest-rate-max
     *
     * @return float $this->config_month_number_max
     */
    public function getConfigMonthNumberMax()
    {
        return $this->config_month_number_max;
    }

    /**
     * This method get the config config-month-longrun
     *
     * @return float $this->config_month_longrun
     */
    public function getConfigMonthLongrun()
    {
        return $this->config_month_longrun;
    }

    /**
     * This method get the config config-month-allowed
     *
     * @return int $this->config_month_allowed
     */
    public function getConfigMonthAllowed()
    {
        return $this->config_month_allowed;
    }

    /**
     * This method get the config config-payment-firstday
     *
     * @return float $this->config_payment_firstday
     */
    public function getConfigPaymentFirstday()
    {
        return $this->config_payment_firstday;
    }

    /**
     * This method get the config config-payment-amount
     *
     * @return float $this->config_payment_amount
     */
    public function getConfigPaymentAmount()
    {
        return $this->config_payment_amount;
    }

    /**
     * This method get the config config-payment-lastrate
     *
     * @return float $this->config_payment_lastrate
     */
    public function getConfigPaymentLastrate()
    {
        return $this->config_payment_lastrate;
    }

    /**
     * This method get the config rate-min-normal
     *
     * @return float $this->config_rate_min_normal
     */
    public function getConfigRateMinNormal()
    {
        return $this->config_rate_min_normal;
    }

    /**
     * This method get the config rate-min-longrun
     *
     * @return float $this->config_rate_min_longrun
     */
    public function getConfigRateMinLongrun()
    {
        return $this->config_rate_min_longrun;
    }

    /**
     * This method get the config service-charge
     *
     * @return float $this->config_service_charge
     */
    public function getConfigServiceCharge()
    {
        return $this->config_service_charge;
    }

    /*     * ***DETAILS GETTER**** */

    /**
     * This method get the details total-amount
     *
     * @return float $this->details_total_amount
     */
    public function getDetailsTotalAmount()
    {
        return $this->details_total_amount;
    }

    /**
     * This method get the details details-amount
     *
     * @return float $this->details_amount
     */
    public function getDetailsAmount()
    {
        return $this->details_amount;
    }

    /**
     * This method get the details details interest-amount
     *
     * @return float $this->details_interest_amount
     */
    public function getDetailsInterestAmount()
    {
        return $this->details_interest_amount;
    }

    /**
     * This method get the details details-service-charge
     *
     * @return float $this->details_service_charge
     */
    public function getDetailsServiceCharge()
    {
        return $this->details_service_charge;
    }

    /**
     * This method get the details details annual-percentage-rate
     *
     * @return float $this->details_annual_percentage_rate
     */
    public function getDetailsAnnualPercentageRate()
    {
        return $this->details_annual_percentage_rate;
    }

    /**
     * This method get the details details monthly-debit-interest
     *
     * @return float $this->details_monthly_debit_interest
     */
    public function getDetailsMonthlyDebitInterest()
    {
        return $this->details_monthly_debit_interest;
    }

    /**
     * This method get the details details number-of-rate
     *
     * @return int $this->details_number_of_rates
     */
    public function getDetailsNumberOfRates()
    {
        return $this->details_number_of_rates;
    }

    /**
     * This method get the details details rate
     *
     * @return float $this->details_rate
     */
    public function getDetailsRate()
    {
        return $this->details_rate;
    }

    /**
     * This method get the details details last-rate
     *
     * @return float $this->details_last_rate
     */
    public function getDetailsLastRate()
    {
        return $this->details_last_rate;
    }

    /**
     * This method get the details details interest-rate
     *
     * @return float $this->details_interest_rate
     */
    public function getDetailsInterestRate()
    {
        return $this->details_interest_rate;
    }

    /**
     * This method gets the details payment-firstday
     *
     * @return int $this->details_payment_firstday
     */
    public function getDetailsPaymentFirstday()
    {
       return $this->details_payment_firstday;
    }

    /*     * ****OTHERS GETTER***** */

    /**
     * This method get the selected languange
     *
     * @return string $this->language
     */
    public function getLanguage()
    {
        return $this->language;
    }

    /**
     * This method get the error message
     *
     * @return string $this->request_error_msg
     */
    public function getErrorMsg()
    {
        return $this->request_error_msg;
    }

    /**
     * This method get the  message
     *
     * @return string $this->request_msg
     */
    public function getMsg()
    {
        return $this->request_msg;
    }

    /**
     * This method get the code
     *
     * @return string $this->request_code
     */
    public function getCode()
    {
        return $this->request_code;
    }

    /**
     * This method set all needed data
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
     */
    public function setData($total_amount, $amount, $interest_rate, $interest_amount, $service_charge, $annual_percentage_rate, $monthly_debit_interest, $number_of_rates, $rate, $last_rate, $payment_firstday)
    {
        $this->picalcdata->setData($total_amount, $amount, $interest_rate, $interest_amount, $service_charge, $annual_percentage_rate, $monthly_debit_interest, $number_of_rates, $rate, $last_rate, $payment_firstday);
    }

    /**
     * This method set the details setter
     */
    public function getData()
    {
        $array = $this->picalcdata->getData();
        $this->setDetailsTotalAmount($array['total_amount']);
        $this->setDetailsAmount($array['amount']);
        $this->setDetailsInterestRate($array['interest_rate']);
        $this->setDetailsInterestAmount($array['interest_amount']);
        $this->setDetailsServiceCharge($array['service_charge']);
        $this->setDetailsAnnualPercentageRate($array['annual_percentage_rate']);
        $this->setDetailsMonthlyDebitInterest($array['monthly_debit_interest']);
        $this->setDetailsNumberOfRates($array['number_of_rates']);
        $this->setDetailsRate($array['rate']);
        $this->setDetailsLastRate($array['last_rate']);
    }

    /**
     * This method unset all Data
     */
    public function unsetData()
    {
        $this->picalcdata->unsetData();
    }

    /**
     * Specify how to get Data from GET
     *
     * @param string $var
     * @return string
     */
     public function getGetParameter($var) {
        return $this->picalcdata->getGetParameter($var);
    }

    /**
     * Specify how to get Data from POST
     *
     * @param string $var
     * @return string
     */
     public function getPostParameter($var) {
         return $this->picalcdata->getPostParameter($var);
    }
}
