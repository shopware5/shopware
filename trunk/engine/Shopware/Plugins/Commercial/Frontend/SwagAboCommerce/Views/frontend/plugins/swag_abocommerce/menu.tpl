{* Replace the notepad with the abo order list *}
{block name="frontend_index_checkout_actions_notepad"}
    {* if $aboCommerceOrderListsActive *}
        <a href="{url controller="Abocommerce" action="listAction"}" title="Bestellisten" class="item-order-list">
            Bestelllisten
        </a>
    {* /if *}
{/block}
