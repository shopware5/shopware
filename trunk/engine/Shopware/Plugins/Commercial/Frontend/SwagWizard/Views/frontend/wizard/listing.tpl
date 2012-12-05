{extends file='frontend/wizard/index.tpl'}

{block name="frontend_index_header_css_screen" append}
	<link type="text/css" media="all" rel="stylesheet" href="{link file='frontend/_resources/styles/jquery/jquery-ui-1.8.6.custom.css'}" />
	<style type="text/css">
	/*<![CDATA[*/
		.filterRadio li {
		    padding: 4px 12px;
		}
		.filterRadio label, .filterRadio [type="checkbox"], .filterRadio input[type="radio"] {
			cursor:pointer;
			margin-left: 0;
		}
		.filterRadio [type="submit"] {
			 margin: 0;
		}
		.filterSlider {
			cursor:pointer;
		}

		#content #left h2 {
            border: none;
		    height: auto;
		}
		.filterSliderText {
			text-align:center;
            font-size:10px;
            font-weight:bold;
            padding:10px 0;
		}
		#content #center .listing_actions .articleperpage {
		    margin: 0
		}
		#content #center .listing_actions .list-settings {
		    width: 140px;
		}

        #content #left menu.filter {
            margin-bottom: 18px;
            border: 1px solid #C7C7C7;
        }

        #content #left menu.filterRadio li {
		    background-color: #E7F1D3;
		    border-top: 1px solid #fff;
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
		$('.filterSliderField').css('display', 'none');
{foreach from=$Wizard->getActiveFilters() item=WizardFilter}

{if $WizardFilter.typeID==4}
		var filterValues = {};
		var value = null;
		{foreach from=$WizardFilter->getValuesByArticleIds() item=WizardValue}
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
		var values = [ $('#filterValue{$WizardFilter.id}V0').val(), $('#filterValue{$WizardFilter.id}V1').val() ];
		var value = null;
		var filterValues = {};
		{for $WizardValue=$WizardFilter.range_from; $WizardValue<=$WizardFilter.range_to; $WizardValue=$WizardValue+$WizardFilter.steps}
			filterValues[{$WizardValue|round:2}] = [{$WizardValue|round:2}, "{$WizardValue|currency}"];
		{/for}
{/if}
{if $WizardFilter.steps}
	$('#filterSlider{$WizardFilter.id}').slider({
		step: {$WizardFilter.steps},
		min: {$WizardFilter.range_from},
		max: {$WizardFilter.range_to},
		range: range,
		values: values,
		value: value,
		filterValues: filterValues,
		animate: true,
		create: function(event, ui) {
			$(this).slider('option', 'slide').call($(this), null, $(this).slider('option'));
		},
		slide: function(event, ui) {
			var filterValues = $(this).slider('option', 'filterValues');
			if(ui.values) {
				var text = ['Von'];
				$.each(ui.values, function(index, value) {
					var v = filterValues[value];
					$('#filterValue{$WizardFilter.id}V'+index).attr('value', v[0]);
					text[index+1] = v[1];
				});
				$('#filterSliderText{$WizardFilter.id}').html(text.join(' bis '));
			} else if(filterValues[ui.value]) {
				var v = filterValues[ui.value];
				$('#filterSliderText{$WizardFilter.id}').html(v[1]);
				$('#filterValue{$WizardFilter.id}V'+v[0]).attr('checked', true);
			}
		}
	});
{/if}

{/foreach}
	});
	//]]>
	</script>
{/block}

{block name='frontend_index_content'}
<div id="center" class="grid_13">

	<div class="cat_text">
		<div class="inner_container">
			<h1>{$Wizard.name}</h1>
			<p>{$Wizard.description}</p>
		</div>
	</div>

	{if $WizardArticles|count}
		<h2>{s name="resultTitle"}Zu Ihren Kriterien wurden {$WizardCount} Artikel gefunden{/s}</h2>

		{if $WizardTemplate == 'table'}
			{assign var="sTemplate" value="listing-3col"}
			{assign var="sBoxMode" value="table"}
		{else}
			{assign var="sTemplate" value="listing-1col"}
			{assign var="sBoxMode" value="list"}
		{/if}

		{include file='frontend/listing/listing_actions.tpl'}

		<div class="listing" id="{$sTemplate}">
			{foreach from=$WizardArticles item=sArticle key=key name=list}
				{include file='frontend/listing/box_article.tpl'}
			{/foreach}
		</div>
		<div class="clear">&nbsp;</div>
		{include file='frontend/listing/listing_actions.tpl'}
	{else}
		<h2>{s name="noMatch"}Es konnten keine Artikel gefunden werden, die Ihren Kriterien entsprechen.{/s}</h2>
	{/if}
</div>
{/block}

