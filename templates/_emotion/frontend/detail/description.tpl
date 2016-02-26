{block name="frontend_detail_description"}
<div id="description">
	
	{* Headline *}
	{block name='frontend_detail_description_title'}
		<h2>{s name="DetailDescriptionHeader"}{/s} "{$sArticle.articleName}"</h2>
	{/block}
	
	{* Properties *}
	{if $sArticle.sProperties}
		{block name='frontend_detail_description_properties'}
			<ul class="description_properties">
				{foreach from=$sArticle.sProperties item=sProperty}
				<li class="article_properties">
					<span class="property_name">
						{$sProperty.name}
					</span>
					<span class="property_value">
						{$sProperty.value}
					</span>
				</li>
				{/foreach}
			</ul>
		{/block}
	{/if}
	
	{* Article description *}
	{block name='frontend_detail_description_text'}
	{$sArticle.description_long|replace:"<table":"<table id=\"zebra\""}
	{/block}
	
	
	{* Links *}
	{block name='frontend_detail_description_links'}
		{if $sArticle.sLinks}
			<div class="space">&nbsp;</div>

			<h2>{se name="ArticleTipMoreInformation"}{/se} "{$sArticle.articleName}"</h2>
			{foreach from=$sArticle.sLinks item=information}
				{if $information.supplierSearch}
					<a href="{url controller='listing' action='manufacturer' sSupplier=$sArticle.supplierID}" target="{$information.target}" class="ico link">
						{se name="DetailDescriptionLinkInformation"}{/se}
					</a>
				{else}
					<a href="{$information.link}" target="{if $information.target}{$information.target}{else}_blank{/if}" rel="nofollow" class="ico link">
						{$information.description}
					</a>
				{/if}
			{/foreach}
		{/if}
	{/block}

    {* Supplier *}
    {block name='frontend_detail_description_supplier'}
    {if $sArticle.supplierDescription}
        <div class="space">&nbsp;</div>

        <h2>{se name="DetailDescriptionSupplier"}{/se} "{$sArticle.supplierName}"</h2>
		{$sArticle.supplierDescription}
    {/if}
    {/block}

	{* Downloads *}
	{block name='frontend_detail_description_downloads'}
	{if $sArticle.sDownloads}
		<div class="space">&nbsp;</div>
		<h2>{se name="DetailDescriptionHeaderDownloads"}{/se}</h2>
		
		{foreach from=$sArticle.sDownloads item=download}
			<a href="{$download.filename}" target="_blank" class="ico link">
				{se name="DetailDescriptionLinkDownload"}{/se} {$download.description}
			</a>		
		{/foreach}
	{/if}
	{/block}
	<div class="space">&nbsp;</div>
		
	{* Our comment *}
	{if $sArticle.attr3}
		{block name='frontend_detail_description_our_comment'}
		<div class="space">&nbsp;</div>
		<div id="unser_kommentar">
			<h2>{se name='DetailDescriptionComment'}{/se} "{$sArticle.articleName}"</h2>
			<blockquote>{$sArticle.attr3}</blockquote>
		</div>	
		{/block}
	{/if}
</div>
{/block}