/**
 * Shopware 4.0
 * Copyright © 2012 shopware AG
 *
 * According to our dual licensing model, this program can be used either
 * under the terms of the GNU Affero General Public License, version 3,
 * or under a proprietary license.
 *
 * The texts of the GNU Affero General Public License with an additional
 * permission and of our proprietary license can be found at and
 * in the LICENSE file you have received along with this program.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU Affero General Public License for more details.
 *
 * "Shopware" is a registered trademark of shopware AG.
 * The licensing of the program under the AGPLv3 does not imply a
 * trademark license. Therefore any rights, title and interest in
 * our trademarks remain entirely with us.
 *
 * @category   Shopware
 * @package    Base
 * @subpackage Component
 * @copyright  Copyright (c) 2012, shopware AG (http://www.shopware.de)
 * @version    $Id$
 * @author shopware AG
 */
//{namespace name=backend/base/vtype}
/**
 * todo@all: Documentation
 */
Ext.apply(Ext.form.VTypes, {
    password : function(val, field) {
        if (field.initialPassField) {
            var pwd = Ext.getCmp(field.initialPassField);
            return (val == pwd.getValue());
        }
        return true;
    },
    passwordText : '{s name=password_match}The inserted passwords are not equal{/s}'
});

Ext.apply(Ext.form.field.VTypes, {
    remote:function (val, field) {

        if (!field.validationUrl) {
            Ext.Error.raise('The remote vType validation needs a validationUrl property');
            return false;
        }

        if (!field.validationErrorMsg) {
            Ext.Error.raise('The remote vType validation needs a validationErrorMsg property');
            return false;
        }

        // Is the field rendered?
        if(!field.rendered) {
            return true;
        }

        if(Ext.isDefined(field.oldValid)) {
            if(val == field.oldValue) {
                return field.oldValid;
            }
        }
        field.oldValue = val;

        if (!field.validationRequestParams){
            parameters = {
                value:val,
                param:field.validationRequestParam
            };
        }else {
            parameters = field.validationRequestParams;
            parameters.value = val;
        }

        Ext.Ajax.request({
            async: false,
            url:field.validationUrl,
            params: parameters,
            success:function (response) {

                if (!response.responseText) {
                    //field is invalid setting the custom error message
                    field.markInvalid(field.validationErrorMsg);
                    field.vtypeText = field.validationErrorMsg;

                    field.oldValid = false;
                }
                else {
                    field.clearInvalid();

                    field.oldValid = true;
                }
            },
            failure:function (response) {
                //todo@all: implement right error handling
                Shopware.Msg.createGrowlMessage('', field.validationErrorMsg, '', 'growl', false);
                return false;
            }
        });
        return field.oldValid;
    },
    /**
     * Date Range Check - Checks if an start date is not after a given end date and vice versa
     * 
     * <code>
     *     var dateStart =  new Ext.form.DateField( {
     *       name: 'dataStart',
     *       id: 'dataStart',
     *       fieldLabel: "Date Start",
     *       hideLabel: false,
     *       vtype: 'daterange',//type here
     *       endDateField: 'dateEnd'//and end date field
     *     });
     *     
     *     var dateEnd =  new Ext.form.DateField( {
     *       id: 'dateEnd',
     *       name: 'dateEnd',
     *       fieldLabel: "Date End",
     *       hideLabel: false,
     *       vtype: 'daterange',//add type
     *       startDateField: 'dataStart'//start date field
     *     });
     * </code>
     * 
     * @param val Date
     * @param field
     */
    daterange:function(val, field) 
    {
        var date = field.parseDate(val);

        if (!date) {
            return false;
        }
        // build range
        if (field.startDateField && (!this.dateRangeMax || (date.getTime() != this.dateRangeMax.getTime()))) {
            var start = field.up('form').down('#' + field.startDateField);
            start.setMaxValue(date);
            start.validate();
            this.dateRangeMax = date;
        }
        else if (field.endDateField && (!this.dateRangeMin || (date.getTime() != this.dateRangeMin.getTime()))) {
            var end = field.up('form').down('#' + field.endDateField);
            end.setMinValue(date);
            end.validate();
            this.dateRangeMin = date;
        }
        /*
         * Always return true since we're only using this vtype to set the
         * min/max allowed values (these are tested for after the vtype test)
         */
        return true; 
    }
});