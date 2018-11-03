<form>
    <div data-time-switcher="true" data-values-key="{$dataKey}">
        <label><input type="button" class="btn timescale is-active timescale-week" name="weeks" :value="$t('weeksText')" /></label>
        <label><input type="button" class="btn timescale timescale-month" name="months" :value="$t('monthsText')" /></label>
        <label><input type="button" class="btn timescale timescale-year" name="years" :value="$t('yearsText')" /></label>
    </div>

    <div class="graph-container">
        <div class="graph-wrapper">
            <canvas height="260"
                data-benchmark-graph="true"
                data-name="{$dataKey}"
                data-include-industry="false"
                data-time="weeks"
                data-chart-type="line">
            </canvas>
        </div>

        <div class="button-container">
            <div class="button-wrapper switch-shop" data-switch-button="true" data-industry="false" data-values-key="{$dataKey}" data-time="weeks">
                <label class="switch">
                    <input type="checkbox" checked="checked">
                    <span class="slider round"></span>
                </label>
                <div>[[ $t('shopTitle') ]]</div>
            </div>

            <div class="button-wrapper switch-last-year"
                 data-switch-button="true"
                 data-industry="false"
                 data-last-year="true"
                 data-values-key="{$dataKey}"
                 data-time="weeks">
                <label class="switch">
                    <input type="checkbox" checked="checked">
                    <span class="slider round"></span>
                </label>
                <div>[[ $t('previousTitle') ]]</div>
            </div>
        </div>
    </div>

    {include file="backend/benchmark/template/local/include/computed_data.tpl" key="{$dataKey}"}
</form>
