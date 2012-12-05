{extends file='frontend/wizard/index.tpl'}

{* Main content *}
{block name='frontend_index_content'}
<div id="center" class="grid_13">

{if $WizardArticles|@count}
		<h2>{s name="resultTitle"}Zu Ihren Kriterien wurden {$WizardCount} Artikel gefunden{/s}</h2>

		{if $WizardTemplate == 'table'}
			{assign var="sTemplate" value="listing"}
			{assign var="sBoxMode" value="table"}
		{else}
			{assign var="sTemplate" value="listing-1col"}
			{assign var="sBoxMode" value="list"}
		{/if}
		{include file='frontend/listing/listing_actions.tpl'}

		<div class="listing" id="{$sTemplate}">
			{foreach from=$WizardArticles item=sArticle key=key name=list}
				{include file='frontend/listing/box_article.tpl' sTemplate='listing'}
			{/foreach}
		</div>

		{include file='frontend/listing/listing_actions.tpl' sTemplate=$sTemplate}
	{else}
		<h2>{s name="noMatch"}Es konnten keine Artikel gefunden werden, die Ihren Kriterien entsprechen.{/s}</h2>
	{/if}

	<hr class="clear space">
	<div class="actions">
	{if $WizardPage}
		<a href="{url action=filter wizardID=$Wizard.id}?{['page'=>$WizardPage-1, 'filter'=>$WizardSelection]|http_build_query:'':'&'}" class="button-middle large" title="Zur&uuml;ck">{s name="back"}Zur&uuml;ck{/s}</a>
	{/if}
		<a href="{url action=index wizardID=$Wizard.id}" class="button-right large right" title="Berater neustarten">{s name="restart"}Berater neustarten{/s}</a>
		<hr class="clear space">
	</div>

    {if $isEmotion}
        <div class="wizard-slider"></div>
    {else}
        <div class="slider"></div>
    {/if}
</div>
{/block}

{block name='frontend_listing_actions_sort'}{/block}

{block name="frontend_listing_actions_change_layout"}
	<div class="list-settings">
	<label>{s name='ListingActionsSettingsTitle'}Darstellung:{/s}</label>
	<a href="{url action=result wizardID=$Wizard.id}?{['template'=>'table', 'page'=>$WizardPage, 'filter'=>$WizardSelection]|http_build_query:'':'&'}" class="table-view {if $sBoxMode=='table'}active{/if}" title="{s name='ListingActionsSettingsTable'}Tabellen-Ansicht{/s}">&nbsp;</a>
	<a href="{url action=result wizardID=$Wizard.id}?{['template'=>'list', 'page'=>$WizardPage, 'filter'=>$WizardSelection]|http_build_query:'':'&'}" class="list-view {if $sBoxMode=='list'}active{/if}" title="{s name='ListingActionsSettingsList'}Listen-Ansicht{/s}">&nbsp;</a>
	</div>
{/block}

{block name='frontend_index_header_javascript' append}
	<script type="text/javascript">
	//<![CDATA[
	jQuery(document).ready(function($) {
		{if $Wizard.show_other}

            {if $isEmotion}
                var sliderClass = ".wizard-slider";
            {else}
                var sliderClass = ".slider";
            {/if}

            $(sliderClass).ajaxSlider('ajax', {
			'url': '{url action="result"}?offset={$WizardCount}&hide_empty={if $WizardRealCount>$WizardCount}1{else}0{/if}&max_quantity=0&{["wizardID"=>$Wizard.id, "filter"=>$WizardSelection]|http_build_query:"":"&"}',
			'title': '{s name="more"}Weitere Interessante Produkte:{/s}',
			'headline': true,
			'navigation': false,
			'scrollSpeed': 800,
			'rotate': false,
			'width':628,
			'height':440,
			'containerCSS': { 'marginTop': '0px', 'marginBottom': '15px' }
		});
		{/if}
	});
	//]]>
	</script>
{/block}
