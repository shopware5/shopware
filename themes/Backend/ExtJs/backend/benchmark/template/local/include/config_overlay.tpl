<div class="shop-config--overlay">
    <div class="shop-list--config">
        <p class="config-headline"></p>
        <div class="custom-select industry-select" data-custom-drop-down="true">
            <input class="overlay-shop-id" type="hidden" name="shopId" />
            <input class="industry-value" type="hidden" name="industry-value" />
            <div class="dropdown-wrapper">
                <div class="select-header">
                    [[ $t('defaultIndustryText') ]]
                </div>
                <div class="select-options">
                    <div class="select-option industry-option" data-translation-name="animalsPetSupplies" data-industry-value="1">[[ $t('animalsPetSupplies') ]]</div>
                    <div class="select-option industry-option" data-translation-name="apparelAccessories" data-industry-value="2">[[ $t('apparelAccessories') ]]</div>
                    <div class="select-option industry-option" data-translation-name="artsEntertainment" data-industry-value="3">[[ $t('artsEntertainment') ]]</div>
                    <div class="select-option industry-option" data-translation-name="babyToddler" data-industry-value="4">[[ $t('babyToddler') ]]</div>
                    <div class="select-option industry-option" data-translation-name="businessIndustrial" data-industry-value="5">[[ $t('businessIndustrial') ]]</div>
                    <div class="select-option industry-option" data-translation-name="camerasOptics" data-industry-value="6">[[ $t('camerasOptics') ]]</div>
                    <div class="select-option industry-option" data-translation-name="electronics" data-industry-value="7">[[ $t('electronics') ]]</div>
                    <div class="select-option industry-option" data-translation-name="foodBeveragesTobacco" data-industry-value="8">[[ $t('foodBeveragesTobacco') ]]</div>
                    <div class="select-option industry-option" data-translation-name="furniture" data-industry-value="9">[[ $t('furniture') ]]</div>
                    <div class="select-option industry-option" data-translation-name="hardware" data-industry-value="10">[[ $t('hardware') ]]</div>
                    <div class="select-option industry-option" data-translation-name="healthBeauty" data-industry-value="11">[[ $t('healthBeauty') ]]</div>
                    <div class="select-option industry-option" data-translation-name="homeGarden" data-industry-value="12">[[ $t('homeGarden') ]]</div>
                    <div class="select-option industry-option" data-translation-name="luggageBags" data-industry-value="13">[[ $t('luggageBags') ]]</div>
                    <div class="select-option industry-option" data-translation-name="mature" data-industry-value="14">[[ $t('mature') ]]</div>
                    <div class="select-option industry-option" data-translation-name="media" data-industry-value="15">[[ $t('media') ]]</div>
                    <div class="select-option industry-option" data-translation-name="officeSupplies" data-industry-value="16">[[ $t('officeSupplies') ]]</div>
                    <div class="select-option industry-option" data-translation-name="religiousCeremonial" data-industry-value="17">[[ $t('religiousCeremonial') ]]</div>
                    <div class="select-option industry-option" data-translation-name="software" data-industry-value="18">[[ $t('software') ]]</div>
                    <div class="select-option industry-option" data-translation-name="sportingGoods" data-industry-value="19">[[ $t('sportingGoods') ]]</div>
                    <div class="select-option industry-option" data-translation-name="toysGames" data-industry-value="20">[[ $t('toysGames') ]]</div>
                    <div class="select-option industry-option" data-translation-name="vehiclesParts" data-industry-value="21">[[ $t('vehiclesParts') ]]</div>
                </div>
            </div>
            <div class="fade-out"></div>
        </div>

        <div class="type--header">
            [[ $t('shopTypeTitle') ]]
        </div>

        <div class="shop-config--type">
            <label class="type-label">
                <input class="shop-type--b2c" name="type" type="radio" value="b2c"/>
                <span class="check-mark"></span>
                <span class="type-label--name">[[ $t('b2cText') ]]</span>
                <span class="type-label--short">([[ $t('b2cShortText') ]])</span>
            </label>
            <label class="type-label">
                <input class="shop-type--b2b" name="type" type="radio" value="b2b"/>
                <span class="check-mark"></span>
                <span class="type-label--name">[[ $t('b2bText') ]]</span>
                <span class="type-label--short">([[ $t('b2bShortText') ]])</span>
            </label>
        </div>

        <div class="submit-button--ct">
            <input class="shop-config--submit-button btn primary" type="submit" :value="[[ $t('shopConfigSaveText') ]]"/>
        </div>

        <div class="action-buttons--ct">
            <div class="reset-button--ct">
                <div class="reset-button--icon"></div>
                <input class="action-buttons--reset-button action-button" type="button" :value="[[ $t('shopConfigResetText') ]]"/>
            </div>
            <div class="cancel-button--ct">
                <div class="cancel-button--icon"></div>
                <input class="action-buttons--cancel-button action-button" type="button" :value="[[ $t('shopConfigCancelText') ]]"/>
            </div>
        </div>
    </div>
</div>