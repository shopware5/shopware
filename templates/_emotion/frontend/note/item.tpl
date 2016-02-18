<div class="table_row {if $lastrow} lastrow{/if}">

	{* Reviews *}
	{if !{config name=VoteDisable} && $sBasketItem.sVoteAverange.averange}
		<div class="rating{if !$sUserLoggedIn} full_length{/if}">
			<div class="star star{($sBasketItem.sVoteAverange.averange*2)|round}"></div>
		</div>
	{/if}

    {if $sBasketItem.sConfigurator}
        {$detailLink={url controller="detail" sArticle=$sBasketItem.articleID number=$sBasketItem.ordernumber}}
    {else}
        {$detailLink=$sBasketItem.linkDetails}
    {/if}

	{* Article informations *}
	<div class="grid_12">

		{* Article picture *}
		{if $sBasketItem.image.src.0}
			<a href="{$detailLink}" title="{$sBasketItem.articlename}" class="thumb_image">
				<img src="{$sBasketItem.image.src.2}" border="0" alt="{$sBasketItem.articlename}" />
			</a>
			{* Zoom picture *}
			<a href="{$sBasketItem.image.src.5}" rel="lightbox" class="zoom_picture">
				{s name='NoteLinkZoom'}{/s}
			</a>
		{else}
			<a href="{$detailLink}" title="{$sBasketItem.articlename}" class="thumb_image">
				<img class="no_image" src="{link file='frontend/_resources/images/no_picture.jpg'}" alt="{$sBasketItem.articlename}" />
			</a>
		{/if}



		{* Delivery informations *}
		{if {config name=BASKETSHIPPINGINFO}}
			<div class="delivery{if !$sUserLoggedIn} full_length{/if}{if {config name=VoteDisable}} vote_disabled{/if}"  >
				{include file="frontend/plugins/index/delivery_informations.tpl" sArticle=$sBasketItem}
			</div>
		{/if}

		<div class="basket_details">
			{* Article name *}
			<a class="title" href="{$detailLink}" title="{$sBasketItem.articlename}">
				{$sBasketItem.articlename|truncate:40}
			</a>

			{* Supplier name *}
			<div class="supplier">
				{s name='NoteInfoSupplier'}{/s} {$sBasketItem.supplierName}
			</div>

			{* Order number *}
			<p class="ordernumber">
				{se name='NoteInfoId'}{/se} {$sBasketItem.ordernumber}
			</p>

			{* Article Description *}
			<p class="desc">
				{$sBasketItem.description_long|strip_tags|trim|truncate:160}

				{* Unit price *}
				{block name="frontend_note_item_unitprice"}
				{if $sBasketItem.purchaseunit}
						<div class="article_price_unit">
						<p>
							<strong>{se name="NoteUnitPriceContent"}{/se}:</strong> {$sBasketItem.purchaseunit} {$sBasketItem.sUnit.description}
							{if $sBasketItem.purchaseunit != $sBasketItem}
								{if $sBasketItem.referenceunit}
									({$sBasketItem.referenceprice|currency} {s name="Star" namespace="frontend/listing/box_article"}{/s} / {$sBasketItem.referenceunit} {$sBasketItem.sUnit.description})
								{/if}
							{/if}
						</p>
					</div>
				{/if}
				{/block}
			</p>
			{if $sBasketItem.datum_add}
				{* added date *}
				<div class="date">
					{s name='NoteInfoDate'}Hinzugefügt am:{/s} {$sBasketItem.datum_add|date:DATE_MEDIUM}
				</div>
			{/if}
			{block name="frontend_note_index_items"}{/block}
		</div>

	</div>

	{* Unit price *}
	<div>
		{block name="frontend_note_item_price"}
		{if $sBasketItem.itemInfo}
			{$sBasketItem.itemInfo}
		{else}
			<strong class="price">{if $sBasketItem.priceStartingFrom}{s namespace='frontend/listing/box_article' name='ListingBoxArticleStartsAt'}{/s} {/if}{$sBasketItem.price|currency}*</strong>
		{/if}

		{* Remove article *}
		<a href="{url controller='note' action='delete' sDelete=$sBasketItem.id}" class="delete" title="{s name='NoteLinkDelete'}{/s}">
			{se name='NoteLinkDelete'}{/se}
		</a>

		{* Additional links *}
		{block name="frontend_note_item_actions"}
		<div class="action">
			{* Place article in basket *}
			{if !$sBasketItem.sConfigurator && !$sBasketItem.sVariantArticle}
			<a href="{url controller=checkout action=addArticle sAdd=$sBasketItem.ordernumber}" class="basket" title="{s name='NoteLinkBuy'}{/s}">
				{s name='NoteLinkBuy'}{/s}
			</a>
			{/if}

			{* Compare article *}
			{block name="frontend_note_item_actions_compare"}
                <a href="{url controller='compare' action='add_article' articleID=$sBasketItem.articleID}" class="compare_add_article" title="{s name='ListingBoxLinkCompare'}{/s}" rel="nofollow">
                    {s name='ListingBoxLinkCompare'}{/s}
                </a>
			{/block}

			{* Article Details *}
			<a href="{$detailLink}" class="detail" title="{$sBasketItem.articlename}">
				{se name='NoteLinkDetails'}{/se}
			</a>
		</div>
		{/block}
	{/block}
	</div>
</div>
