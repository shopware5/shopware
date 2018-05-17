<div class="col-50 targetgroup--shop">
    <h4>[[ $t('shopTitle') ]]</h4>
    <div class="like-shop-ice"></div>
    <div class="data">
        <span class="data--headline high-contrast">[[ $t('womenTitle') ]]</span><br>
        <span class="data--headline shop-color">[[ local.customers.women.percentage ]]%</span><br>
        <span class="data--txt">[[ local.customers.women.amount ]]</span><br>
        <span class="data--txt">Ø [[ local.customers.women.averageAge ]] [[ $t('yearsOldTitle') ]]</span>
        <br><br>
        <span class="data--headline high-contrast">[[ $t('menTitle') ]]</span><br>
        <span class="data--headline shop-color">[[ local.customers.men.percentage ]]%</span><br>
        <span class="data--txt">[[ local.customers.men.amount ]]</span><br>
        <span class="data--txt">Ø [[ local.customers.men.averageAge ]] [[ $t('yearsOldTitle') ]]</span><br>
    </div>

    <div class="data-wrapper">
        <span class="data--headline high-contrast">[[ $t('ageTitle') ]]</span><br>

        <div class="row">
            <div class="col-33 data--txt shop-color">[[ $t('above50Text') ]]</div>
            <div class="col-66">
                <target-group-bar name="above50"></target-group-bar>
            </div>
        </div>
        <div class="row">
            <div class="col-33 data--txt shop-color">[[ $t('between30And50Text') ]]</div>
            <div class="col-66">
                <target-group-bar name="between30And50"></target-group-bar>
            </div>
        </div>
        <div class="row">
            <div class="col-33 data--txt shop-color">[[ $t('between15And30Text') ]]</div>
            <div class="col-66">
                <target-group-bar name="between15And30"></target-group-bar>
            </div>
        </div>
    </div>
</div>