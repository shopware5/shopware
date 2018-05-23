{extends file="backend/benchmark/template/local/index.tpl"}

{block name="benchmark_index_title"}Shopware BI - Branche wählen{/block}

{block name="benchmark_index_body"}
    <body id="swag-select">
        {include file="backend/benchmark/template/local/include/language_switch.tpl"}
        <div class="wrapper">
            <div class="branch-select">
                <div class="swag-container">
                    <div id="select-wrapper">
                        <h1>[[ $t('industrySelectionHeadline') ]]</h1>
                        <form action="{url controller="BenchmarkOverview" action="setIndustry"}">
                            <input type="hidden" name="lang" value="{$benchmarkDefaultLanguage}">
                            <div class="btn primary select-bg">
                                <select name="industry">
                                    <option value="0">[[ $t('noneSelected') ]]</option>
                                    <option value="1">[[ $t('animalsPetSupplies') ]]</option>
                                    <option value="2">[[ $t('apparelAccessories') ]]</option>
                                    <option value="3">[[ $t('artsEntertainment') ]]</option>
                                    <option value="4">[[ $t('babyToddler') ]]</option>
                                    <option value="5">[[ $t('businessIndustrial') ]]</option>
                                    <option value="6">[[ $t('camerasOptics') ]]</option>
                                    <option value="7">[[ $t('eletronics') ]]</option>
                                    <option value="8">[[ $t('foodBeveragesTobacco') ]]</option>
                                    <option value="9">[[ $t('furniture') ]]</option>
                                    <option value="10">[[ $t('hardware') ]]</option>
                                    <option value="11">[[ $t('healthBeauty') ]]</option>
                                    <option value="12">[[ $t('homeGarden') ]]</option>
                                    <option value="13">[[ $t('luggageBags') ]]</option>
                                    <option value="14">[[ $t('mature') ]]</option>
                                    <option value="15">[[ $t('media') ]]</option>
                                    <option value="16">[[ $t('officeSupplies') ]]</option>
                                    <option value="17">[[ $t('religiousCeremonial') ]]</option>
                                    <option value="18">[[ $t('software') ]]</option>
                                    <option value="19">[[ $t('sportingGoods') ]]</option>
                                    <option value="20">[[ $t('toysGames') ]]</option>
                                    <option value="21">[[ $t('vehiclesParts') ]]</option>
                                </select>
                            </div>
                            <p>[[ $t('industrySelectionDescription') ]]</p>

                            <button type="submit" class="btn secondary">[[ $t('industrySelectionSaveButton') ]]</button>
                        </form>
                    </div>
                </div>

                <div class="wild-graph"></div>
                <div class="bubble-one"></div>
                <div class="bubble-two"></div>
            </div>
        </div>
    </body>
{/block}