{block name='frontend_index_left_categories'}
<form name="filter_form" method="post" action="{url action=listing wizardID=$Wizard.id}?{[
		'template'=>$WizardTemplate
	]|http_build_query:'':'&'}">
<menu class="filter">
{foreach from=$Wizard->getActiveFilters() item=WizardFilter}
	<li><h2 class="headingbox_nobg">{$WizardFilter.name|escape}</h2></li>
	<menu class="filterRadio">

	{if $WizardFilter.typeID eq 2 || $WizardFilter.typeID eq 4}
		{foreach from=$WizardFilter->getValuesByArticleIds() item=WizardValue}
			<li {if $WizardFilter.typeID eq 4}class="filterSliderField"{/if}>
				<input class="auto_submit" id="filterValue{$WizardFilter.id}V{$WizardValue.id}" type="radio" name="filter[{$WizardFilter.id}]" value="{$WizardValue.id}" {if $WizardSelection[$WizardFilter.id] eq $WizardValue.id} checked{/if}/>
				<label for="filterValue{$WizardFilter.id}V{$WizardValue.id}">{$WizardValue.value}</label>
			</li>
		{/foreach}
		{if $WizardFilter.typeID eq 4}
			<li class="hide_script">
				<div class="filterSliderText" id="filterSliderText{$WizardFilter.id}">&nbsp;</div>
	            <div class="filterSlider" id="filterSlider{$WizardFilter.id}"></div>
			</li>
			<li class="hide_script">
				<input id="filterValueReset{$WizardFilter.id}" type="radio" name="filter[{$WizardFilter.id}]" value="0" />
				<label for="filterValueReset{$WizardFilter.id}">Alle zeigen</label>
				<input type="submit" class="button-middle small right" value="Suchen">
			</li>
		{elseif $WizardSelection[$WizardFilter.id]}
			<li>
				<input class="auto_submit" id="filterValueReset{$WizardFilter.id}" type="radio" name="filter[{$WizardFilter.id}]" value="0" />
				<label for="filterValueReset{$WizardFilter.id}">Alle zeigen</label>
			</li>
		{/if}
	{elseif $WizardFilter.typeID eq 9}
		{$WizardFilterCountSteps = $WizardFilter.range_to-$WizardFilter.range_from/$WizardFilter.steps|round:2}
		<li class="filterSliderField">
			<label for="filterValue{$WizardFilter.id}V0">Von: </label>
			<input id="filterValue{$WizardFilter.id}V0" type="text" name="filter[{$WizardFilter.id}][0]"
				value="{if isset($WizardSelection[$WizardFilter.id][0])}{$WizardSelection[$WizardFilter.id][0]|round:2}{else}{$WizardFilter.range_from+round($WizardFilterCountSteps*0.25)}{/if}"
			/>
		</li>
		<li class="filterSliderField">
			<label for="filterValue{$WizardFilter.id}V0">Bis: </label>
			<input id="filterValue{$WizardFilter.id}V1" type="text" name="filter[{$WizardFilter.id}][1]"
				value="{if isset($WizardSelection[$WizardFilter.id][1])}{$WizardSelection[$WizardFilter.id][1]|round:2}{else}{$WizardFilter.range_from+round($WizardFilterCountSteps*0.75)}{/if}"
			/>
		</li>
		<li class="hide_script">
			<div class="filterSliderText" id="filterSliderText{$WizardFilter.id}">&nbsp;</div>
            <div class="filterSlider" id="filterSlider{$WizardFilter.id}"></div>
		</li>
		<li class="hide_script">
			<input class="" id="filterValue{$WizardFilter.id}V3" type="checkbox" name="filter[{$WizardFilter.id}][2]" value="1" {if $WizardSelection[$WizardFilter.id][2]} checked{/if}/>
			<label for="filterValue{$WizardFilter.id}V3">Aktiv</label>
			<input type="submit" class="button-middle small right" value="Suchen">
		</li>
	{elseif $WizardFilter.typeID eq 6}
		{foreach from=$WizardFilter->getValuesByArticleIds($WizardArticleIds) item=WizardValue key=WizardValueKey}
			<li>
				<input class="auto_submit" id="filterValue{$WizardFilter.id}V{$WizardValueKey}" type="checkbox" name="filter[{$WizardFilter.id}][]" value="{$WizardValue.id}" {if $WizardValue.id|in_array:$WizardSelection[$WizardFilter.id]} checked{/if}/>
				<label for="filterValue{$WizardFilter.id}V{$WizardValueKey}">{$WizardValue.value}</label>
			</li>
		{/foreach}
	{else}
		{foreach from=$WizardFilter->getValuesByArticleIds($WizardArticleIds) item=WizardValue key=WizardValueKey}
		{if empty($WizardSelection[$WizardFilter.id]) || $WizardSelection[$WizardFilter.id] eq $WizardValue.id}
			<li>
				<input class="auto_submit" id="filterValue{$WizardFilter.id}V{$WizardValueKey}" type="radio" name="filter[{$WizardFilter.id}]" value="{$WizardValue.id}" {if $WizardSelection[$WizardFilter.id] eq $WizardValue.id} checked{/if}/>
				<label for="filterValue{$WizardFilter.id}V{$WizardValueKey}">{$WizardValue.value}</label>
			</li>
		{/if}
		{/foreach}
		{if $WizardSelection[$WizardFilter.id]}
			<li>
				<input class="auto_submit" id="filterValueReset{$WizardFilter.id}" type="radio" name="filter[{$WizardFilter.id}]" value="0" />
				<label for="filterValueReset{$WizardFilter.id}">Alle zeigen</label>
			</li>
		{/if}
	{/if}
	</menu>
{/foreach}
</menu>
</form>
{/block}

