{* liveshopping *}
<div class="liveshopping detail liveshopping_offer">
    {include file="frontend/plugins/live_shopping/data.tpl" liveShopping=$liveShopping}

    {* headline *}
    <span class="headline">{se name="sLiveHeadline"}Angebot endet in:{/se}</span>

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
                <span class="days">{se name="sLiveDays"}Tage{/se}</span>
                <span class="hours">{se name="sLiveHours"}Std.{/se}</span>
                <span class="minutes">{se name="sLiveMinutes"}Min.{/se}</span>
                <span class="seconds">{se name="sLiveSeconds"}Sek.{/se}</span>
                <div class="clear"></div>
            </div>
        </div>

        <div class="column col2 box">
            <div class="box_left">
            {* icon *}
                <span class="liveshopping_icon">
                    <img src="{link file='frontend/_resources/images/icon_clock.png'}" alt="icon_clock" width="44" height="44" />
				</span>

            {* pseudo price *}
                <div class="liveshopping_price pseudo">
                    <small>{se name="reducedPrice"}Statt: {/se}<em class="start_price" style="text-decoration: line-through;">{$liveShopping.startPrice|currency} {s name="Star"}*{/s}</em></small>
                    <strong class="current_price">{$liveShopping.currentPrice|currency} {s name="Star"}*{/s}</strong>
                </div>
            </div>
            <div class="box_right">
                {* procent *}
                <small>{se name="sLiveSave"}Sie sparen{/se}</small>
                <strong>{$liveShopping.percentage|number_format:2:',': '.'}%</strong>
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

