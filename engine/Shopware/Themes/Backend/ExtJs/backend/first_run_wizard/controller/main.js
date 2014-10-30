/**
 * Shopware 4
 * Copyright © shopware AG
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
 */

/**
 * Shopware First Run Wizard - Shopware Id tab
 *
 * @category  Shopware
 * @package   Shopware
 * @copyright Copyright (c) shopware AG (http://www.shopware.de)
 */

//{namespace name=backend/first_run_wizard/main}
//{block name="backend/first_run_wizard/controller/main"}

Ext.define('Shopware.apps.FirstRunWizard.controller.Main', {

    extend:'Ext.app.Controller',
    mainWindow: null,

    refs: [
        { ref: 'wizardWindow', selector: 'first-run-wizard' },
        { ref: 'cardContainer', selector: 'first-run-wizard container[name=card-container]' },
        { ref: 'nextButton', selector: 'first-run-wizard button[name=next-button]' },
        { ref: 'previousButton', selector: 'first-run-wizard button[name=previous-button]' },
        { ref: 'buttonToolbar', selector: 'first-run-wizard toolbar[name=button-toolbar]' },
        { ref: 'navigation', selector: 'first-run-wizard dataview[name=navigation]' }
    ],

    init: function () {
        var me = this;

        me.firstRunWizardStep = parseInt(Ext.util.Cookies.get('firstRunWizardStep'));
        me.firstRunWizardIsConnected = Ext.util.Cookies.get('firstRunWizardIsConnected');

        if (Ext.isEmpty(me.firstRunWizardStep)) {
            me.firstRunWizardStep = 0;
        }
        if (Ext.isEmpty(me.firstRunWizardIsConnected)) {
            me.firstRunWizardIsConnected = null;
        } else {
            me.firstRunWizardIsConnected = me.firstRunWizardIsConnected == 'true';
        }

        me.control({
            'first-run-wizard': {
                'update-step': me.updateServerStep,
                'navigate-next': me.navigateNext,
                'navigate-back': me.navigateBack
            }
        });

        me.getView('main.Window').create({
            currentStep: me.firstRunWizardStep,
            isConnected: me.firstRunWizardIsConnected
        }).show();

        me.validateButtons();

        me.callParent(arguments);
    },

    navigateNext: function() {
        var me = this,
            cardContainer = me.getCardContainer(),
            calculatedStep;

        calculatedStep = me.switchNavigation(+1);

        if (calculatedStep == null) {
            me.getWizardWindow().confirmedClose = true;
            me.getWizardWindow().close();
            return;
        }

        cardContainer.getLayout().setActiveItem(calculatedStep);
        me.validateButtons();
    },

    navigateBack: function() {
        var me = this,
            cardContainer = me.getCardContainer(),
            calculatedStep;

        calculatedStep = me.switchNavigation(-1);

        cardContainer.getLayout().setActiveItem(calculatedStep);
        me.validateButtons();
    },

    updateServerStep: function(newStep) {
        var me = this;

        me.firstRunWizardStep = newStep;

        if (newStep === 0) {
            Ext.util.Cookies.clear('firstRunWizardStep');
            Ext.util.Cookies.clear('firstRunWizardIsConnected');
            Ext.Ajax.request({
                url: '{url controller="firstRunWizard" action="saveStep"}',
                method: 'POST',
                params: {
                    value: newStep
                }
            });
            Ext.Ajax.request({
                url: '{url controller="Cache" action="clearCache"}',
                method: 'POST',
                params: {
                    'cache[config]' : 'on',
                    'cache[template]' : 'on',
                    'cache[theme]' : 'on',
                    'cache[http]' : 'on',
                    'cache[proxy]' : 'on',
                    'cache[search]' : 'on',
                    'cache[router]' : 'on'
                },
                callback: function() {
                    location.reload();
                }
            });
        } else {
            Ext.util.Cookies.set('firstRunWizardStep', newStep);
        }
    },

    switchNavigation: function(direction) {
        var me = this,
            navigation = me.getNavigation(),
            calculatedDirection = direction;

        var index = me.firstRunWizardStep;

        do {
            if (navigation.getStore().getCount() < index + calculatedDirection) {
                return null;
            }

            if (navigation.getStore().getAt(index + calculatedDirection - 1).get('disabled') == true ) {
                calculatedDirection += direction;
            } else {
                break;
            }
        }
        while (true);

        me.getWizardWindow().currentStep = index + calculatedDirection;
        me.firstRunWizardStep = index + calculatedDirection;

        me.getNavigation().refresh();
        me.updateServerStep(index + calculatedDirection);

        return index + calculatedDirection - 1;
    },

    validateButtons: function() {
        var me = this,
            layout = me.getCardContainer().getLayout(),
            activeLayout, buttons, defaultButtons,
            customButtons = {},
            toolbar = me.getButtonToolbar();

        activeLayout = layout.getActiveItem();
        if (typeof activeLayout.getButtons === "function") {
            customButtons = activeLayout.getButtons();
        }

        defaultButtons = {
            previous: {
                enabled: true,
                text: me.getWizardWindow().snippets.buttons.back
            },
            next: {
                enabled: true,
                text: me.getWizardWindow().snippets.buttons.next
            },
            extraButtonSettings: null
        };

        buttons = Ext.Object.merge(defaultButtons, customButtons);

        me.getPreviousButton().setText(buttons.previous.text);
        if (buttons.previous.enabled === true) {
            me.getPreviousButton().enable();
        } else {
            me.getPreviousButton().disable();
        }

        me.getNextButton().setText(buttons.next.text);
        if (buttons.next.enabled === true) {
            me.getNextButton().enable();
        } else {
            me.getNextButton().disable();
        }

        if (!Ext.isEmpty(buttons.extraButtonSettings)) {
            toolbar.extraButton = new Ext.create('Ext.button.Button', buttons.extraButtonSettings);
            toolbar.add(toolbar.extraButton);
        } else if (!Ext.isEmpty(toolbar.extraButton)) {
            toolbar.remove(toolbar.extraButton);
            toolbar.extraButton = null;
        }
    }
});

//{/block}