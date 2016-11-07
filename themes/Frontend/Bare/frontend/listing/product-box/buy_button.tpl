{block name="frontend_listing_product_box_buy_button"}

    {block name="frontend_listing_product_box_buy_button_url"}
        {$url = {url controller=checkout action=addArticle} }
    {/block}

    <form name="sAddToBasket"
          method="post"
          action="{$url}"
          class="buybox--form"
          data-add-article="true"
          data-eventName="submit"
          style="height: 42px; margin-top: 7px"
          {if $theme.offcanvasCart}
              data-showModal="false"
              data-addArticleUrl="{url controller=checkout action=ajaxAddArticleCart}"
          {/if}>

        <input type="hidden" name="sAdd" value="{$sArticle.ordernumber}"/>

        {block name="frontend_listing_product_box_buy_button_button"}
            <button class="buybox--button block btn is--primary is--icon-right is--center is--large" name="{s namespace="frontend/detail/buy" name="DetailBuyActionAdd"}{/s}">
                {s namespace="frontend/detail/buy" name="DetailBuyActionAdd"}{/s} <i class="icon--arrow-right"></i>
            </button>
        {/block}
    </form>
{/block}
