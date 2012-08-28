<div id="mainNavigation">
	<ul>
	    {foreach from=$sMainCategories item=sCategory}
	    {if !$sCategory.hidetop}
			<li {if $sCategory.flag} class="active"{/if}>
	        	<a href="{$sCategory.link}" title="{$sCategory.description}" {if $sCategory.flag} class="active"{/if}>
	        		<span>{$sCategory.description}</span>
	        	</a>
	        </li>
        {/if}
		{/foreach}
	</ul>
</div>