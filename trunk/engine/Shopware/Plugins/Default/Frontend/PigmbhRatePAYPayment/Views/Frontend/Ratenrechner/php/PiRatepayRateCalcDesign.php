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

$pi_calculator->unsetData();
$pi_config = $pi_calculator->getRatepayRateConfig();
$pi_monthAllowed = $pi_config['month_allowed'];
$pi_monthAllowedArray = explode(',', $pi_monthAllowed);

$pi_amount = $pi_calculator->getRequestAmount();
$pi_language = $pi_calculator->getLanguage();
$pi_firstday = $pi_calculator->getRequestFirstday();

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
        echo "<div>" . $pi_lang_server_off . "</div>";
    } else {
        echo "<div>" . $pi_lang_config_error_else . "</div>";
    }
} else {
    ?>


<div id="piRpHeader">
    <div class="piRpFullWidth">
         <h2 class="piRpH2"><?php echo $pi_lang_calculate_rates_now; ?></h2>
        <img class="piRpLogoImg" src="<?php echo $pi_ratepay_rate_calc_path; ?>images/Logo_Ratepay_Ratenrechner_01_Final_RGB_Farbe_01-small_.png" width="141" height="43" alt="RatePAY Logo">
    </div>
    <br class="piRpClearFix" />
</div>

<div id="piRpContentSwitch">
    <div class="piRpChooseRuntime">
        <?php echo $pi_lang_cash_payment_price_part_one; ?>:
        <span><b><?php echo $pi_amount; ?> &euro;</b></span>
        <?php echo $pi_lang_cash_payment_price_part_two; ?>
        <br />
        <label for="firstInput">
            <div class="piRpChooseInput" id="piRpChooseInputRate">
                <input id="firstInput" class="piRpFloatLeft" type="radio" name="Zahlmethode" value="wishrate"  onClick="switchRateOrRuntime('rate');">
            </div>
            <div class="piRpNintyPercentWidth piRpFloatLeft"><?php echo $pi_lang_payment_text_wishrate; ?></div>
        </label>
        <div id="piRpContentTerm" class="piRpContent" style="display: none;">
            <?php if($pi_firstday){ ?>
                <div id="piRpDueDate" class="piRpDueDate">
                     <div class="piRpDueText"><?php echo $pi_lang_due_date; ?></div>
                     <select name="piRpDueDateSelect" id="debitSelect" size="3">
                         <option value="1" selected="selected">Zum 1. des Monats</option>
                         <option value="15">Zum 15. des Monats</option>
                         <option value="28">Zum 28. des Monats</option>
                    </select>
                    <br class="piRpClearFix" />
                </div>
            <?php } ?>
            <br class="piRpClearFix" />
            <div class="piRpMarginTop">
                <span class="piRpVertAlignMiddle"><?php echo $pi_lang_please . " " . $pi_lang_insert_wishrate; ?>:</span>
                <input name="" id="rate" class="piRpInput-amount" type="text">
                <span class="piRpCurrency"> &euro;</span>
                <input name="" onclick="piRatepayRateCalculatorAction('rate');" value="<?php echo $pi_lang_calculate_runtime; ?>" id="piRpInput-button" class="piRpInput-button" type="button">
            </div>
        </div>
        <br class="piRpClearFix" />
        <label for="secondInput">
            <div class="piRpChooseInput" id="piRpChooseInputRuntime">
                <input id="secondInput" class="piRpFloatLeft" type="radio" name="Zahlmethode" value="runtime" onClick="switchRateOrRuntime('runtime');">
            </div>
            <div class="piRpNintyPercentWidth piRpFloatLeft"><?php echo $pi_lang_payment_text_runtime; ?></div>
        </label>
        <div id="piRpContentRuntime" class="piRpContent" style="display: none;">
            <?php if($pi_firstday){ ?>
                <div id="piRpDueDate" class="piRpDueDate">
                     <div class="piRpDueText"><?php echo $pi_lang_due_date; ?></div>
                     <select name="piRpDueDateSelect" class="piRpDueDateSelect" id="debitSelectRuntime" size="3">
                         <option value="1" selected="selected"><?php echo $pi_lang_first_month; ?></option>
                         <option value="15"><?php echo $pi_lang_second_month; ?></option>
                         <option value="28"><?php echo $pi_lang_third_month; ?></option>
                    </select>
                    <br class="piRpClearFix" />
                </div>
            <?php } ?>
            <br class="piRpClearFix" />
            <div class="piRpMarginTop">
                <span class="piRpVertAlignMiddle"><?php echo $pi_lang_please . " " . $pi_lang_insert_runtime; ?>:</span>
                <select id="runtime">
                    <?php
                    foreach ($pi_monthAllowedArray as $pi_month) {
                        echo '<option value="' . $pi_month . '">' ; if($pi_month<10) echo'&nbsp;'; echo $pi_month . ' ' . $pi_lang_months . '</option>';
                    }
                    ?>
                </select>
                <input name="" onclick="piRatepayRateCalculatorAction('runtime');" value="<?php echo $pi_lang_calculate_rate; ?>" type="button" id="piRpInput-buttonRuntime"  class="piRpInput-button2">
            </div>
        </div>
        <br class="piRpClearFix" />
        <div class="piRpContentSwitchDiv" id="piRpSwitchToTerm" class="piRpActive" style="display: none">
            <span id="pirpspanrate">
                <?php echo $pi_lang_insert_wishrate; ?> <?php echo $pi_lang_calculate_runtime; ?>
            </span>
            <input name="" value="<?php echo $pi_lang_calculate_runtime; ?>" type="button" class="piRpInput-button piRpContentSwitchInput ">
        </div>
        <div class="piRpContentSwitchDiv"  id="piRpSwitchToRuntime" style="display: none">
            <span id="pirpspanruntime" class="pirpactive">
                <?php echo $pi_lang_choose_runtime; ?> <?php echo $pi_lang_calculate_rate; ?>
            </span>
            <input name="" value="<?php echo $pi_lang_calculate_rate; ?>" type="button" class="piRpInput-button piRpContentSwitchInput ">
        </div>
        <div id="piRpResultContainer"></div>
    </div>
</div>

<br class="piRpClearFix" />

 <?php
}
?>