{extends file='parent:frontend/detail/description.tpl'}

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

{* Links *}
{block name='frontend_detail_description_links'}
    {if $sArticle.sLinks}
        <div class="space">&nbsp;</div>

        <h2>{se name="ArticleTipMoreInformation"}{/se} "{$sArticle.articleName}"</h2>
        {foreach from=$sArticle.sLinks item=information}
            {if $information.supplierSearch}
                <a href="{url controller='supplier' sSupplier=$sArticle.supplierID}" target="{$information.target}" class="ico link">
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