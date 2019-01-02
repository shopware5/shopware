{namespace name="frontend/index/datepicker"}

{block name="frontend_index_date_picker_config"}
<script>
    var datePickerGlobalConfig = datePickerGlobalConfig || {ldelim}
        locale: {ldelim}
            weekdays: {ldelim}
                shorthand: [{s name="datePickerWeekdaysShorthand"}{/s}],
                longhand: [{s name="datePickerWeekdaysLonghand"}{/s}]
            {rdelim},
            months: {ldelim}
                shorthand: [{s name="datePickerMonthsShorthand"}{/s}],
                longhand: [{s name="datePickerMonthsLonghand"}{/s}]
            {rdelim},
            firstDayOfWeek: {s name="datePickerFirstDayOfWeek"}{/s},
            weekAbbreviation: {s name="datePickerWeekAbbreviation"}{/s},
            rangeSeparator: {s name="datePickerRangeSeparator"}{/s},
            scrollTitle: {s name="datePickerScrollTitle"}{/s},
            toggleTitle: {s name="datePickerToggleTitle"}{/s},
            daysInMonth: [{s name="datePickerDaysInMonth"}{/s}]
        {rdelim},
        dateFormat: {s name="datePickerDateFormat"}{/s},
        timeFormat: {s name="datePickerTimeFormat"}{/s},
        altFormat: {s name="datePickerDisplayDateFormat"}{/s},
        altTimeFormat: {s name="datePickerDisplayTimeFormat"}{/s}
    {rdelim};
</script>
{/block}