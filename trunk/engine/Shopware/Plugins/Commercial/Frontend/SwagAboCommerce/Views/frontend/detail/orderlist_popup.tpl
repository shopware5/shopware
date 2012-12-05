<div class="orderlist-popup">
    <div class="arrow"></div>
    <div class="orderlist-inner-popup">
        <h3 class="orderlist-popup-headline">Bestellliste auswählen</h3>

        <form class="add-to-orderlist-form" method="post" action="{url controller=AboCommerce action=saveToOrderlist}">
            <p>
                <select name="orderlist" class="orderlist-select">
                    {foreach $aboCommerceOrderLists as $list}
                        <option value="{$list.id}">{$list.name}</option>
                    {/foreach}
                </select>
            </p>
            <p>
                <a href="#add-to-orderlist" class="btn-add-to-orderlist">
                    Artikel hinzufügen
                </a>
            </p>

            {* Only show the link to the orderlist management if the user is logged in *}
            {if $isUserLoggedIn}
                <div class="orderlist-popup-footer">
                   <a href="#manage-orderlists">
                       Bestellisten verwalten
                   </a>
                </div>
            {/if}
        </form>
    </div>
</div>