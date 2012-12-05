{* disabled actions *}
{block name='frontend_listing_box_article_actions'}
    {* liveshopping *}
    {if $sArticle.liveShopping}
        {* counter *}
        <div class="column col1 liveshopping_counter">
            <div class="clear"></div>

            {* counter top *}
            <div class="counter_top">
                <span class="days">{$liveShopping.remaining.days}<small>{se name="sLiveDays"}Tage{/se}</small></span>
                <span class="hours">{$liveShopping.remaining.hours}<small>{se name="sLiveHours"}Std.{/se}</small></span>
                <span class="minutes">{$liveShopping.remaining.minutes}<small>{se name="sLiveMinutes"}Min.{/se}</small></span>
                <span class="seconds">{$liveShopping.remaining.seconds}<small>{se name="sLiveSeconds"}Sek.{/se}</small></span>
            </div>

        </div>

        <div class="listing_liveshopping 4col liveshopping_time">

            <div class="liveshopping_inner">

                {* box *}
                <div class="column col2 box">

                    <div class="box_left">

                        {* icon *}
                        <span class="liveshopping_icon">
                            <img src="{link file='frontend/_resources/images/icon_clock.png'}" alt="icon_clock" width="44" height="44" />
                        </span>

                        {* pseudo price *}
                        <div class="liveshopping_price pseudo">
                            <small>{se name="reducedPrice"}Statt: {/se}<em style="text-decoration: line-through;">39,95 &euro;{s name="Star"}*{/s}</em></small><br />
                            <strong>134,95 &euro;{s name="Star"}*{/s}</strong>
                        </div>

                    </div>

                    <div class="box_right">

                        {* procent *}
                        <small>{se name="sLiveSave"}Sie sparen{/se} 33,33%</small>
                    </div>

                </div>

                {* COL4
                <div class="column col4 stock">

                    <div class="stock_tip">
                        <div class="stock_tip_arrow"></div>
                        <div class="stock_tip_inner">
                            {se name="sLiveStill"}Noch{/se} <strong>46</strong> {se name="sLivePiece"}Stück{/se}
                        </div>
                    </div>

                </div>
                 *}

                {* COL 1 *}
                <div class="column col1 stock">

                    <div class="stock_tip">
                        <div class="stock_tip_inner">
                            {se name="sLiveStill"}Noch{/se}<br /> <strong>46</strong><br />{se name="sLivePiece"}St&uuml;ck{/se}
                        </div>
                    </div>

                </div>

            </div>
        </div>
    {/if}
{/block}

{* disabled price *}
{block name='frontend_listing_box_article_price'}
{/block}
