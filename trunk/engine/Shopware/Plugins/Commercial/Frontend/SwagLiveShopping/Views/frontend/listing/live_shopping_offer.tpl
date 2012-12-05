<div class="liveshopping listing">
    {include file="frontend/plugins/live_shopping/data.tpl" liveShopping=$liveShopping}

    <div class="column col1 liveshopping_counter">
        <div class="clear"></div>
        <div class="counter_top">
            <span class="days">{$liveShopping.remaining.days}</span><small>{se name="sLiveDays"}Tage{/se}</small>
            <span class="hours">{$liveShopping.remaining.hours}</span><small>{se name="sLiveHours"}Std.{/se}</small>
            <span class="minutes">{$liveShopping.remaining.minutes}</span><small>{se name="sLiveMinutes"}Min.{/se}</small>
            <span class="seconds">{$liveShopping.remaining.seconds}</span><small>{se name="sLiveSeconds"}Sek.{/se}</small>
        </div>
    </div>

    <div class="listing_liveshopping 4col liveshopping_offer">
        <div class="liveshopping_inner">
            <div class="column col2 box">
                <div class="box_left">
                    <span class="liveshopping_icon">
                        <img src="{link file='frontend/_resources/images/icon_clock.png'}" alt="icon_clock" width="44" height="44" />
                    </span>
                    <div class="liveshopping_price pseudo">
                        <small>{se name="reducedPrice"}Statt: {/se}<em style="text-decoration: line-through;">{$liveShopping.startPrice|currency} {s namespace="frontend/listing/box_article" name="Star"}*{/s}</em></small><br />
                        <strong class="current_price">{$liveShopping.currentPrice|currency} {s namespace="frontend/listing/box_article" name="Star"}*{/s}</strong>
                    </div>
                </div>
                <div class="box_right">
                    <small>{se name="sLiveSave"}Sie sparen{/se} {$liveShopping.percentage|number_format:2:',': '.'}%</small>
                </div>
            </div>
            {if $liveShopping.limited === 1}
                <div class="column col1 stock">
                    <div class="stock_tip">
                        <div class="stock_tip_inner">
                            {se name="sLiveStill"}Noch{/se}<br /> <strong>{$liveShopping.quantity}</strong><br />{se name="sLivePiece"}St&uuml;ck{/se}
                        </div>
                    </div>
                </div>
            {/if}
        </div>
    </div>
</div>