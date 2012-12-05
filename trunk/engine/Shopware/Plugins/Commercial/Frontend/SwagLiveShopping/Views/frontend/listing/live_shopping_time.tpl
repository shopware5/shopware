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

    <div class="listing_liveshopping 4col liveshopping_time">
        <div class="liveshopping_inner">
            <div class="column col2 box">
                <div class="box_left">
                    {if $liveShopping.type===2}
                        {* icon *}
                        <span class="liveshopping_icon arrow down">
                            <img src="{link file='frontend/_resources/images/icon_down.png'}" alt="icon_down" width="44" height="44" />
                        </span>
                        {else}
                        {* icon *}
                        <span class="liveshopping_icon arrow up">
                            <img src="{link file='frontend/_resources/images/icon_up.png'}" alt="icon_up" width="44" height="44" />
                        </span>
                    {/if}
                    <div class="liveshopping_price pseudo">
                        <small>{se name="reducedPrice"}Statt: {/se}<em style="text-decoration: line-through;">{$liveShopping.startPrice|currency} {s namespace="frontend/listing/box_article" name="Star"}*{/s}</em></small><br />
                        <strong class="current_price">{$liveShopping.currentPrice|currency} {s namespace="frontend/listing/box_article" name="Star"}*{/s}</strong>
                    </div>
                </div>
                <div class="box_right">
                    {if $liveShopping.type === 2}
                        {s name="sLivePriceFalls" force}Rabatt / Minute: {/s} {$liveShopping.perMinute|currency} {s namespace="frontend/listing/box_article" name="Star"}*{/s}
                    {else}
                        {s name="sLivePriceRises" force}Aufpreis / Minute: {/s}{$liveShopping.perMinute|currency} {s namespace="frontend/listing/box_article" name="Star"}*{/s}
                    {/if}
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