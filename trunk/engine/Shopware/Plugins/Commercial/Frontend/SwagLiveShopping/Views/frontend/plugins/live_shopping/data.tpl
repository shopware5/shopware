<div class="live_shopping_data_container">
    <input class="valid_to" type="hidden" value="{$liveShopping.validTo}">
    <input class="live_shopping_id" type="hidden" value="{$liveShopping.id}">
    <input class="live_shopping_data_url" type="hidden" value="{url controller="LiveShopping" action="getLiveShoppingData" liveShoppingId=$liveShopping.id}">
    <input class="live_shopping_type" type="hidden" value="{$liveShopping.type}">
    <input class="star" type="hidden" value=' {s namespace="frontend/listing/box_article" name="Star"}*{/s}'>

    <input class="live_shopping_initial_quantity" type="hidden" value="{$liveShopping.quantity}">
    <input class="live_shopping_initial_sells" type="hidden" value="{$liveShopping.sells}">

    <div class="currency-helper hidden">{0|currency}</div>
</div>
