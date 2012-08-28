<?php
/**
 *  Copyright 2010 KLARNA AB. All rights reserved.
 *
 *  Redistribution and use in source and binary forms, with or without modification, are
 *  permitted provided that the following conditions are met:
 *
 *     1. Redistributions of source code must retain the above copyright notice, this list of
 *        conditions and the following disclaimer.
 *
 *     2. Redistributions in binary form must reproduce the above copyright notice, this list
 *        of conditions and the following disclaimer in the documentation and/or other materials
 *        provided with the distribution.
 *
 *  THIS SOFTWARE IS PROVIDED BY KLARNA AB "AS IS" AND ANY EXPRESS OR IMPLIED
 *  WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND
 *  FITNESS FOR A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL KLARNA AB OR
 *  CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR
 *  CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR
 *  SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON
 *  ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING
 *  NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF
 *  ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 *
 *  The views and conclusions contained in the software and documentation are those of the
 *  authors and should not be interpreted as representing official policies, either expressed
 *  or implied, of KLARNA AB.
 *
 * @package KlarnaAPI
 */

/**
 * KlarnaCalc provides methods to calculate part payment functions.
 *
 * All rates are yearly rates, but they are calculated monthly. So
 * a rate of 9 % is used 0.75% monthly. The first is the one we specify
 * to the customers, and the second one is the one added each month to
 * the account. The IRR uses the same notation.
 *
 * The APR is however calculated by taking the monthly rate and raising
 * it to the 12 power. This is according to the EU law, and will give
 * very large numbers if the $pval is small compared to the $fee and
 * the amount of months you repay is small as well.
 *
 * All functions work in discrete mode, and the time interval is the
 * mythical evenly divided month. There is no way to calculate APR in
 * days without using integrals and other hairy math. So don't try.
 * The amount of days between actual purchase and the first bill can
 * of course vary between 28 and 61 days, but all calculations in this
 * class assume this time is exactly and that is ok since this will only
 * overestimate the APR and all examples in EU law uses whole months as well.
 *
 * @package   KlarnaAPI
 * @version   2.1.2
 * @since     2011-09-13
 * @link      http://integration.klarna.com/
 * @copyright Copyright (c) 2010 Klarna AB (http://klarna.com)
 */
class KlarnaCalc {

    /**
     * This constant tells the irr function when to stop.
     * If the calculation error is lower than this the calculation is done.
     *
     * @var float
     */
    protected static $accuracy = 0.01;

    /**
     * Calculates the midpoint between two points. Used by divide and conquer.
     *
     * @param  float $a
     * @param  float $b
     * @return float
     */
    private static function midpoint($a, $b) {
        return (($a+$b)/2);
    }

    /**
     * npv - Net Present Value
     * Calculates the difference between the initial loan to the customer
     * and the individual payments adjusted for the inverse of the interest
     * rate. The variable we are searching for is $rate and if $pval,
     * $payarray and $rate is perfectly balanced this function returns 0.0.
     *
     * @param  float        $pval       initial loan to customer (in any currency)
     * @param  array        $payarray   array of monthly payments from the customer
     * @param  float        $rate       interest rate per year in %
     * @param  int          $fromdayone do we count interest from the first day yes(1)/no(0).
     * @return float
     */
    private static function npv($pval, $payarray, $rate, $fromdayone) {
        $month = $fromdayone;
        foreach($payarray as $payment) {
            $pval -= $payment / pow (1 + $rate/(12*100.0), $month++);
        }

        return ($pval);
    }

