{extends file="backend/benchmark/template/local/index.tpl"}

{block name="benchmark_index_title"}Shopware BI - Waiting{/block}

{block name="benchmark_index_body"}
    <body id="swag-waiting">
        <div class="wrapper swag-onbording waiting">
            <div class="row">
                <div class="col-100">
                    <div class="opener">
                        <h1>[[ $t('waitingHeadline{$waitingSinceDays}') ]]</h1>
                    </div>
                    <p>[[ $t('waitingText{$waitingSinceDays}') ]]</p>
                </div>

                <div class="col-100">
                    {if $waitingSinceDays <= 1}
                        <div class="compare-no-apples-and-pears"></div>
                    {elseif $waitingSinceDays <= 2}
                        <div class="eat-fruits-while-waiting"></div>
                    {else}
                        <div class="no-fruits-no-fun"></div>
                    {/if}
                </div>

                <div class="col-100">
                    <button class="btn primary" type="submit">
                        [[ $t('waitingButton{$waitingSinceDays}') ]]
                    </button>
                </div>
            </div>
            <svg viewBox="0 0 100 100" xmlns="http://www.w3.org/2000/svg" class="circle big">
                <circle cx="50" cy="50" r="50">
            </svg>
            <svg viewBox="0 0 100 100" xmlns="http://www.w3.org/2000/svg" class="circle small">
                <circle cx="50" cy="50" r="50">
            </svg>
        </div>
    </body>
{/block}
