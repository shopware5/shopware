<div class="computed-data">
    <div class="time-container weeks">
        <div class="value-container current-weeks">
            <div class="data-value">
                [[  local.weeks.meta.shownTime ]]
            </div>
            <div class="data-label">
                [[ $t('periodTitle') ]]
            </div>
        </div>

        <div class="value-container total">
            <div class="data-value local">
                [[  local.weeks.totalValues.{$key} ]]
            </div>
            <div class="data-label">
                [[ $t('totalTitle') ]]
            </div>
        </div>
    </div>

    <div class="time-container months" style="display: none;">
        <div class="value-container current-weeks">
            <div class="data-value">
                [[  local.weeks.meta.shownTime ]]
            </div>
            <div class="data-label">
                [[ $t('periodTitle') ]]
            </div>
        </div>

        <div class="value-container total">
            <div class="data-value local">
                [[  local.months.totalValues.{$key} ]]
            </div>
            <div class="data-label">
                [[ $t('totalTitle') ]]
            </div>
        </div>
    </div>

    <div class="time-container years" style="display: none;">
        <div class="value-container current-weeks">
            <div class="data-value">
                [[  local.weeks.meta.shownTime ]]
            </div>
            <div class="data-label">
                [[ $t('periodTitle') ]]
            </div>
        </div>

        <div class="value-container total">
            <div class="data-value local">
                [[  local.years.totalValues.{$key} ]]
            </div>
            <div class="data-label">
                [[ $t('totalTitle') ]]
            </div>
        </div>
    </div>
</div>