    /**
     * This function uses divide and conquer to numerically find the IRR,
     * Internal Rate of Return. It starts of by trying a low of 0% and a
     * high of 100%. If this isn't enough it will double the interval up
     * to 1000000%. Note that this is insanely high, and if you try to convert
     * an IRR that high to an APR you will get even more insane values,
     * so feed this function good data.
     *
     * Return values: float irr  if it was possible to find a rate that gets
     *                           npv closer to 0 than $accuracy.
     *                int -1     The sum of the payarray is less than the lent
     *                           amount, $pval. Hellooooooo. Impossible.
     *                int -2     the IRR is way to high, giving up.
     *
     * This algorithm works in logarithmic time no matter what inputs you give
     * and it will come to a good answer within ~30 steps.
     *
     * @param  float        $pval       initial loan to customer (in any currency)
     * @param  array        $payarray   array of monthly payments from the customer
     * @param  int          $fromdayone do we count interest from the first day yes(1)/no(0).
     * @return float
     */
    private static function irr($pval, $payarray, $fromdayone) {
        $low     = 0.0;
        $high    = 100.0;
        $lowval  = self::npv($pval, $payarray, $low, $fromdayone);
        $highval = self::npv($pval, $payarray, $high, $fromdayone);

        // The sum of $payarray is smaller than $pval, impossible!
        if($lowval > 0.0) {
            return (-1);
        }

        // Standard divide and conquer.
        do {
            $mid = self::midpoint($low, $high);
            $midval  = self::npv($pval, $payarray, $mid, $fromdayone);
            if(abs($midval) < self::$accuracy) {
                //we are close enough
                return ($mid);
            }

            if($highval < 0.0) {
                // we are not in range, so double it
                $low = $high;
                $lowval = $highval;
                $high *= 2;
                $highval = self::npv($pval, $payarray, $high, $fromdayone);
            }
            else if($midval >= 0.0) {
                // irr is between low and mid
                $high = $mid;
                $highval = $midval;
            }
            else {
                // irr is between mid and high
                $low = $mid;
                $lowval = $midval;
            }
        } while ($high < 1000000);
        // bad input, insanely high interest. APR will be INSANER!
        return (-2);
    }

    /**
     * IRR is not the same thing as APR, Annual Percentage Rate. The
     * IRR is per time period, i.e. 1 month, and the APR is per year,
     * and note that that you need to raise to the power of 12, not
     * mutliply by 12.
     *
     * This function turns an IRR into an APR.
     *
     * If you feed it a value of 100%, yes the APR will be millions!
     * If you feed it a value of   9%, it will be 9.3806%.
     * That is the nature of this math and you can check the wiki
     * page for APR for more info.
     *
     * @param  float $irr Internal Rate of Return, expressed yearly, in %
     * @return float      Annual Percentage Rate, in %
     */
    private static function irr2apr($irr) {
        return (100 * (pow (1 + $irr / (12 * 100.0), 12) - 1));
    }

    /**
     * This is a simplified model of how our paccengine works if
     * a client always pays their bills. It adds interest and fees
     * and checks minimum payments. It will run until the value
     * of the account reaches 0, and return an array of all the
     * individual payments. Months is the amount of months to run
     * the simulation. Important! Don't feed it too few months or
     * the whole loan won't be paid off, but the other functions
     * should handle this correctly.
     *
     * Giving it too many months has no bad effects, or negative
     * amount of months which means run forever, but it will stop
     * as soon as the account is paid in full.
     *
     * Depending if the account is a base account or not, the
     * payment has to be 1/24 of the capital amount.
     *
     * The payment has to be at least $minpay, unless the capital
     * amount + interest + fee is less than $minpay; in that case
     * that amount is paid and the function returns since the client
     * no longer owes any money.
     *
     * @param  float        $pval       initial loan to customer (in any currency)
     * @param  float        $rate       interest rate per year in %
     * @param  float        $fee        monthly invoice fee
     * @param  float        $minpay     minimum monthly payment allowed for this country.
     * @param  float        $payment    payment the client to pay each month
     * @param  int          $months     amount of months to run (-1 => infinity)
     * @param  boolean      $base       is it a base account?
     * @return array  An array of monthly payments for the customer.
     */
    private static function fulpacc($pval, $rate, $fee, $minpay, $payment, $months, $base) {
        $bal = $pval;
        $payarray = array();
        while(($months != 0) && ($bal > self::$accuracy)) {
            $interest = $bal * $rate / (100.0 * 12);
            $newbal = $bal + $interest + $fee;

            if($minpay >= $newbal || $payment >= $newbal) {
                $payarray[] = $newbal;
                return $payarray;
            }

            $newpay = max($payment, $minpay);
            if($base) {
                $newpay = max($newpay, $bal/24.0 + $fee + $interest);
            }

            $bal = $newbal - $newpay;
            $payarray[] = $newpay;
            $months -= 1;
        }

        return $payarray;
    }

