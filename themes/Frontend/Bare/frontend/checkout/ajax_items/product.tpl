{namespace name="frontend/checkout/ajax_cart"}

{$calculated = $lineItem.product}

{$link = {url controller=detail sArticle=$lineItem.id number=$calculated.identifier}}

{block name='frontend_checkout_ajax_cart_item_product'}
    <div class="cart--item">
        {block name='frontend_checkout_ajax_cart_articleimage'}
            <div class="thumbnail--container{if $lineItem.cover.thumbnails[0]} has--image{/if}">

                {block name='frontend_checkout_ajax_cart_articleimage_product'}
                    {$name = $lineItem.name|strip_tags|escape}
                    {$desc = $name}
                    {if $lineItem.cover.description}
                        {$desc = $lineItem.cover.description|strip_tags|escape}
                    {/if}

                    {if $lineItem.cover}
                        <img srcset="{$lineItem.cover.thumbnails[0].sourceSet}" alt="{$desc}" title="{$desc|truncate:160}" class="thumbnail--image" />
                    {elseif $basketItem.image.src.0}
                        <img src="{link file='frontend/_public/src/img/no-picture.jpg'}" alt="{$desc}" title="{$desc|truncate:160}" class="thumbnail--image" />
                    {/if}
                {/block}
            </div>
        {/block}

        {* Article actions *}
        {block name='frontend_checkout_ajax_cart_actions'}
            <div class="action--container">
                {$deleteUrl = {url action='removeLineItem' identifier=$calculated.identifier sTargetAction=$sTargetAction}}

                <form action="{$deleteUrl}" method="post">
                    <button type="submit" class="btn is--small action--remove" title="{s name="AjaxCartRemoveArticle"}{/s}">
                        <i class="icon--cross"></i>
                    </button>
                </form>
            </div>
        {/block}

        {* Article name *}
        {block name='frontend_checkout_ajax_cart_articlename'}
            <a class="item--link" href="{$link}" title="{$lineItem.name|escapeHtml}">

                {block name="frontend_checkout_ajax_cart_articlename_quantity"}
                    <span class="item--quantity">{$calculated.quantity}x</span>
                {/block}

                {block name="frontend_checkout_ajax_cart_articlename_name"}
                    <span class="item--name">

                        {if $theme.offcanvasCart}
                            {$lineItem.name|escapeHtml}
                        {else}
                            {$lineItem.name|truncate:28:"...":true|escapeHtml}
                        {/if}
                    </span>
                {/block}

                {block name="frontend_checkout_ajax_cart_articlename_price"}
                    <span class="item--price">{if $calculated.price}{$calculated.price.totalPrice|currency}{else}{s name="AjaxCartInfoFree"}{/s}{/if}{s name="Star"}{/s}</span>
                {/block}
            </a>
        {/block}
    </div>
{/block}