{extends file="backend/benchmark/template/local/index.tpl"}

{block name="benchmark_index_title"}Shopware BI - Fragen und Antworten{/block}

{block name="benchmark_index_head"}
    {$smarty.block.parent}
    <script type="text/javascript">
        function enableButton() {
            var checkBox = document.getElementById("confirmation-check"),
                button = document.getElementById("load-industry");

            button.style.display = checkBox.checked ? 'block' : 'none';
        };
    </script>
{/block}

{block name="benchmark_index_body"}
    <body id="swag-info">
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

                <div class="wrapper-info">
                    <div class="info--text">
                        <h1>[[ $t('faqHeadline') ]]</h1>

                        <div class="row">
                            <div class="col-66">
                                <h2>[[ $t('question1Headline') ]]</h2>
                                <p>[[ $t('question1Text') ]]</p>

                                <h2>[[ $t('question2Headline') ]]</h2>
                                <p>[[ $t('question2Text') ]]</p>

                                <h2>[[ $t('question3Headline') ]]</h2>
                                <p>[[ $t('question3Text') ]]</p>
                            </div>

                            <div class="col-33">
                                <h2><span class="check-color">[[ $t('factsHeadline') ]]</span></h2>
                                <ul class="top-benefits">
                                    <li>[[ $t('fact1') ]]</li>
                                    <li>[[ $t('fact2') ]]</li>
                                    <li>[[ $t('fact3') ]]</li>
                                    <li>[[ $t('fact4') ]]</li>
                                    <li>[[ $t('fact5') ]]</li>
                                    <li>[[ $t('fact6') ]]</li>
                                    <li>[[ $t('fact7') ]]</li>
                                </ul>
                            </div>
                        </div>

                        <div class="accept-abg">
                            <input type="checkbox" id="confirmation-check" onclick="enableButton()"/>
                            <label for="confirmation-check" class="confirmation-label">[[ $t('participationCheck') ]]</label>
                            <div id="load-industry" style="display: none;">
                                <form method="get" action="{url controller="BenchmarkLocalOverview" action="render" template="industry_select"}">
                                    <input type="hidden" value="{$benchmarkDefaultLanguage}" name="lang"/>
                                    <button class="btn primary" type="submit">
                                        [[ $t('nextButton') ]]
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </body>
{/block}