    /**
     * Calculates how much you have to pay each month if you want to
     * pay exactly the same amount each month. The interesting input
     * is the amount of $months.
     *
     * It does not include the fee so add that later.
     *
     * Return value: monthly payment.
     *
     * @param  float  $pval   principal value
     * @param  int    $months months to pay of in
     * @param  float  $rate   interest rate in % as before
     * @return float monthly payment
     */
    private static function annuity($pval, $months, $rate) {
        if($months == 0) {
            return $pval;
        }

        if($rate == 0) {
            return $pval/$months;
        }

        $p = $rate / (100.0*12);
        return $pval * $p / (1 - pow((1+$p), -$months));
    }

    /**
     * How many months does it take to pay off a loan if I pay
     * exactly $monthly each month? It might actually go faster
     * if you hit the minimum payments, but this function returns
     * the longest amount of months.
     *
     * This function _does_ not include the fee, so remove the fee
     * from the monthly before sending it into this function.
     *
     * Return values: float $months
     *                int   -1      you are not paying more than
     *                              the interest. infinity
     *                int   -2      $fromdayone has to be 0 or 1
     *
     * $fromdayone should be 0 for pay_in_X_months since the interest
     * won't be applied on the first invoice. In all other cases use 1.
     *
     * @param  float  $pval       principal value
     * @param  float  $monthly    payment/month (-fee)
     * @param  float  $rate       interest rate in %
     * @param  int    $fromdayone do we count interest from day one? [0, 1]
     * @return float  months it takes (round it up)
     */
    private static function fixed($pval, $monthly, $rate, $fromdayone) {
        $p = $rate / (100.0*12);
        $f = 1 + $p;
        if($fromdayone == 0) {
            if( $f < $pval * $p / $monthly ) {
                return -1;
            }
            // this might be wrong. check it.
            // it seems to give the right output.
            return 1 - log($f - $pval * $p / $monthly) / log($f);
        }
        else if($fromdayone == 1) {
            if(1.0 < $pval * $p / $monthly ) {
                return -1;
            }
            return -log(1.0 - $pval * $p / $monthly) / log($f);
        }
        else {
            return -2;
        }
    }

    /**
     * Calculate the APR for an annuity given the following inputs.
     *
     * If you give it bad inputs, it will return negative values.
     *
     * @param  float  $pval   principal value
     * @param  int    $months months to pay off in
     * @param  float  $rate   interest rate in % as before
     * @param  float  $fee    monthly fee
     * @param  float  $minpay minimum payment per month
     * @return float  APR in %
     */
    private static function apr_annuity($pval, $months, $rate, $fee, $minpay) {
        $payment = self::annuity($pval, $months, $rate) + $fee;
        if($payment < 0) {
            return $payment;
        }
        $payarray = self::fulpacc($pval, $rate, $fee, $minpay, $payment, $months, false);
        $apr = self::irr2apr(self::irr($pval, $payarray, 1));

        return $apr;
    }

    /**
     * Calculate the APR given a fixed payment each month.
     *
     * If you give it bad inputs, it will return negative values.
     *
     * @param  float  $pval    principal value
     * @param  float  $payment monthly payment for client
     * @param  float  $rate    interest rate in % as before
     * @param  float  $fee     monthly fee
     * @param  float  $minpay  minimum payment per month
     * @return float  APR in %
     */
    private static function apr_fixed($pval, $payment, $rate, $fee, $minpay) {
        $months = self::fixed($pval, $payment-$fee, $rate, 1);
        if($months < 0) {
            return $months;
        }
        $months = ceil($months);
        $payarray = self::fulpacc($pval, $rate, $fee, $minpay, $payment, $months, false);
        $apr = self::irr2apr(self::irr($pval, $payarray, 1));

        return $apr;
    }

