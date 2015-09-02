
{* 
	BENOETIGTE PARAMETER
	sLiveshoppingData > Liveshopping-Datensatz
*}
{assign var="slider0" value=120}
{assign var="slider100" value=200}

{assign var="sliderDiff" value=$slider100-$slider0}
{assign var="sliderOnePro" value=$sliderDiff/100}
{assign var="instockTotal" value=$sLiveshoppingData.max_quantity+$sLiveshoppingData.sells}
{assign var="instockPro" value=$sLiveshoppingData.max_quantity*100/$instockTotal}
{assign var="instockTopValue" value=$sliderOnePro*$instockPro}
{assign var="instockTopValue" value=$slider100-$instockTopValue}


<div class="box_countdown">
	<div class="box_countdown_bg"></div>	
	<div class="box_countdown_aktionsende"></div>	
	<div class="box_countdown_startpreis">{s name='LiveTickerStartPrice'}{/s}</div>	
	<div class="box_countdown_startpreis_zahl">{$sLiveshoppingData.startprice|currency}</div>	
	<div class="box_countdown_slider" style="top:{$instockTopValue}px;">
		<div class="box_countdown_aktpreis">{s name='LiveTickerCurrentPrice'}{/s}</div>
		<div class="box_countdown_preis"><span class="{$uniquekey}" id="{$sLiveshoppingData.ordernumber}_display_price">{$sLiveshoppingData.price|currency}</span></div>
	</div>	
	<div class="box_countdown_time">
		<span class="{$uniquekey}{$sLiveshoppingData.ordernumber}_days_doubledigit">00</span>:<span class="{$uniquekey}{$sLiveshoppingData.ordernumber}_hours_doubledigit">00</span>:<span class="{$uniquekey}{$sLiveshoppingData.ordernumber}_min_doubledigit">00</span>:<span class="{$uniquekey}{$sLiveshoppingData.ordernumber}_sec_doubledigit">00</span>
	</div>	
</div>
