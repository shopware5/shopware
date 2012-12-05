{extends file='parent:frontend/account/orders.tpl'}

{block name="frontend_account_orders_info_empty"}
    <h1>{se name='OrdersHeadline'}Meine Bestellungen{/se}</h1>

    <fieldset>
        <div class="notice center bold">
            {se name="OrdersInfoEmpty"}{/se}
        </div>
    </fieldset>
{/block}