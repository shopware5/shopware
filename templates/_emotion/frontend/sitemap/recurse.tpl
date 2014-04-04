
{foreach from=$sCategoryTree item=categoryTree}
    {if $categoryTree.hideOnSitemap}
        {continue}
    {/if}
    {if $depth==1}
    {elseif $depth==2}
    {/if}
    {if $categoryTree.sub}
        <ul>
            <li>
                <a href="{$categoryTree.link}" title="{$categoryTree.name}" class="active">{$categoryTree.name}</a>
                {if $depth>=1}<ul>{/if}{include file="frontend/sitemap/recurse.tpl" sCategoryTree=$categoryTree.sub depth=$depth+1}{if $depth>=1}</ul>{/if}
            </li>
        </ul>
    {else}
    	{if $depth==1}<ul>{/if}<li>
        	<a href="{$categoryTree.link}" title="{$categoryTree.name}">{$categoryTree.name}</a></li>{if $depth==1}</ul>{/if}
    	{/if}
{/foreach}
