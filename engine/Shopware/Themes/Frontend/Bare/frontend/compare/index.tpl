{if $sComparisons}
<div id="compareContainer" onmouseover="jQuery.compare.showCompare();" onmouseout="jQuery.compare.hideCompare();">
	<span id="compareHighlight">{se name="CompareInfoCount"}Mein Vergleich{/se} <span class="comparisons">{$sComparisons|@count}</span></span>
</div>
<div id="compareContainerResults" onmouseover="jQuery.compare.showCompare();" onmouseout="jQuery.compare.hideCompare()">
<div class="arrow"></div>
	<ul>
		{foreach from=$sComparisons item=compare}
		<li>
			<div>{$compare.articlename|truncate:30}</div>
			<a href="{url controller='compare' action='delete_article' articleID=$compare.articleID}" rel="nofollow" class="del_comp compare_delete_article">&nbsp;</a>
		</li>
		{/foreach}
		
		
		<li class="bt_compare">
			<a href="{url controller='compare' action='overlay' forceSecure}" rel="nofollow" class="bt_compare compare_get_overlay" onclick="return false;">
				{se name="CompareActionStart"}{/se}
			</a>
		</li>
		
		<li class="last">
			<a href="{url controller='compare' action='delete_all' forceSecure}" rel="nofollow" class="bt_compare_del compare_delete_all" onclick="return false;">
				{se name="CompareActionDelete"}{/se}
			</a>
		</li>
	</ul>
</div>
{/if}