<?php

/**
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 *
 * @package pi_ratepay_rate_calculator
 * Code by PayIntelligent GmbH  <http://www.payintelligent.de/>
 */
interface PiRatepayRateCalcDataInterface
{

    public function getProfileId();

    public function getSecurityCode();

    public function isLive();

    public function getSecurityCodeHashed();

    public function getTransactionId();

    public function getTransactionShortId();

    public function getOrderId();

    public function getMerchantConsumerId();

    public function getMerchantConsumerClassification();

    public function getAmount();

    public function getData();

    public function getPaymentFirstdayConfig();

    public function setData($total_amount, $amount, $interest_rate, $interest_amount, $service_charge, $annual_percentage_rate, $monthly_debit_interest, $number_of_rates, $rate, $last_rate, $payment_firstday);

    public function unsetData();

    public function getGetParameter($var);

    public function getPostParameter($var);
}
