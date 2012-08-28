/**
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 *
 * @package pi_ratepay_rate_calculator
 * Code by PayIntelligent GmbH  <http://www.payintelligent.de/>
 */

function piRatepayRateCalculatorAction(mode) {
    var calcValue;
    var calcMethod;

    var html;

    if (window.XMLHttpRequest) {// code for IE7+, Firefox, Chrome, Opera, Safari
        xmlhttp = new XMLHttpRequest();
    } else {// code for IE6, IE5
        xmlhttp = new ActiveXObject("Microsoft.XMLHTTP");
    }
    piRpInputValue=document.getElementById('piRpInput-button').value;
    piRpInputValueRuntime=document.getElementById('piRpInput-buttonRuntime').value;
    if (mode == 'rate') {
        calcValue = document.getElementById('rate').value;
        calcMethod = 'calculation-by-rate';
        document.getElementById('piRpInput-button').className = "piRpInput-button  ajaxloader";
        document.getElementById('piRpInput-button').value='wird geladen ...';
         if(document.getElementById('debitSelect')){
             dueDate = document.getElementById('debitSelect').value;
        } else {
            dueDate= '';
        }
    } else if (mode == 'runtime') {
        calcValue = document.getElementById('runtime').value;
        calcMethod = 'calculation-by-time';
        document.getElementById('piRpInput-buttonRuntime').className = "piRpInput-button  ajaxloader";

        document.getElementById('piRpInput-buttonRuntime').value='wird geladen ...';
        if(document.getElementById('debitSelectRuntime')){
             dueDate = document.getElementById('debitSelectRuntime').value;
        } else {
            dueDate= '';
        }
    }


    xmlhttp.open("POST", pi_ratepay_rate_ajax_path + "calcRequest", false);

    xmlhttp.setRequestHeader("Content-Type",
        "application/x-www-form-urlencoded");

    xmlhttp.send("calcValue=" + calcValue + "&calcMethod=" + calcMethod + "&dueDate=" + dueDate);

    if (xmlhttp.responseText != null) {
        html = xmlhttp.responseText;
        document.getElementById('piRpResultContainer').innerHTML = html;
        document.getElementById('piRpResultContainer').style.display = 'block';
        document.getElementById('piRpResultContainer').style.padding = '3px 0 0 0';
        document.getElementById('piRpSwitchToTerm').style.display = 'none';
        setTimeout("piSetLoaderBack()",300);

    }
}

function piLoadrateCalculator() {
    var html;

    if (window.XMLHttpRequest) {// code for IE7+, Firefox, Chrome, Opera, Safari
        xmlhttp = new XMLHttpRequest();
    } else {// code for IE6, IE5
        xmlhttp = new ActiveXObject("Microsoft.XMLHTTP");
    }
    xmlhttp.open("POST", pi_ratepay_rate_ajax_path + "calcDesign", false);

    xmlhttp.setRequestHeader("Content-Type",
        "application/x-www-form-urlencoded");

    xmlhttp.send();

    if (xmlhttp.responseText != null) {
        html = xmlhttp.responseText;
        document.getElementById('pirpmain-cont').innerHTML = html;
    }
}

function piSetLoaderBack(){
    document.getElementById('piRpInput-buttonRuntime').className='piRpInput-button';
    document.getElementById('piRpInput-button').className='piRpInput-button';
    document.getElementById('piRpInput-buttonRuntime').value= piRpInputValueRuntime;
    document.getElementById('piRpInput-button').value= piRpInputValue;
}

function piLoadrateResult() {
    var html;

    if (window.XMLHttpRequest) {// code for IE7+, Firefox, Chrome, Opera, Safari
        xmlhttp = new XMLHttpRequest();
    } else {// code for IE6, IE5
        xmlhttp = new ActiveXObject("Microsoft.XMLHTTP");
    }
    xmlhttp.open("POST", pi_ratepay_rate_ajax_path + "calcRequest", false);

    xmlhttp.setRequestHeader("Content-Type",
        "application/x-www-form-urlencoded");

    xmlhttp.send();

    if (xmlhttp.responseText != null) {
        html = xmlhttp.responseText;
        document.getElementById('pirpmain-cont').innerHTML = html;
    }
}