{block name="frontend_listing_product_box_to_product_button"}

    {block name="frontend_listing_product_box_to_product_button_url"}
        {$url = {$sArticle.linkDetails|rewrite:$sArticle.articleName} }
    {/block}

    {block name="frontend_listing_product_box_to_product_button_title"}
        {$title = {$sArticle.articleName|escapeHtml} }
    {/block}

    <form method="get"
          style="height: 42px; margin-top: 7px"
          action="{$url}"
          data-eventName="submit">

        {block name="frontend_listing_product_box_buy_button_button"}
            <button class="buybox--button block btn is--icon-right is--center is--large">
                Zum Produkt <i class="icon--arrow-right"></i>
            </button>
        {/block}
    </form>
{/block}