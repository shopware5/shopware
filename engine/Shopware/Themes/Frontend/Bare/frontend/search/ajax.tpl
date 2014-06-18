{if $sSearchResults.sResults}
	<div class="searchresult_top"></div>

    {block name="search_ajax_inner"}
	<div class="search-results--inner">

		{foreach $sSearchResults.sResults as $search_result}
        <ul>

            {block name="search_ajax_inner_item"}
			<li>
                <a href="{$search_result.link}" title="{$search_result.name}">
                {if $search_result.thumbNails.0 && file_exists($search_result.thumbNails.0)}
                    <img src="{$search_result.thumbNails.0}" class="resultimage" width="30">
                {else}
                    <img src="{link file='frontend/_public/src/img/no--picture.jpg'}" alt="{s name='ListingBoxNoPicture'}{/s}" width="30" />
                {/if}
                </a>
            </li>
            <li>
                <a href="{$search_result.link}" title="{$search_result.name}">
                    {$search_result.name}
                </a>
            </li>
            <li>{$search_result.price|currency} *</li>

				{*{if $search_result.thumbNails.1}*}
					{*<a href="{$search_result.link}" class="searchthumb" title="{$search_result.name}">*}
						{*<img src="{$search_result.thumbNails.1}" class="resultimage" style="margin: 0pt 5px 0pt 0pt;">*}
					{*</a>*}
				{*{else}*}
					{*{if !isset($sArticle.image.src)}*}
						{*<div class="searchthumb">*}
							{*<img src="{link file='frontend/_resources/images/no_picture.jpg'}" alt="{s name='ListingBoxNoPicture'}{/s}" />*}
						{*</div>*}
					{*{/if}*}
				{*{/if}*}
				{*<div class="searchinner">*}
					{*<a href="{$search_result.link}" class="resultlink">*}
						{*<h3>{$search_result.name}</h3>*}
					{*</a>*}
					{*<div class="searchdescription">{$search_result.description|strip_tags|truncate:200}</div>*}
				{*</div>*}
			</li>
            {/block}

        </ul>
		{/foreach}


        {block name="search_ajax_all_results"}
        <a href="{url controller='search' sSearch=$sSearchRequest.sSearch}" class="resultall">
            <i class="icon--arrow-right" style="font-size:9px"></i> alle Ergebnisse anzeigen
            <span class="result_number"></span>
        </a>

        <div style="text-align: right; margin-top:12px;">{$sSearchResults.sArticlesCount} {s name='SearchAjaxInfoResults'}Treffer{/s}</div>
        {/block}

	</div>
    {/block}

	<div class="searchresult_cap"></div>
{/if}