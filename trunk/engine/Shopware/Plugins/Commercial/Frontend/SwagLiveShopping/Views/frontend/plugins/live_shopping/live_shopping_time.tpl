{* liveshopping *}
<div class="liveshopping detail liveshopping_time">
    {include file="frontend/plugins/live_shopping/data.tpl" liveShopping=$liveShopping}

    {* headline *}
    <span class="headline">{s name="sLiveHeadline"}Angebot endet in:{/s}</span>

    <div class="liveshopping_inner">

        {* counter *}
        <div class="column col1 counter">
            <div class="clear"></div>

        {* counter top *}
            <div class="counter_top">
                <span class="days">{$liveShopping.remaining.days}</span>
                <span class="colon">:</span>
                <span class="hours">{$liveShopping.remaining.hours}</span>
                <span class="colon">:</span>
                <span class="minutes">{$liveShopping.remaining.minutes}</span>
                <span class="colon">:</span>
                <span class="seconds">{$liveShopping.remaining.seconds}</span>
            </div>

            {* counter bottom *}
            <div class="counter_bottom">
                <span class="days">{s name="sLiveDays"}Tage{/s}</span>
                <span class="hours">{s name="sLiveHours"}Std.{/s}</span>
                <span class="minutes">{s name="sLiveMinutes"}Min.{/s}</span>
                <span class="seconds">{s name="sLiveSeconds"}Sek.{/s}</span>
                <div class="clear"></div>
            </div>
        </div>

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

                {* pseudo price *}
                <div class="liveshopping_price pseudo">
                    <small>{s name="reducedPrice"}Statt: {/s}<em>{$liveShopping.startPrice|currency} {s namespace="frontend/listing/box_article" name="Star"}*{/s}</em></small>
                    <strong class="current_price">{$liveShopping.currentPrice|currency} {s namespace="frontend/listing/box_article" name="Star"}*{/s}</strong>
                </div>

                {* time elapse *}
                <div class="elapse">
                    <span class="elapse_inner">&nbsp;</span>
                </div>
            </div>
            <div class="box_right">
                {if $liveShopping.type === 2}
                    {s name="sLivePriceFalls"}Rabatt / Minute: {/s}
                {else}
                    {s name="sLivePriceRises"}Aufpreis / Minute: {/s}
                {/if}
                <strong>{$liveShopping.perMinute|currency} {s namespace="frontend/listing/box_article" name="Star"}*{/s}</strong>
            </div>
        </div>

        {if $liveShopping.limited === 1}
            <div class="column col3 stock">
                {if $inListing==false}<div class="stock_wrapper">{/if}
                <div class="stock_tip line" style="left: 140px;">
                    <div class="stock_tip_arrow"></div>
                    <div class="stock_tip_inner">
                        {se name="sLiveStill"}Noch{/se} <strong>{$liveShopping.quantity}</strong> {se name="sLivePiece"}Stück{/se}
                    </div>
                </div>
                {if $inListing==false}</div>{/if}
            </div>
        {/if}
    </div>
</div>
