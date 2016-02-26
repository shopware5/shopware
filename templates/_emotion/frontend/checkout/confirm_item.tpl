
{extends file='frontend/checkout/cart_item.tpl'}

{* Article image *}
{block name='frontend_checkout_cart_item_image'}
    {if $sBasketItem.additional_details.sConfigurator}
        {$detailLink={url controller=detail sArticle=$sBasketItem.articleID number=$sBasketItem.ordernumber}}
    {else}
        {$detailLink={url controller=detail sArticle=$sBasketItem.articleID forceSecure}}
    {/if}

    {if $sBasketItem.image.src.0}
        <a href="{$detailLink}" title="{$sBasketItem.articlename|strip_tags}" class="thumb_image{if {config name=detailmodal}} detail-modal{/if}" target="_blank">
            <img src="{$sBasketItem.image.src.1}" border="0" alt="{$sBasketItem.articlename}" />
        </a>
    {else}
        <a href="{$detailLink}" title="{$sBasketItem.articlename|strip_tags}" class="thumb_image{if {config name=detailmodal}} detail-modal{/if}" target="_blank">
            <img class="no_image" src="{link file='frontend/_resources/images/no_picture.jpg'}" alt="{$sBasketItem.articlename}" />
        </a>
    {/if}
{/block}

{* Article name and order number *}
{block name='frontend_checkout_cart_item_details'}

    {* Add a hidden AGB Checkbox into the form *}
    {if !{config name='IgnoreAGB'}}
        <input type="hidden" class="agb-checkbox" name="sAGB" value="{if $sAGBChecked}1{else}0{/if}" />
    {/if}
    <div class="basket_details">
        {* Article name *}
        {if $sBasketItem.modus ==0}
            <a class="title{if {config name=detailmodal}} detail-modal{/if}" href="{$detailLink}" target="_blank" title="{$sBasketItem.articlename|strip_tags}">
                {$sBasketItem.articlename|strip_tags}
            </a>
            <p class="ordernumber">
                {se name="CartItemInfoId" namespace="frontend/checkout/cart_item"}{/se} {$sBasketItem.ordernumber}
            </p>
        {else}
            {$sBasketItem.articlename}
        {/if}

        {block name='frontend_checkout_cart_item_details_inline'}
            <p>
                {s name='CheckoutItemPrice'}{/s} {$sBasketItem.price|currency} {s name="Star" namespace="frontend/listing/box_article"}{/s}
            </p>
        {/block}
    </div>
    <div class="clear">&nbsp;</div>

    {* Main article features *}
    <div class="main-article-features">
        <p>
            {include file="string:{config name=mainfeatures}"}
        </p>
    </div>
{/block}

{block name='frontend_checkout_cart_item_price'}{/block}

{block name='frontend_checkout_cart_item_quantity'}
{if $sLaststock.articles[$sBasketItem.ordernumber].OutOfStock == true}
<div class="grid_1">
	-
</div>
{else}
	{$smarty.block.parent}
{/if}
{/block}

{block name='frontend_checkout_cart_item_delivery_informations'}
{if $sLaststock.articles[$sBasketItem.ordernumber].OutOfStock == true}
	<div class="grid_3">
		<div class="status4">&nbsp;</div>
		<p class="deliverable2">{s name="CheckoutItemLaststock"}Nicht lieferbar!{/s}</p>
	</div>
{else}
	{$smarty.block.parent}
{/if}
{/block}


{* Tax price *}
{block name='frontend_checkout_cart_item_tax_price'}
<div class="grid_2">
	<div class="textright">
		{if $sUserData.additional.charge_vat}{$sBasketItem.tax|currency}{else}&nbsp;{/if}
	</div>
</div>
{/block}

{* Bundle price *}
{block name='frontend_checkout_cart_item_bundle_price'}
<div class="grid_3 push_3">
	<div class="textright">
		<strong>
			{$sBasketItem.amount|currency}*
		</strong>
	</div>
	<div class="clear">&nbsp;</div>
</div>
{/block}

{* Bundle tax price *}
{block name='frontend_checkout_cart_item_bundle_tax_price'}
<div class="grid_2 push_4">
	<div class="textright">
		{if $sUserData.additional.charge_vat}{$sBasketItem.tax|currency}{else}&nbsp;{/if}
	</div>
</div>
{/block}

{* Voucher price *}
{block name="frontend_checkout_cart_item_voucher_price"}
<div class="grid_3 push_3">
	<div class="textright">
		<strong>
			{if $sBasketItem.itemInfo}
				{$sBasketItem.itemInfo}
			{else}
				{$sBasketItem.price|currency}*
			{/if}
		</strong>
	</div>
	<div class="clear">&nbsp;</div>
</div>
{/block}

{* Voucher tax price *}
{block name='frontend_checkout_cart_item_voucher_tax_price'}
<div class="grid_2 push_4">
	<div class="textright">
		{if $sUserData.additional.charge_vat}{$sBasketItem.tax|currency}{else}&nbsp;{/if}
	</div>
</div>
{/block}

{* Premium price *}
{block name="frontend_checkout_cart_item_premium_price"}
<div class="grid_3 push_3">
	<div class="textright">
		<strong>
			{s name="CartItemInfoFree"}{/s}
		</strong>
	</div>
	<div class="clear">&nbsp;</div>
</div>
{/block}

{* Premium tax price *}
{block name='frontend_checkout_cart_item_premium_tax_price'}
<div class="grid_2 push_4">
	<div class="textright">
		{if $sUserData.additional.charge_vat}{$sBasketItem.tax|currency}{else}&nbsp;{/if}
	</div>
</div>
{/block}

{* Small quantitiy price *}
{block name='frontend_checkout_Cart_item_small_quantities_price'}
<div class="grid_3 push_3">
	<div class="textright">
		<strong>
			{if $sBasketItem.itemInfo}
				{$sBasketItem.itemInfo}
			{else}
				{$sBasketItem.price|currency}*
			{/if}
		</strong>
	</div>
	<div class="clear">&nbsp;</div>
</div>
{/block}

{* Small quanitity tax price *}
{block name='frontend_checkout_cart_item_small_quantites_tax_price'}
<div class="grid_2 push_4">
	<div class="textright">
		{if $sUserData.additional.charge_vat}{$sBasketItem.tax|currency}{else}&nbsp;{/if}
	</div>
</div>
{/block}

{* Rebate price *}
{block name='frontend_checkout_cart_item_rebate_price'}
<div class="grid_3 push_3">
	<div class="textright">
		<strong>
			{if $sBasketItem.itemInfo}
				{$sBasketItem.itemInfo}
			{else}
				{$sBasketItem.price|currency}*
			{/if}
		</strong>
	</div>
	<div class="clear">&nbsp;</div>
</div>
{/block}

{* Rebate tax price *}
{block name='frontend_checkout_cart_item_rebate_tax_price'}
<div class="grid_2 push_4">
	<div class="textright">
		{if $sUserData.additional.charge_vat}{$sBasketItem.tax|currency}{else}&nbsp;{/if}
	</div>
</div>
{/block}

{* Hide tax symbol *}
{block name='frontend_checkout_cart_tax_symbol'}{/block}
