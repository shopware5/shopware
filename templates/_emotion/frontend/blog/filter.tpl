
{if $sFilterDate && $sFilterDate|@count > 1}
	{* Filter by date *}
	{block name='frontend_blog_filter_date'}
	<h2 class="headingbox">{se name="BlogHeaderFilterDate"}{/se}</h2>
	<div class="blogFilter">
	        <ul>
	        {foreach name=filter from=$sFilterDate item=date}
                {if !$date.removeProperty}
                    {if $smarty.get.sFilterDate==$date.dateFormatDate}
                        {assign var=filterDateActive value=true}
                        <li class="active"><a href="{$date.link}" title="{$date.dateFormatDate}" class="active">{$date.dateFormatDate|date_format:"{s name="BlogHeaderFilterDateFormat"}{/s}"} ({$date.dateCount})</a></li>
                    {else}
                        <li {if $smarty.foreach.filter.last}class="last"{/if}><a href="{$date.link}" title="{$date.dateFormatDate}">{$date.dateFormatDate|date_format:"{s name="BlogHeaderFilterDateFormat"}{/s}"} ({$date.dateCount})</a></li>
                    {/if}
                {elseif $filterDateActive}
                    <li class="close"><a href="{$date.link}" title="{s name='FilterLinkDefault' namespace='frontend/listing/filter_properties'}{/s}">{s name='FilterLinkDefault' namespace='frontend/listing/filter_properties'}{/s}</a></li>
                {/if}
			{/foreach}

	    </ul>
	</div>
	{/block}
{/if}

{if $sFilterAuthor && $sFilterAuthor|@count > 1}
	{* Filter by author *}
	{block name='frontend_blog_filter_author'}
	<h2 class="headingbox">{se name="BlogHeaderFilterAuthor"}{/se}</h2>
	<div class="blogFilter">
	        <ul>
                {foreach name=filterAuthor from=$sFilterAuthor item=author}
                    {if !$author.removeProperty}
                        {if $smarty.get.sFilterAuthor==$author.name|urlencode}
                            {assign var=filterAuthorActive value=true}
                            <li class="active"><a href="{$author.link}" title="{$author.name}" class="active">{$author.name} ({$author.authorCount})</a></li>
                        {else}
                            <li {if $smarty.foreach.filterAuthor.last}class="last"{/if}><a href="{$author.link}" title="{$author.name}">{$author.name} ({$author.authorCount})</a></li>
                        {/if}
                    {elseif $filterAuthorActive}
                        <li class="close"><a href="{$author.link}" title="{s name='FilterLinkDefault' namespace='frontend/listing/filter_properties'}{/s}">{s name='FilterLinkDefault' namespace='frontend/listing/filter_properties'}{/s}</a></li>
                    {/if}
                {/foreach}
	    </ul>
	</div>
	{/block}
{/if}

{if $sFilterTags && $sFilterTags|@count > 1}
    {* Filter by tags *}
    {block name='frontend_blog_filter_tags'}
        <h2 class="headingbox">{se name="BlogHeaderFilterTags"}{/se}</h2>
        <div class="blogFilter">
            <ul>
                {foreach name=filterTags from=$sFilterTags item=tag}
                    {if !$tag.removeProperty}
                        {if $smarty.get.sFilterTags==$tag.name|urlencode}
                            {assign var=filterTagsActive value=true}
                            <li class="active"><a href="{$tag.link}" title="{$tag.name}" class="active">{$tag.name} ({$tag.tagsCount})</a></li>
                            {else}
                            <li {if $smarty.foreach.filterTags.last}class="last"{/if}><a href="{$tag.link}" title="{$tag.name}">{$tag.name} ({$tag.tagsCount})</a></li>
                        {/if}
                    {elseif $filterTagsActive}
                        <li class="close"><a href="{$tag.link}" title="{s name='FilterLinkDefault' namespace='frontend/listing/filter_properties'}{/s}">{s name='FilterLinkDefault' namespace='frontend/listing/filter_properties'}{/s}</a></li>
                    {/if}
                {/foreach}
            </ul>
        </div>
    {/block}
{/if}
