{extends file="backend/benchmark/template/local/index.tpl"}

{block name="benchmark_index_title"}Shopware BI - Start{/block}

{block name="benchmark_index_body"}
    <body id="swag-start">
        {include file="backend/benchmark/template/local/include/loading_indicator.tpl"}
        {include file="backend/benchmark/template/local/include/language_switch.tpl"}
        <div class="wrapper">
            <div class="swag-onbording">

                <div class="tob-bar">
                    <ul class="top-benefits">
                        <li>[[ $t('freeText') ]]</li>
                        <li>[[ $t('anonymousText') ]]</li>
                        <li>[[ $t('nonBindingText') ]]</li>
                    </ul>
                </div>

                <div class="preview-screen"></div>

                <div class="teaser-text">
                    <div class="funny-graph"></div>
                    <div class="row">
                        <div class="col-100">
                            <h1>[[ $t('startHeadline') ]]</h1>
                            <p>[[ $t('startText') ]]</p>
                        </div>

                        <div class="col-100">
                            <form method="get" action="{url controller="BenchmarkLocalOverview" action="render" template="info"}">
                                <input type="hidden" value="{$benchmarkDefaultLanguage}" name="lang"/>
                                <button class="btn primary" type="submit">
                                    [[ $t('startButtonText') ]]
                                </button>
                            </form>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </body>
{/block}
