{* Last seen articles *}
<div class="viewlast">
	<h2 class="heading">{s name='WidgetsRecentlyViewedHeadline'}{/s}</h2>
	<ul>
    {foreach from=$sLastArticles item=sArticle}
        <li {if $sArticle@last}class="lastview_rule_last"{else}class="lastview_rule"{/if}>
        	{if $sArticle.img}
        		<a href="{$sArticle.linkDetails|rewrite:$sArticle.name}" title="{$sArticle.name}" class="article_image" style="background: #fff url({$sArticle.img}) no-repeat center center;">
        			<span class="hidden">{$sArticle.name}</span>
        		</a>
        	{else}
        		<a href="{$sArticle.linkDetails|rewrite:$sArticle.name}" title="{$sArticle.name}" class="article_image no_picture">
        			<span class="hidden">{se name='WidgetsRecentlyViewedLinkDetails'}{/se}</span>
        		</a>
        	{/if}
        	<a href="{$sArticle.linkDetails|rewrite:$sArticle.name}" title="{$sArticle.name}" class="article_description">
        		{$sArticle.name|truncate:50}
        	</a>
		</li>
	{/foreach}
	</ul>
</div>