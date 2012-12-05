{extends file='frontend/wizard/index.tpl'}

{block name="frontend_index_header_css_screen" append}
	<link type="text/css" media="all" rel="stylesheet" href="{link file='frontend/_resources/styles/jquery/jquery-ui-1.8.6.custom.css'}" />
	<style type="text/css">
	/*<![CDATA[*/
		#center.wizard_filter .inner_container {
		    border-top:medium none;padding:20px;
		}
		.wizard_filter .filterValue {
			text-align:center;font-size:14px;font-weight:bold;padding:10px 0;
		}
		.wizard_filter .filterSlider {
			cursor:pointer;
		}
		.filterSlider .ui-corner-all {
			border: 1px solid #777777;
		}
	/*//]]>*/
	</style>
{/block}

{block name='frontend_index_header_javascript' append}
	<script type="text/javascript" src="{link file='frontend/_resources/javascript/jquery-ui-1.8.6.custom.min.js'}"></script>
	<script type="text/javascript">
	//<![CDATA[
	jQuery(document).ready(function($) {
		{if $WizardSelection && $Wizard.preview}

        {if $isEmotion}
            var sliderClass = ".wizard-slider";
        {else}
            var sliderClass = ".slider";
        {/if}

		$(sliderClass).ajaxSlider('ajax', {
			'url': '{url action="result"}?{["wizardID"=>$Wizard.id, "filter"=>$WizardSelection]|http_build_query:"":"&"}',
			'title': '{s name="Results"}Ergebnisse{/s}:',
			'headline': true,
			'navigation': false,
			'scrollSpeed': 800,
			'rotate': false,
			'height':440,
			'containerCSS': { 'marginTop': '0px', 'marginBottom': '15px' }
		});
		{/if}
		{if $WizardFilter.typeID==4 || $WizardFilter.typeID==3}
			var filterValues = {};
			var value = null;
			{foreach from=$WizardValues item=WizardValue}
				{if $WizardValue@first}
					value = '{$WizardValue.key}';
				{/if}
				filterValues['{$WizardValue.key}'] = [{$WizardValue.id}, "{$WizardValue.value|escape}"];
				{if $WizardSelection[$WizardFilter.id] && $WizardValue.id==$WizardSelection[$WizardFilter.id]}
				value = '{$WizardValue.key}';
				{/if}
			{/foreach}
			var range = false;
			var values = null;
		{elseif $WizardFilter.typeID==9}
			var range = true;
			var values = [ $('#filterValue0').val(), $('#filterValue1').val() ];
			var value = null;
			var filterValues = {};
			{for $WizardValue=$WizardFilter.range_from; $WizardValue<=$WizardFilter.range_to; $WizardValue=$WizardValue+$WizardFilter.steps}
				filterValues[{$WizardValue|round:2}] = [{$WizardValue|round:2}, "{$WizardValue|currency}"];
			{/for}
		{/if}
		{if $WizardFilter.steps}
			$('.filterRadio').css('display', 'none');
			$('.filterSlider').slider({
				step: {$WizardFilter.steps},
				min: {$WizardFilter.range_from},
				max: {$WizardFilter.range_to},
				range: range,
				values: values,
				value: value,
				animate: true,
				create: function(event, ui) {
					$(this).slider('option', 'slide').call($(this), null, $(this).slider('option'));
				},
				slide: function(event, ui) {
					if(ui.values) {
						var text = ['Von'];
						$.each(ui.values, function(index, value) {
							var v = filterValues[value];
							$('#filterValue'+index).attr('value', v[0]);
							text[index+1] = v[1];
						});
						$('.filterValue').html(text.join(' bis '));
					} else if(filterValues[ui.value]) {
						var v = filterValues[ui.value];
						$('.filterValue').html(v[1]);
						$('#filterValue'+v[0]).attr('checked', true);
					}
				}
			});
			{if $WizardSelection[$WizardFilter.id]}
				{$WizardSelectionValue=$WizardValues[$WizardSelection[$WizardFilter.id]]}
			{else}
				{$WizardSelectionValue=$WizardValues|current}
			{/if}
		{/if}
	});
	//]]>
	</script>
{/block}

