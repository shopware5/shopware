{extends file='parent:frontend/detail/navigation.tpl'}

{* Article navigation *}
{block name='frontend_detail_menu_top'}
	
	{* Breadcrumb *}
	<div id="breadcrumb" class="detail">
		{if !{config name=disableArticleNavigation}}
			<div class="article_overview">
				<a href="{$sArticle.sNavigation.sCurrent.sCategoryLink|rewrite:$sArticle.sNavigation.sCurrent.sCategoryName}" title="{$sArticle.sNavigation.sCurrent.sCategoryName}">{s name='DetailNavIndex'}{/s}</a>
			</div>
		{/if}
		{if $sShopname}
			<a href="{url controller='index'}">
				{$sShopname}
			</a>
		{/if}
		
		{foreach name=breadcrumb from=$sBreadcrumb item=breadcrumb}
			{if $breadcrumb.name}
				{if $smarty.foreach.breadcrumb.last}
					<span class="sep">/</span>
					<a href="{if $breadcrumb.link}{$breadcrumb.link}{else}#{/if}" title="{$breadcrumb.name}" class="last">
						<strong>{$breadcrumb.name}</strong>
					</a>
				{else} 
					<span class="sep">/</span>
					<a href="{if $breadcrumb.link}{$breadcrumb.link}{else}#{/if}" title="{$breadcrumb.name}">
						{$breadcrumb.name}
					</a>
				{/if}
			{/if}
		{/foreach}
		</div>	
	
	<div class="clear">
{/block}
