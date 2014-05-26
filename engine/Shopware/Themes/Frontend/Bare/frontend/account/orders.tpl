{extends file='frontend/account/index.tpl'}

{* Breadcrumb *}
{block name='frontend_index_start' append}
    {$sBreadcrumb[] = ['name'=>"{s name='MyOrdersTitle'}{/s}", 'link'=>{url}]}
{/block}

{* Main content *}
{block name='frontend_index_content'}
	<div class="content block account--content">

        {if !$sOpenOrders}
            {block name="frontend_account_orders_info_empty"}
				<div class="account--no-orders-info">
					{include file="frontend/_includes/messages.tpl" type="warning" content="{s name='OrdersInfoEmpty'}{/s}"}
				</div>
            {/block}
        {else}

			{block name="frontend_account_orders_welcome"}
				<div class="account--welcome panel">
					<h1 class="panel--title">{s name="OrdersHeader"}{/s}</h1>
					<div class="panel--body is--wide">
						<p>{s name="OrdersWelcomeText"}{/s}</p>
					</div>
				</div>
			{/block}

			{* Orders overview *}
			{block name="frontend_account_orders_overview"}
				<div class="account--orders-overview panel">

					{block name="frontend_account_orders_table"}
						<div class="panel--table">
							{block name="frontend_account_orders_table_head"}
								<div class="orders--table-header panel--tr">
									<div class="panel--th column--date">{s name="OrderColumnDate"}{/s}</div>
									<div class="panel--th column--id">{s name="OrderColumnId"}{/s}</div>
									<div class="panel--th column--dispatch">{s name="OrderColumnDispatch"}{/s}</div>
									<div class="panel--th column--status">{s name="OrderColumnStatus"}{/s}</div>
									<div class="panel--th column--actions is--align-center">{s name="OrderColumnActions"}{/s}</div>
								</div>
							{/block}

							{block name="frontend_account_order_item_overview"}
								{foreach name=orderitems from=$sOpenOrders item=offerPosition}
									{include file="frontend/account/order_item.tpl"}
								{/foreach}
							{/block}
						</div>
					{/block}

					{block name='frontend_account_orders_actions_paging'}
						<div class="account--paging panel--paging">
							{if $sPages.previous}
								<a href="{$sPages.previous}">
									{s name="ListingTextPrevious"}&lt;{/s}
								</a>
							{/if}

							{foreach from=$sPages.numbers item=page}
								{if $page.markup}
									<a>{$page.value}</a>
								{else}
									<a href="{$page.link}">{$page.value}</a>
								{/if}
							{/foreach}

							{if $sPages.next}
								<a href="{$sPages.next}">{s name="ListingTextNext"}&gt;{/s}</a>
							{/if}

							{block name='frontend_account_orders_actions_paging_count'}
								<div class="pagination--display">
									{s name="ListingTextSite"}Seite{/s}
									<strong>{if $sPage}{$sPage}{else}1{/if}</strong>
									{s name="ListingTextFrom"}von{/s}
									<strong>{$sNumberPages}</strong>
								</div>
							{/block}
						</div>
					{/block}

				</div>
            {/block}
        {/if}
    </div>
{/block}