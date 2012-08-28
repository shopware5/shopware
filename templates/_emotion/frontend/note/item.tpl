{extends file='parent:frontend/note/item.tpl'}

{* Unit price *}
{block name="frontend_note_item_unitprice"}
{if $sBasketItem.purchaseunit}
    	<div class="article_price_unit">
        <p>
            <strong>{se name="NoteUnitPriceContent"}{/se}:</strong> {$sBasketItem.purchaseunit} {$sBasketItem.sUnit.description}
        </p>
        {if $sBasketItem.purchaseunit != $sBasketItem}
            <p>
                {if $sBasketItem.referenceunit}
                    {$sBasketItem.referenceprice|currency} {s name="Star" namespace="frontend/listing/box_article"}{/s} / {$sBasketItem.referenceunit} {$sBasketItem.sUnit.description}
                {/if}
            </p>
        {/if}
    </div>
{/if}
{/block}