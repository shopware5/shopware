{* Notepad entry *}
{block name="frontend_index_checkout_actions_notepad"}
    <li class="navigation--entry entry--notepad" role="menuitem">
        <a href="{url controller='note'}" title="{"{s namespace='frontend/index/checkout_actions' name='IndexLinkNotepad'}{/s}"|escape}" class="btn">
            <i class="icon--heart"></i>
            {if $sNotesQuantity > 0}
                <span class="badge notes--quantity">
                    {$sNotesQuantity}
                </span>
            {/if}
        </a>
    </li>
{/block}

{* My account entry *}
{block name="frontend_index_checkout_actions_my_options"}
    <li class="navigation--entry entry--account" role="menuitem">
        {block name="frontend_index_checkout_actions_account"}
            <a href="{url controller='account'}" title="{"{s namespace='frontend/index/checkout_actions' name='IndexLinkAccount'}{/s}"|escape}" class="btn is--icon-left entry--link account--link">
                <i class="icon--account"></i>
                <span class="account--display">
                    {s namespace='frontend/index/checkout_actions' name='IndexLinkAccount'}{/s}
                </span>
            </a>
        {/block}
    </li>
{/block}

{* Cart entry *}
{block name="frontend_index_checkout_actions_cart"}
    <li class="navigation--entry entry--cart" role="menuitem">
        <a class="btn is--icon-left cart--link" href="{url controller='checkout' action='cart'}" title="{"{s namespace='frontend/index/checkout_actions' name='IndexLinkCart'}{/s}"|escape}">
            <span class="cart--display">
                {if $sUserLoggedIn}
                    {s name='IndexLinkCheckout' namespace='frontend/index/checkout_actions'}{/s}
                {else}
                    {s namespace='frontend/index/checkout_actions' name='IndexLinkCart'}{/s}
                {/if}
            </span>

            <span class="badge is--primary is--minimal cart--quantity{if $sBasketQuantity < 1} is--hidden{/if}">{$sBasketQuantity}</span>

            <i class="icon--basket"></i>

            <span class="cart--amount">
                {$sBasketAmount|currency} {s name="Star" namespace="frontend/listing/box_article"}{/s}
            </span>
        </a>
        <div class="ajax-loader">&nbsp;</div>
    </li>
{/block}

{block name="frontend_index_checkout_actions_inner"}{/block}
