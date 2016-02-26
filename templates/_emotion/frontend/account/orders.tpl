{extends file='frontend/account/index.tpl'}

{* Breadcrumb *}
{block name='frontend_index_start' append}
	{$sBreadcrumb[] = ['name'=>"{s name='MyOrdersTitle'}{/s}", 'link'=>{url}]}
{/block}

{* Main content *}
{block name='frontend_index_content'}

{* Orders overview *}
<div class="grid_16 orders" id="center">
	{if !$sOpenOrders}
	{block name="frontend_account_orders_info_empty"}
		<h1>{se name='OrdersHeadline'}Meine Bestellungen{/se}</h1>

		<fieldset>
			<div class="notice center bold">
				{se name="OrdersInfoEmpty"}{/se}
			</div>
		</fieldset>
	{/block}
	{else}
	<h1>{se name="OrdersHeader"}{/se}</h1>
	<div class="orderoverview_active">
	
		<div class="table grid_16">
			{block name="frontend_account_orders_table_head"}
			<div class="table_head">
				
				<div class="grid_3">
					{se name="OrderColumnDate"}{/se}
				</div>
				
				<div class="grid_2">
					{se name="OrderColumnId"}{/se}
				</div>
				<div class="grid_3">
					{se name="OrderColumnDispatch"}{/se}
				</div>
				
				<div class="grid_5">
					{se name="OrderColumnStatus"}{/se}
				</div>
				
				<div class="grid_2 textright">
					<div class="textright">
						{se name="OrderColumnActions"}{/se}
					</div>
				</div>
			</div>
			{/block}
			{foreach name=orderitems from=$sOpenOrders item=offerPosition}
				{if $smarty.foreach.orderitems.last}
					{assign var=lastitem value=1}
				{else}
					{assign var=lastitem value=0}
				{/if}
				{include file="frontend/account/order_item.tpl" lastitem=$lastitem}
			{/foreach}

            <div class="space">&nbsp;</div>

            {block name='frontend_account_orders_actions_paging'}
                {if $sPages.numbers|@count > 1}
                    <div class="listing_actions normal">
                        <div class="bottom">
                            <div class="paging">
                                <label>{se name='ListingPaging'}Blättern:{/se}</label>

                                {if $sPages.previous}
                                    <a href="{$sPages.previous}" class="navi prev">
                                        {s name="ListingTextPrevious"}&lt;{/s}
                                    </a>
                                {/if}

                                {foreach from=$sPages.numbers item=page}
                                    {if $page.markup}
                                        <a title="" class="navi on">{$page.value}</a>
                                    {else}
                                        <a href="{$page.link}" title="" class="navi">
                                            {$page.value}
                                        </a>
                                    {/if}
                                {/foreach}

                                {if $sPages.next}
                                    <a href="{$sPages.next}" class="navi more">{s name="ListingTextNext"}&gt;{/s}</a>
                                {/if}
                            </div>
                            <div class="display_sites">
                                {se name="ListingTextSite"}Seite{/se} <strong>{if $sPage}{$sPage}{else}1{/if}</strong> {se name="ListingTextFrom"}von{/se} <strong>{$sNumberPages}</strong>
                            </div>
                        </div>
                    </div>
                {/if}
            {/block}
		</div>
	</div>
	<div class="space">&nbsp;</div>
	{/if}
</div>
{/block}