    /**
     * Calculates APR for a campaign where you give $free months to
     * the client and there is no interest on the first invoice.
     * The only new input is $free, and if you give "Pay in Jan"
     * in November, then $free = 2.
     *
     * The more free months you give, the lower the APR so it does
     * matter.
     *
     * This function basically pads the $payarray with zeros in the
     * beginning (but there is some more magic as well).
     *
     * @param  float  $pval    principal value
     * @param  float  $payment monthly payment for client
     * @param  float  $rate    interest rate in % as before
     * @param  float  $fee     monthly fee
     * @param  float  $minpay  minimum payment per month
     * @param  int    $free    free months
     * @return float  APR in %
     */
    private static function apr_payin_X_months($pval, $payment, $rate, $fee, $minpay, $free) {
        $firstpay = $payment; //this used to be buggy. use this line.
        $months = self::fixed($pval, $payment-$fee, $rate, 0);
        if($months < 0) {
            return $months;
        }

        $months = ceil($months);
        $farray = array();
        while($free--) {
            $farray[] = 0.0;
        }
        $pval += $fee;

        $farray[] = $firstpay;
        $pval -= $firstpay;
        $payarray = self::fulpacc($pval, $rate, $fee, $minpay, $payment, $months, false);
        $newarray = array_merge($farray, $payarray);
        $apr = self::irr2apr(self::irr($pval, $newarray, 1));

        return $apr;
    }

    /**
     * Grabs the array of all monthly payments for specified PClass.
     *
     * <b>Flags can be either</b>:<br>
     * {@link KlarnaFlags::CHECKOUT_PAGE}<br>
     * {@link KlarnaFlags::PRODUCT_PAGE}<br>
     *
     * @param  float         $sum     The sum for the order/product.
     * @param  KlarnaPClass  $pclass  {@link KlarnaPClass PClass} used to calculate the APR.
     * @param  int           $flags   Indicates if it is the checkout or a product page.
     * @throws KlarnaException
     * @return array  An array of monthly payments.
     */
    private static function get_payarr($sum, $pclass, $flags) {
        $monthsfee = (($flags === KlarnaFlags::CHECKOUT_PAGE) ? $pclass->getInvoiceFee() : 0);
        $startfee = (($flags === KlarnaFlags::CHECKOUT_PAGE) ? $pclass->getStartFee() : 0);

        //Include start fee in sum
        $sum += $startfee;

        $base = ($pclass->getType() === KlarnaPClass::ACCOUNT);

        $lowest = self::get_lowest_payment_for_account($pclass->getCountry());
        if($flags == KlarnaFlags::CHECKOUT_PAGE) {
            $minpay = ($pclass->getType() === KlarnaPClass::ACCOUNT) ? $lowest : 0;
        }
        else {
            $minpay = 0;
        }

        $payment = self::annuity($sum, $pclass->getMonths(), $pclass->getInterestRate());

        //Add monthly fee
        $payment += $monthsfee;

        return  self::fulpacc($sum, $pclass->getInterestRate(), $monthsfee, $minpay, $payment, $pclass->getMonths(), $base);
    }