{block name='frontend_listing_actions_sort'}{/block}

{block name="frontend_listing_actions_change_layout"}
	<div class="list-settings">
	<label>{s name='ListingActionsSettingsTitle'}Darstellung:{/s}</label>
	<a href="{url action=listing wizardID=$Wizard.id}?{[
		'template'=>'table',
		'page'=>$WizardPage,
		'perPage'=>$WizardPerPage,
		'filter'=>$WizardSelection
	]|http_build_query:'':'&'}" class="table-view {if $sBoxMode=='table'}active{/if}" title="{s name='ListingActionsSettingsTable'}Tabellen-Ansicht{/s}">
		&nbsp;
	</a>
	<a href="{url action=listing wizardID=$Wizard.id}?{[
		'template'=>'list',
		'page'=>$WizardPage,
		'perPage'=>$WizardPerPage,
		'filter'=>$WizardSelection
	]|http_build_query:'':'&'}" class="list-view {if $sBoxMode=='list'}active{/if}" title="{s name='ListingActionsSettingsList'}Listen-Ansicht{/s}">
		&nbsp;
	</a>
	</div>
{/block}

{block name='frontend_listing_actions_paging'}
{if $sPages.pages|@count != 1}
	<div class="bottom">
		<div class="paging">
			<label>{s name='ListingPaging'}Blättern{/s}</label>
			{if isset($sPages.before)}
				<a href="{url action=listing wizardID=$Wizard.id}?{[
		'template'=>$WizardTemplate,
		'page'=>$sPages.before,
		'perPage'=>$WizardPerPage,
		'filter'=>$WizardSelection
	]|http_build_query:'':'&'}" title="{s name='ListingLinkNext'}Vorherige Seite{/s}" class="navi prev">
					{s name="ListingTextPrevious"}&lt;{/s}
				</a>
			{/if}
			{foreach from=$sPages.pages item=page}
				{if $sPage==$page+1}
					<a title="" class="navi on">{$page+1}</a>
				{else}
					<a href="{url action=listing wizardID=$Wizard.id}?{[
		'template'=>$WizardTemplate,
		'page'=>{$page},
		'perPage'=>$WizardPerPage,
		'filter'=>$WizardSelection
	]|http_build_query:'':'&'}" title="" class="navi">
						{$page+1}
					</a>
				{/if}
			{/foreach}
			{if $sPages.next}
				<a href="{url action=listing wizardID=$Wizard.id}?{[
		'template'=>$WizardTemplate,
		'page'=>$sPages.next,
		'perPage'=>$WizardPerPage,
		'filter'=>$WizardSelection
	]|http_build_query:'':'&'}" title="{s name='ListingLinkPrevious'}N�chste Seite{/s}" class="navi more">{s name="ListingTextNext"}&gt;{/s}</a>
			{/if}
		</div>
		<div class="display_sites">
			{se name="ListingTextSite"}Seite{/se} <strong>{$sPage}</strong> {se name="ListingTextFrom"}von{/se} <strong>{$sNumberPages}</strong>
		</div>
	</div>
{/if}
{/block}

{block name='frontend_listing_actions_items_per_page'}
{if $sPerPage}
	<form method="post" action="{url action=listing wizardID=$Wizard.id}?{[
		'template'=>$WizardTemplate,
		'filter'=>$WizardSelection
	]|http_build_query:'':'&'}">
	<div class="articleperpage rightalign">
		<label>{s name='ListingLabelItemsPerPage'}Artikel pro Seite:{/s}</label>
		<select name="perPage" class="auto_submit">
		{foreach from=$sPerPages item=perPage}
	        <option value="{$perPage}" {if $perPage eq $sPerPage}selected="selected"{/if}>{$perPage}</option>
		{/foreach}
		</select>
	</div>
	</form>
{/if}
{/block}

{block name="frontend_listing_actions_class"}
<div class="listing_actions{if !$sPages || $sPages.count <= 1} normal{/if}">
{/block}