{block name='frontend_index_content'}
<div id="center" class="grid_13 wizard_filter">
	{if $WizardFilter}
	<form name="filter_form" method="GET" action="{url action=filter wizardID=$Wizard.id}">
		<input type="hidden" name="page" value="{$WizardPage+1}" />
		{foreach from=$WizardSelection item=SelectionValue key=SelectionKey}
			{if $SelectionValue|is_array}
				{foreach from=$SelectionValue item=MultiSelectionValue key=MultiSelectionKey}
					<input type="hidden" name="filter[{$SelectionKey|escape}][{$MultiSelectionKey|escape}]" value="{$MultiSelectionValue|escape}" />
				{/foreach}
			{else}
				<input type="hidden" name="filter[{$SelectionKey|escape}]" value="{$SelectionValue|escape}" />
			{/if}
		{/foreach}
   		<div class="cat_text">
        	<h2 class="headingbox_dark largesize">{$WizardPage}. {$WizardFilter.name}
        		{if $WizardCount && $WizardSelection}
	        		<a href="{url action=result wizardID=$Wizard.id}?{['template'=>'list', 'page'=>$WizardPage+1, 'filter'=>$WizardSelection]|http_build_query:'':'&'}" style="float:right;padding-right:5px;">
	        			[{$WizardCount} {s name="articlesfound"}Ergebnisse{/s}]
	        		</a>
        		{/if}
        	</h2>
        	<div class="inner_container">

        	{$WizardFilter.description}

        	<ul class="filterRadio">
            {foreach from=$WizardValues item=WizardValue key=WizardValueKey}
            	<li>
            		{if $WizardFilter.typeID eq 6}
						<input id="filterValue{$WizardValue.id}" type="checkbox" name="filter[{$WizardFilter.id}][]" value="{$WizardValue.id}" {if $WizardValue.id|in_array:$WizardSelection[$WizardFilter.id]} checked{/if}/>
					{else}
						<input id="filterValue{$WizardValue.id}" type="radio" name="filter[{$WizardFilter.id}]" value="{$WizardValue.id}" {if $WizardSelection[$WizardFilter.id] eq $WizardValue.id} checked{/if}/>
					{/if}
					<label for="filterValue{$WizardValue.id}">{$WizardValue.value}</label>
				</li>
            {/foreach}
            {if $WizardFilter.typeID eq 9}
           		{$WizardFilterCountSteps = $WizardFilter.range_to-$WizardFilter.range_from/$WizardFilter.steps|round:2}
            	<li>
            		<label for="filterValue0">Von: </label>
					<input id="filterValue0" type="text" name="filter[{$WizardFilter.id}][0]"
						value="{if isset($WizardSelection[$WizardFilter.id][0])}{$WizardSelection[$WizardFilter.id][0]|round:2}{else}{$WizardFilter.range_from+round($WizardFilterCountSteps*0.25)}{/if}"
					 />
				</li>
				<li>
            		<label for="filterValue0">Bis: </label>
            		<input id="filterValue1" type="text" name="filter[{$WizardFilter.id}][1]"
					 	value="{if isset($WizardSelection[$WizardFilter.id][1])}{$WizardSelection[$WizardFilter.id][1]|round:2}{else}{$WizardFilter.range_from+round($WizardFilterCountSteps*0.75)}{/if}"
					  />
            		<input type="hidden" name="filter[{$WizardFilter.id}][2]" value="1" />
				</li>
			{/if}
			</ul>

            {if $WizardFilter.steps}
            <div class="filterValue">&nbsp;</div>
            <div class="filterSlider"></div>
            {/if}

            <hr class="clear space">
            </div>
        </div>
       	<hr class="clear" />

		<div class="actions">
		{if $WizardPage}
			<a href="{url action=filter wizardID=$Wizard.id}?{['page'=>$WizardPage-1, 'wizardID'=>$Wizard.id, 'filter'=>$WizardSelection]|http_build_query:'':'&'}" class="button-middle large" title="Zur&uuml;ck">{s name="Zurück"}Zurück{/s}</a>
		{/if}
			<input type="submit" value="{s name='Weiter'}Weiter{/s}" class="button-right large right">
			<hr class="clear space">
		</div>
   	</form>
   	{/if}

    {if $isEmotion}
   	<div class="wizard-slider"></div>
    {else}
   	<div class="slider"></div>
    {/if}
</div>
{/block}