    /**
     * Calculates APR for the specified values.<br>
     * Result is rounded with two decimals.<br>
     *
     * <b>Flags can be either</b>:<br>
     * {@link KlarnaFlags::CHECKOUT_PAGE}<br>
     * {@link KlarnaFlags::PRODUCT_PAGE}<br>
     *
     * @param  float         $sum     The sum for the order/product.
     * @param  KlarnaPClass  $pclass  {@link KlarnaPClass PClass} used to calculate the APR.
     * @param  int           $flags   Indicates if it is the checkout or a product page.
     * @param  int           $free    Number of free months.
     * @throws KlarnaException
     * @return float  APR in %
     */
    public static function calc_apr($sum, $pclass, $flags, $free = 0) {
        if(!is_numeric($sum)) {
            throw new KlarnaException('Error in ' . __METHOD__ . ': Argument sum is not numeric!');
        }
        else if(is_numeric($sum) && (!is_int($sum) || !is_float($sum))) {
            $sum = floatval($sum);
        }

        if(!($pclass instanceof KlarnaPClass)) {
            throw new KlarnaException('Error in ' . __METHOD__ . ': Supplied PClass is not a PClass object!');
        }

        if(!is_numeric($free)) {
            throw new KlarnaException('Error in ' . __METHOD__ . ': Argument free is not an integer!');
        }
        else if(is_numeric($free) && !is_int($free)) {
            $free = intval($free);
        }
        if($free < 0) {
            throw new KlarnaException('Error in ' . __METHOD__ . ': Number of free months must be positive or zero!');
        }

        if(is_numeric($flags) && !is_int($flags)) {
            $flags = intval($flags);
        }
        if(!is_numeric($flags) || !in_array($flags, array(KlarnaFlags::CHECKOUT_PAGE, KlarnaFlags::PRODUCT_PAGE))) {
            throw new KlarnaException('Error in ' . __METHOD__ . ': Flags argument invalid!');
        }

        $monthsfee = (($flags === KlarnaFlags::CHECKOUT_PAGE) ? $pclass->getInvoiceFee() : 0);
        $startfee = (($flags === KlarnaFlags::CHECKOUT_PAGE) ? $pclass->getStartFee() : 0);

        //Include start fee in sum
        $sum += $startfee;

        $lowest = self::get_lowest_payment_for_account($pclass->getCountry());
        if($flags == KlarnaFlags::CHECKOUT_PAGE) {
            $minpay = ($pclass->getType() === KlarnaPClass::ACCOUNT) ? $lowest : 0;
        }
        else {
            $minpay = 0;
        }

        //add monthly fee
        $payment = self::annuity($sum, $pclass->getMonths(), $pclass->getInterestRate()) + $monthsfee;
        //echo "annuity $payment, $sum " . $pclass->getMonths() . " " . $pclass->getInterestRate() . "\n";

        $type = $pclass->getType();
        switch($type) {
            case KlarnaPClass::CAMPAIGN:
            case KlarnaPClass::ACCOUNT:
                $apr = self::apr_annuity($sum, $pclass->getMonths(), $pclass->getInterestRate(), $pclass->getInvoiceFee(), $minpay);
                break;
            case KlarnaPClass::SPECIAL:
                $apr = self::apr_payin_X_months($sum, $payment, $pclass->getInterestRate(), $pclass->getInvoiceFee(), $minpay, $free);
                break;
            case KlarnaPClass::FIXED:
                $apr = self::apr_fixed($sum, $payment, $pclass->getInterestRate(), $pclass->getInvoiceFee(), $minpay);
                break;
            default:
                throw new KlarnaException('Error in ' . __METHOD__ . ': Unknown PClass type! ('.$type.')');
        }

        return round($apr, 2);
    }

    /**
     * Calculates the total credit purchase cost.<br>
     * The result is rounded up, depending on the pclass country.<br>
     *
     * <b>Flags can be either</b>:<br>
     * {@link KlarnaFlags::CHECKOUT_PAGE}<br>
     * {@link KlarnaFlags::PRODUCT_PAGE}<br>
     *
     * @param  float         $sum     The sum for the order/product.
     * @param  KlarnaPClass  $pclass  {@link KlarnaPClass PClass} used to calculate total credit cost.
     * @param  int           $flags   Indicates if it is the checkout or a product page.
     * @throws KlarnaException
     * @return float  Total credit purchase cost.
     */
    public static function total_credit_purchase_cost($sum, $pclass, $flags) {
        if(!is_numeric($sum)) {
            throw new KlarnaException('Error in ' . __METHOD__ . ': Argument sum is not numeric!');
        }
        else if(is_numeric($sum) && (!is_int($sum) || !is_float($sum))) {
            $sum = floatval($sum);
        }

        if(!($pclass instanceof KlarnaPClass)) {
            throw new KlarnaException('Error in ' . __METHOD__ . ': Supplied PClass is not a PClass object!');
        }

        if(is_numeric($flags) && !is_int($flags)) {
            $flags = intval($flags);
        }
        if(!is_numeric($flags) || !in_array($flags, array(KlarnaFlags::CHECKOUT_PAGE, KlarnaFlags::PRODUCT_PAGE))) {
            throw new KlarnaException('Error in ' . __METHOD__ . ': Flags argument invalid!');
        }

        $payarr = self::get_payarr($sum, $pclass, $flags);

        $credit_cost = 0;
        foreach($payarr as $pay) {
            $credit_cost += $pay;
        }

        return self::pRound($credit_cost, $pclass->getCountry());
    }

