
<div class="time_bars">		
	<strong class="end_time">
		{$sLiveshoppingData.valid_to_ts|date:datetime}
	</strong>
	
	{* Col 1 *}
	<div class="left">		
		<div class="time_processbar_container">
			<div class="live{$uniquekey}{$sLiveshoppingData.ordernumber}_days_process">&nbsp;</div>
		</div>
		<div class="time_processbar_container">
			<div class="live{$uniquekey}{$sLiveshoppingData.ordernumber}_hours_process">&nbsp;</div>
		</div>
		<div class="time_processbar_container">
			<div class="live{$uniquekey}{$sLiveshoppingData.ordernumber}_min_process">&nbsp;</div>
		</div>
		<div class="time_processbar_container">
			<div class="live{$uniquekey}{$sLiveshoppingData.ordernumber}_sec_process">&nbsp;</div>
		</div>
		{if $sLiveshoppingData.max_quantity_enable}
		<div class="instock_processbar_container">
			<div class="live{$uniquekey}{$sLiveshoppingData.ordernumber}_instock_process">&nbsp;</div>
		</div>
		{/if}
	</div>
	
	{* Col 2 *}
	<div class="times">
		<div>
			<span class="live{$uniquekey}{$sLiveshoppingData.ordernumber}_days">0</span> {s name='LiveTimeDays'}{/s}
		</div>
		<div>
			<span class="live{$uniquekey}{$sLiveshoppingData.ordernumber}_hours">0</span> {s name='LiveTimeHours'}{/s}
		</div>
		<div>
			<span class="live{$uniquekey}{$sLiveshoppingData.ordernumber}_min">0</span> {s name='LiveTimeMinutes'}{/s}
		</div>
		<div>
			<span class="live{$uniquekey}{$sLiveshoppingData.ordernumber}_sec">0</span> {s name='LiveTimeSeconds'}{/s}
		</div>
		
		{if $sLiveshoppingData.max_quantity_enable}
			<div>{s name='LiveTimeRemaining'}{/s} <strong>{$sLiveshoppingData.max_quantity}</strong> {s name='LiveTimeRemainingPieces'}{/s}</div>
		{/if}
	</div>			
</div>
