/**
 * Shopware 5
 * Copyright (c) shopware AG
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
 * @version    $Id$
 * @author shopware AG
 */

/**
 * todo@all: Documentation
 */
Ext.define('Shopware.form.PasswordStrengthMeter', {
    extend: 'Ext.form.field.Text',
    alias: 'widget.passwordmeter',
    inputType: 'password',

    reset: function() {
        this.callParent();
        this.updateMeter();
    },
//{literal}
    fieldSubTpl: [
        '<div><input id="{id}" type="{type}" ',
        '<tpl if="name">name="{name}" </tpl>',
        '<tpl if="size">size="{size}" </tpl>',
        '<tpl if="tabIdx">tabIndex="{tabIdx}" </tpl>',
        'class="{fieldCls} {typeCls}" autocomplete="off" /></div>',
        '<div class="' + Ext.baseCSSPrefix + 'form-strengthmeter">',
        '<div class="' + Ext.baseCSSPrefix + 'form-strengthmeter-scorebar">&nbsp;</div>',
        '</div>',
        {
            compiled: true,
            disableFormats: true
        }
    ],
//{/literal}
    // private
    onChange: function(newValue, oldValue) {
        this.updateMeter(newValue);
    },

    /**
     * Sets the width of the meter, based on the score
     *
     * @param { Object } e Private function
     */
    updateMeter : function(val) {
        var me = this, maxWidth, score, scoreWidth,
            objMeter = me.el.down('.' + Ext.baseCSSPrefix + 'form-strengthmeter'),
            scoreBar = me.el.down('.' + Ext.baseCSSPrefix + 'form-strengthmeter-scorebar');

        if (val){
            maxWidth = objMeter.getWidth();
            score = me.calcStrength(val);
            scoreWidth = maxWidth - (maxWidth / 100) * score;
            scoreBar.setWidth(scoreWidth, true);
        } else {
            scoreBar.setWidth('100%');
        }
    },

    /**
     * Calculates the strength of a password
     *
     * @param { Object } p The password that needs to be calculated
     * @return { int } intScore The strength score of the password
     */
    calcStrength: function(p) {
        // PASSWORD LENGTH
        var len = p.length,
            score = len;

        if (len > 0 && len <= 4) { // length 4 or
            // less
            score += len
        } else if (len >= 5 && len <= 7) {
            // length between 5 and 7
            score += 6;
        } else if (len >= 8 && len <= 15) {
            // length between 8 and 15
            score += 12;
        } else if (len >= 16) { // length 16 or more
            score += 18;
        }

        // LETTERS (Not exactly implemented as dictacted above
        // because of my limited understanding of Regex)
        if (p.match(/[a-z]/)) {
            // [verified] at least one lower case letter
            score += 1;
        }
        if (p.match(/[A-Z]/)) { // [verified] at least one upper
            // case letter
            score += 5;
        }
        // NUMBERS
        if (p.match(/\d/)) { // [verified] at least one
            // number
            score += 5;
        }
        if (p.match(/(?:.*?\d){3}/)) {
            // [verified] at least three numbers
            score += 5;
        }

        // SPECIAL CHAR
        if (p.match(/[\!,@,#,$,%,\^,&,\*,\?,_,~]/)) {
            // [verified] at least one special character
            score += 5;
        }
        // [verified] at least two special characters
        if (p.match(/(?:.*?[\!,@,#,$,%,\^,&,\*,\?,_,~]){2}/)) {
            score += 5;
        }

        // COMBOS
        if (p.match(/(?=.*[a-z])(?=.*[A-Z])/)) {
            // [verified] both upper and lower case
            score += 2;
        }
        if (p.match(/(?=.*\d)(?=.*[a-z])(?=.*[A-Z])/)) {
            // [verified] both letters and numbers
            score += 2;
        }
        // [verified] letters, numbers, and special characters
        if (p.match(/(?=.*\d)(?=.*[a-z])(?=.*[A-Z])(?=.*[\!,@,#,$,%,\^,&,\*,\?,_,~])/)) {
            score += 2;
        }

        return Math.min(Math.round(score * 2), 100);
    }
});
