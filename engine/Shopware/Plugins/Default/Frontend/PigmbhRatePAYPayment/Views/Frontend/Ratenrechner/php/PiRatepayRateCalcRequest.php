<?php
/**
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 *
 * @package pi_ratepay_rate_calculator
 * Code by PayIntelligent GmbH  <http://www.payintelligent.de/>
 */
$pi_calculator = new PiRatepayRateCalc();
if ($pi_calculator->getPostParameter('calcValue') != '' && $pi_calculator->getPostParameter('calcMethod') != '') {
    if ($pi_calculator->getPostParameter('calcMethod') == "calculation-by-time" || $pi_calculator->getPostParameter('calcMethod') == "calculation-by-rate") {
        if ($pi_calculator->getPostParameter('calcMethod') == "calculation-by-time" && is_numeric($pi_calculator->getPostParameter('calcValue'))) {
            if (preg_match('/^[0-9]{1,3}$/', $pi_calculator->getPostParameter('calcValue'))) {
                $pi_calculator->setRequestCalculationValue($pi_calculator->getPostParameter('calcValue'));
                $pi_calculator->setRequestDueDay($pi_calculator->getPostParameter('dueDate'));
                $pi_resultArray = $pi_calculator->getRatepayRateDetails($pi_calculator->getPostParameter('calcMethod'));
            } else {
                $pi_calculator->setErrorMsg('wrongvalue');
            }
        } else if ($pi_calculator->getPostParameter('calcMethod') == "calculation-by-rate") {
            if (preg_match('/^[0-9]+(\.[0-9][0-9][0-9])?(,[0-9]{1,2})?$/', $pi_calculator->getPostParameter('calcValue'))) {
                $pi_value = $pi_calculator->getPostParameter('calcValue');
                $pi_value = str_replace(".", "", $pi_value);
                $pi_value = str_replace(",", ".", $pi_value);
                $pi_calculator->setRequestCalculationValue($pi_value);
                $pi_calculator->setRequestDueDay($pi_calculator->getPostParameter('dueDate'));
                $pi_resultArray = $pi_calculator->getRatepayRateDetails($pi_calculator->getPostParameter('calcMethod'));
            } else if (preg_match('/^[0-9]+(\,[0-9][0-9][0-9])?(.[0-9]{1,2})?$/', $pi_calculator->getPostParameter('calcValue'))) {
                $pi_value = $pi_calculator->getPostParameter('calcValue');
                $pi_value = str_replace(",", "", $pi_value);
                $pi_calculator->setRequestCalculationValue($pi_value);
                $pi_calculator->setRequestDueDay($pi_calculator->getPostParameter('dueDate'));
                $pi_resultArray = $pi_calculator->getRatepayRateDetails($pi_calculator->getPostParameter('calcMethod'));
            } else {
                $pi_calculator->setErrorMsg('wrongvalue');
            }
        } else {
            $pi_calculator->setErrorMsg('wrongvalue');
        }
    } else {
        $pi_calculator->setErrorMsg('wrongsubtype');
    }
} else {
    $pi_calculator->getData();
    $pi_resultArray = $pi_calculator->createFormattedResult();
}

$pi_language = $pi_calculator->getLanguage();
$pi_amount = $pi_calculator->getRequestAmount();

if ($pi_language == "DE") {
     require_once $calcPath.'/languages/german.php';
    $pi_currency = 'EUR';
    $pi_decimalSeperator = ',';
    $pi_thousandSeperator = '.';
} else {
    require_once $calcPath.'/languages/english.php';
    $pi_currency = 'EUR';
    $pi_decimalSeperator = '.';
    $pi_thousandSeperator = ',';
}

