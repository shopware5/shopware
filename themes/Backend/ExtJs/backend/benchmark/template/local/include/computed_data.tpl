<div class="computed-data">
    <div class="time-container weeks">
        <div class="value-container current-day">
            <div class="data-value">
                [[  local.weeks.meta.today ]]
            </div>
            <div class="data-label">
                Tag
            </div>
        </div>

        <div class="value-container total">
            <div class="data-value local">
                [[  local.weeks.totalValues.{$key} ]]
            </div>
            <div class="data-label">
                Gesamt
            </div>
        </div>

        <div class="value-container current-weeks">
            <div class="data-value">
                [[  local.weeks.meta.shownTime ]]
            </div>
            <div class="data-label">
                Woche(n)
            </div>
        </div>
    </div>

    <div class="time-container months" style="display: none;">
        <div class="value-container current-week">
            <div class="data-value">
                [[  local.months.meta.today ]]
            </div>
            <div class="data-label">
                Woche
            </div>
        </div>

        <div class="value-container total">
            <div class="data-value local">
                [[  local.months.totalValues.{$key} ]]
            </div>
            <div class="data-label">
                Gesamt
            </div>
        </div>

        <div class="value-container current-months">
            <div class="data-value">
                [[  local.months.meta.shownTime ]]
            </div>
            <div class="data-label">
                Monat(e)
            </div>
        </div>
    </div>

    <div class="time-container years" style="display: none;">
        <div class="value-container current-month">
            <div class="data-value">
                [[  local.years.meta.today ]]
            </div>
            <div class="data-label">
                Monat
            </div>
        </div>

        <div class="value-container total">
            <div class="data-value local">
                [[  local.years.totalValues.{$key} ]]
            </div>
            <div class="data-label">
                Gesamt
            </div>
        </div>

        <div class="value-container current-months">
            <div class="data-value">
                [[  local.years.meta.shownTime ]]
            </div>
            <div class="data-label">
                Jahr(e)
            </div>
        </div>
    </div>
</div>