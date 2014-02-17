
{* Compare container *}
{block name='frontend_index_navigation_inline' append}
<div id="compareContainerAjax">
	{include file='frontend/compare/index.tpl'}
</div>
{/block}

{* Compare result *}
{block name='frontend_index_body_inline' append}
<div id="compare_bigbox"></div>
{/block}


{* Compare button *}
{block name='frontend_listing_box_article_actions_inline' prepend}
	<a href="{url controller='compare' action='add_article' articleID=$sArticle.articleID}" rel="nofollow" title="{s name='ListingBoxLinkCompare'}vergleichen{/s}" class="compare_add_article hide_script">{se name='ListingBoxLinkCompare'}{/se}</a>
{/block}

{* Compare javascript *}
{block name='frontend_index_header_javascript_inline' prepend}
	var compareCount = '{$sComparisons|count}';
	var compareMaxCount = '{config name="MaxComparisons"}';
{literal}
	jQuery(document).ready(function() {
		jQuery.compare.setup();
	});
{/literal}
{/block}

{* Compare button 2 *}
{block name='frontend_detail_actions_contact' append}
	<li>
		<a href="{url controller='compare' action='add_article' articleID=$sArticle.articleID}" rel="nofollow" title="{s name='DetailActionLinkCompare'}Artikel vergleichen{/s}" class="compare_add_article hide_script">
			{se name="DetailActionLinkCompare"}{/se}
		</a>
	</li>
{/block}

{* Compare button note *}
{block name='frontend_note_item_actions_compare'}
	<a href="{url controller='compare' action='add_article' articleID=$sBasketItem.articleID}" class="compare_add_article" title="{s name='ListingBoxLinkCompare'}{/s}" rel="nofollow">
		{s name='ListingBoxLinkCompare'}{/s}
	</a>
{/block}


