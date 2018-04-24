<form>
    <div data-time-switcher="true" data-values-key="{$dataKey}">
        <label><input type="button" class="btn timescale is-active timescale-week" name="weeks" value="Woche" /></label>
        <label><input type="button" class="btn timescale timescale-month" name="months" value="Monat" /></label>
        <label><input type="button" class="btn timescale timescale-year" name="years" value="Jahr" /></label>
    </div>

    <div class="graph-container">
        <canvas height="260"
                data-benchmark-graph="true"
                data-name="{$dataKey}"
                data-include-business="false"
                data-time="weeks"
                data-chart-type="{$chartType|default:"line"}">
        </canvas>
    </div>

    {include file="backend/benchmark/template/local/include/computed_data.tpl"}
</form>