    /**
     * Calculates the monthly cost for the specified pclass.<br>
     * The result is rounded up to the correct value depending on the pclass country.<br>
     *
     * Example:<br>
     * <ul>
     *     <li>In product view, round monthly cost with max 0.5 or 0.1 depending on currency.<br>
     *     <ul>
     *         <li>10.50 SEK rounds to 11 SEK</li>
     *         <li>10.49 SEK rounds to 10 SEK</li>
     *         <li> 8.55 EUR rounds to 8.6 EUR</li>
     *         <li> 8.54 EUR rounds to 8.5 EUR</li>
     *     </ul></li>
     *     <li>
     *         In checkout, round the monthly cost to have 2 decimals.<br>
     *         For example 10.57 SEK/per m�nad
     *     </li>
     * </ul>
     *
     * <b>Flags can be either</b>:<br>
     * {@link KlarnaFlags::CHECKOUT_PAGE}<br>
     * {@link KlarnaFlags::PRODUCT_PAGE}<br>
     *
     * @param  int           $sum     The sum for the order/product.
     * @param  KlarnaPClass  $pclass  {@link KlarnaPClass PClass} used to calculate monthly cost.
     * @param  int           $flags   Indicates if it is the checkout or a product page.
     * @throws KlarnaException
     * @return float  The monthly cost.
     */
    public static function calc_monthly_cost($sum, $pclass, $flags) {
        if(!is_numeric($sum)) {
            throw new KlarnaException('Error in ' . __METHOD__ . ': Argument sum is not numeric!');
        }
        else if(is_numeric($sum) && (!is_int($sum) || !is_float($sum))) {
            $sum = floatval($sum);
        }

        if(!($pclass instanceof KlarnaPClass)) {
            throw new KlarnaException('Error in ' . __METHOD__ . ': Supplied PClass is not a PClass object!');
        }

        if(is_numeric($flags) && !is_int($flags)) {
            $flags = intval($flags);
        }
        if(!is_numeric($flags) || !in_array($flags, array(KlarnaFlags::CHECKOUT_PAGE, KlarnaFlags::PRODUCT_PAGE))) {
            throw new KlarnaException('Error in ' . __METHOD__ . ': Flags argument invalid!');
        }

        $payarr = self::get_payarr($sum, $pclass, $flags);
        $value = isset($payarr[0]) ? ($payarr[0]) : 0;
        return (KlarnaFlags::CHECKOUT_PAGE == $flags) ? round($value, 2) : self::pRound($value, $pclass->getCountry());
    }

    /**
     * Returns the lowest monthly payment for Klarna Account.
     *
     * @param  int  $country  {@link KlarnaCountry Country} constant.
     * @throws KlarnaException
     * @return int|float      Lowest monthly payment.
     */
    public static function get_lowest_payment_for_account($country) {
        switch ($country) {
            case KlarnaCountry::SE:
                $lowest_monthly_payment = 50.0;
                break;
            case KlarnaCountry::NO:
                $lowest_monthly_payment = 95.0;
                break;
            case KlarnaCountry::FI:
                $lowest_monthly_payment = 8.95;
                break;
            case KlarnaCountry::DK:
                $lowest_monthly_payment = 89.0;
                break;
            case KlarnaCountry::DE:
            case KlarnaCountry::NL:
                $lowest_monthly_payment = 6.95;
                break;
            default:
                throw new KlarnaException('Error in ' . __METHOD__ . ': Not allowed for this country!');
        }

        return $lowest_monthly_payment;
    }

    /**
     * Rounds a value depending on the specified country.
     *
     * @param int|float  $value    The value to be rounded.
     * @param int        $country  {@link KlarnaCountry} constant.
     * @return float|int
     */
    public static function pRound($value, $country) {
        $multiply = 1; //Round to closest integer
        switch($country) {
            case KlarnaCountry::FI:
            case KlarnaCountry::DE:
            case KlarnaCountry::NL:
                $multiply = 10; //Round to closest decimal
                break;
        }

        return floor(($value*$multiply)+0.5)/$multiply;
    }

}

