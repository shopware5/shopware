/**
 * Shopware UI - ComboBox Override
 *
 * This files provides an override to address the
 * boundlist easier in our Selenium Tests.
 *
 * The override adds an addtional HTML5 "data"-attrbute
 * to the element.
 *
 * shopware AG (c) 2012. All rights reserved.
 *
 * @link http://www.shopware.de/
 * @author t.garcia
 * @date 2012-05-03
 * @license http://www.shopware.de/license
 * @package overrides
 */
Ext.override(Ext.form.field.Time,
/** @lends Ext.form.field.Time */
{
    /** Extends the default function to allow parsing partial times using the alternative formats. */
    safeParse: function(value, format){
        var me = this,
            utilDate = Ext.Date,
            parsedDate,
            result = null,
            altFormats = me.altFormats,
            altFormatsArray = me.altFormatsArray,
            j = 0,
            len;

        if (utilDate.formatContainsDateInfo(format)) {
            // assume we've been given a full date
            result = utilDate.parse(value, format);
        } else {
            // Use our initial safe date
            parsedDate = utilDate.parse(me.initDate + ' ' + value, me.initDateFormat + ' ' + format);
            if (parsedDate) {
                result = parsedDate;
            } else {
                if (!result && altFormats) {
                    altFormatsArray = altFormatsArray || altFormats.split('|');
                    len = altFormatsArray.length;
                    for (; j < len && !result; ++j) {
                        parsedDate = utilDate.parse(me.initDate + ' ' + value, me.initDateFormat + ' ' + altFormatsArray[j]);
                        if (parsedDate) {
                            result = parsedDate;
                        }
                    }
                }
            }
        }
        return result;
    }
});