{if $sSearchResults.sResults}
	<div class="searchresult_top"></div>
	<div class="searchresult_inner">
		<a href="{url controller='search' sSearch=$sSearchRequest.sSearch}" class="resultall">
			{s name='SearchAjaxLinkAllResults'}Alle Ergebnisse anzeigen{/s}
			<span class="result_number">({$sSearchResults.sArticlesCount} {s name='SearchAjaxInfoResults'}Treffer{/s})</span>
		</a>
		<ul class="searchresult">
		{foreach $sSearchResults.sResults as $search_result}
			<li class="searchresult">
				{if $search_result.thumbNails.1}
					<a href="{$search_result.link}" class="searchthumb" title="{$search_result.name}">
						<img src="{$search_result.thumbNails.1}" class="resultimage">
					</a>
				{else}
					{if !isset($sArticle.image.src)}
						<div class="searchthumb">
							<img src="{link file='frontend/_resources/images/no_picture.jpg'}" alt="{s name='ListingBoxNoPicture'}{/s}" />
						</div>
					{/if}
				{/if}
				<div class="searchinner">
					<a href="{$search_result.link}" class="resultlink">
						<h3>{$search_result.name}</h3>
					</a>
					<div class="searchdescription">{$search_result.description|strip_tags|truncate:200}</div>
				</div>
			</li>
		{/foreach}
		</ul>
		</li>
	</div>
	<div class="searchresult_cap"></div>
{/if}