$pi_amount = number_format($pi_amount, 2, $pi_decimalSeperator, $pi_thousandSeperator);
if ($pi_calculator->getErrorMsg() != '') {
    if ($pi_calculator->getErrorMsg() == 'serveroff') {
        echo "<div class='pirperror' id='pirperror'>" . $pi_lang_error . ":&nbsp;&nbsp;" . $pi_lang_server_off . "</div>";
    } else if ($pi_calculator->getErrorMsg() == 'wrongvalue') {
        echo "<div class='pirperror' id='pirperror'>" . $pi_lang_error . ":&nbsp;&nbsp;" . $pi_lang_wrong_value . "</div>";
    } else {
        echo "<div class='pirperror' id='pirperror'>" . $pi_lang_error . ":&nbsp;&nbsp;" . $pi_lang_request_error_else . "</div>";
    }
} else{
    if ($pi_calculator->getPostParameter('calcValue') != '' && $pi_calculator->getPostParameter('calcMethod') != '') {
    ?>

    <div id="piRpNotfication"><?php echo $pi_lang_information . ":<br/>" . $pi_lang_info[$pi_calculator->getCode()]; ?></div>
<?php } ?>
    <div id="piRpResult">
    <h2 class="pirpmid-heading"><b><?php echo $pi_lang_individual_rate_calculation; ?></b></h2>

    <table id="piInstallmentTerms" cellspacing="0">
        <tr>
            <td id="piInstallmentFirstTd">
                <div class="piRpInfoImgDiv"><img onMouseOver="piMouseOver('piRpMouseoverInfoPaymentPrice')" onMouseOut="piMouseOut('piRpMouseoverInfoPaymentPrice')" class="piRpInfoImg" src="<?php echo $pi_ratepay_rate_calc_path; ?>images/info-icon.png" /></div>
                <div class="piRpFloatLeft"><?php echo $pi_lang_cash_payment_price; ?>:</div>
                <div class="piRpRelativePosition">
                    <div class="piRpMouseoverInfo" id="piRpMouseoverInfoPaymentPrice"><?php echo $pi_lang_mouseover_cash_payment_price; ?></div>
                </div>
            </td>
            <td></td>
            <td id="piInstallmentSecondTd">&nbsp;<?php echo $pi_resultArray['amount']; ?>&nbsp;</td>
            <td id="piInstallmentThirdTd" class="piRpTextAlignLeft">&euro;</td>
        </tr>

        <tr class="piTableHr">
            <td>
                <div class="piRpInfoImgDiv"><img onMouseOver="piMouseOver('piRpMouseoverInfoServiceCharge')" onMouseOut="piMouseOut('piRpMouseoverInfoServiceCharge')" class="piRpInfoImg" src="<?php echo $pi_ratepay_rate_calc_path; ?>images/info-icon.png" /></div>
                <div class="piRpFloatLeft"><?php echo $pi_lang_service_charge; ?>:</div>
                <div class="piRpRelativePosition">
                    <div class="piRpMouseoverInfo" id="piRpMouseoverInfoServiceCharge"><?php echo $pi_lang_mouseover_service_charge; ?></div>
                </div>
            </td>
            <td></td>
            <td>&nbsp;<?php echo $pi_resultArray['serviceCharge']; ?>&nbsp;</td>
            <td class="piRpTextAlignLeft">&euro;</td>
        </tr>

        <tr class="piPriceSectionHead">
            <td class="piRpPercentWidth">
                <div class="piRpInfoImgDiv"><img onMouseOver="piMouseOver('piRpMouseoverInfoEffectiveRate')" onMouseOut="piMouseOut('piRpMouseoverInfoEffectiveRate')" class="piRpInfoImg" src="<?php echo $pi_ratepay_rate_calc_path; ?>images/info-icon.png" /></div>
                <div class="piRpFloatLeft"><?php echo $pi_lang_effective_rate; ?>:</div>
                <div class="piRpRelativePosition">
                    <div class="piRpMouseoverInfo" id="piRpMouseoverInfoEffectiveRate"><?php echo $pi_lang_mouseover_effective_rate; ?></div>
                </div>
            </td>
            <td><?php echo $pi_resultArray['annualPercentageRate']; ?>%</td>
            <td></td>
        </tr>
        <tr class="piTableHr">
            <td>
                <div class="piRpInfoImgDiv"><img onMouseOver="piMouseOver('piRpMouseoverInfoDebitRate')" onMouseOut="piMouseOut('piRpMouseoverInfoDebitRate')" class="piRpInfoImg" src="<?php echo $pi_ratepay_rate_calc_path; ?>images/info-icon.png" /></div>
                <div class="piRpFloatLeft"><?php echo $pi_lang_debit_rate; ?>:</div>
                <div class="piRpRelativePosition">
                    <div class="piRpMouseoverInfo" id="piRpMouseoverInfoDebitRate"><?php echo $pi_lang_mouseover_debit_rate; ?></div>
                </div>
            </td>
            <td><?php echo $pi_resultArray['monthlyDebitInterest']; ?>%</td>
            <td></td>
        </tr>

        <tr>
            <td>
                <div class="piRpInfoImgDiv"><img onMouseOver="piMouseOver('piRpMouseoverInfoInterestAmount')" onMouseOut="piMouseOut('piRpMouseoverInfoInterestAmount')" class="piRpInfoImg" src="<?php echo $pi_ratepay_rate_calc_path; ?>images/info-icon.png" /></div>
                <div class="piRpFloatLeft"><?php echo $pi_lang_interest_amount; ?>:</div>
                <div class="piRpRelativePosition">
                    <div class="piRpMouseoverInfo" id="piRpMouseoverInfoInterestAmount"><?php echo $pi_lang_mouseover_interest_amount; ?></div>
                </div>
            </td>
            <td></td>
            <td>&nbsp;<?php echo $pi_resultArray['interestAmount']; ?>&nbsp;</td>
            <td class="piRpTextAlignLeft">&euro;</td>
        </tr>

        <tr>
            <td>
                <div class="piRpInfoImgDiv"><img onMouseOver="piMouseOver('piRpMouseoverInfoTotalAmount')" onMouseOut="piMouseOut('piRpMouseoverInfoTotalAmount')" class="piRpInfoImg" src="<?php echo $pi_ratepay_rate_calc_path; ?>images/info-icon.png" /></div>
                <div class="piRpFloatLeft"><b><?php echo $pi_lang_total_amount; ?>:</b></div>
                <div class="piRpRelativePosition">
                    <div class="piRpMouseoverInfo" id="piRpMouseoverInfoTotalAmount"><?php echo $pi_lang_mouseover_total_amount; ?></div>
                </div>
            </td>
            <td></td>
            <td><b>&nbsp;<?php echo $pi_resultArray['totalAmount']; ?>&nbsp;</b></td>
            <td class="piRpTextAlignLeft"><b>&euro;</b></td>
        </tr>
        <tr>
            <td colspan="4"><div class="piRpFloatLeft">&nbsp;<div></td>
        </tr>
        <tr>
            <td colspan="4"><div class="piRpFloatLeft piRpMarginBottom"><?php echo $pi_lang_calulation_result_text ?><div></td>
        </tr>

        <tr class="piRpyellow piPriceSectionHead">
            <td class="piRpPaddingTop">
                <div class="piRpInfoImgDiv"><img onMouseOver="piMouseOver('piRpMouseoverInfoDurationTime')" onMouseOut="piMouseOut('piRpMouseoverInfoDurationTime')" class="piRpInfoImg" src="<?php echo $pi_ratepay_rate_calc_path; ?>images/info-icon.png" /></div>
                <div class="piRpFloatLeft"><b><?php echo $pi_lang_duration_time; ?>:</b></div>
                <div class="piRpRelativePosition">
                    <div class="piRpMouseoverInfo" id="piRpMouseoverInfoDurationTime"><?php echo $pi_lang_mouseover_duration_time; ?></div>
                </div>
            </td>
            <td colspan="3" class="piRpPaddingRight piRpPaddingTop"><b><?php echo $pi_resultArray['numberOfRatesFull']; ?><?php if(!$pi_lang_months)echo '&nbsp;'; echo $pi_lang_months; ?></b></td>
        </tr>
        <tr class="piRpyellow">
            <td>
                <div class="piRpInfoImgDiv"><img onMouseOver="piMouseOver('piRpMouseoverInfoDurationMonth')" onMouseOut="piMouseOut('piRpMouseoverInfoDurationMonth')" class="piRpInfoImg" src="<?php echo $pi_ratepay_rate_calc_path; ?>images/info-icon.png" /></div>
                <div class="piRpFloatLeft piRpPaddingLeft"><b><?php echo $pi_resultArray['numberOfRates'];?><?php echo $pi_lang_duration_month;?>:</b></div>
                <div class="piRpRelativePosition">
                    <div class="piRpMouseoverInfo" id="piRpMouseoverInfoDurationMonth"><?php echo $pi_lang_mouseover_duration_month; ?></div>
                </div>
            </td>
            <td colspan="2"><b>&nbsp;<?php echo $pi_resultArray['rate'];?>&nbsp;</b></td>
            <td class="piRpPaddingRight"><b>&euro;</b></td>
        </tr>
        <tr class="piRpyellow piRpPaddingBottom">
            <td class="piRpPaddingBottom">
                <div class="piRpInfoImgDiv"><img onMouseOver="piMouseOver('piRpMouseoverInfoLastRate')" onMouseOut="piMouseOut('piRpMouseoverInfoLastRate')" class="piRpInfoImg" src="<?php echo $pi_ratepay_rate_calc_path; ?>images/info-icon.png" /></div>
                <div class="piRpFloatLeft piRpPaddingLeft"><b><?php echo $pi_lang_last_rate; ?>:</b></div>
                <div class="piRpRelativePosition">
                    <div class="piRpMouseoverInfo" id="piRpMouseoverInfoLastRate"><?php echo $pi_lang_mouseover_last_rate; ?></div>
                </div>
            </td>
            <td colspan="2" class="piRpPaddingBottom"><b>&nbsp;<?php echo $pi_resultArray['lastRate']; ?>&nbsp;</b></td>
            <td class="piRpPaddingRight piRpPaddingBottom"><b>&euro;</b></td>
        </tr>
        <tr>
            <td colspan="4"><div class="piRpCalculationText "><?php echo $pi_lang_calulation_example; ?></div></td>
        </tr>
    </table>
    </div>
<?php } ?>