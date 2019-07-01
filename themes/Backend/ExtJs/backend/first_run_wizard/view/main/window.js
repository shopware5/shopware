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
 */

/**
 * Shopware First Run Wizard - Main Window
 *
 * @category  Shopware
 * @package   Shopware
 * @copyright Copyright (c) shopware AG (http://www.shopware.de)
 */

// {namespace name=backend/first_run_wizard/main}

// {block name="backend/first_run_wizard/view/main/window"}

Ext.define('Shopware.apps.FirstRunWizard.view.main.Window', {

    extend: 'Enlight.app.Window',
    cls: 'first-run-wizard',
    alias: 'widget.first-run-wizard',
    layout: 'border',

    height: '90%',

    width: 900,

    /**
     * Flag to only close the window when the process is finished or the user
     * confirms he wants to exit (using a confirmation popup box)
     */
    confirmedClose: false,

    /**
     * Flag to prevent window from being minimizable
     */
    minimizable: false,

    /**
     * If the connection to SBP has been detected
     * Defaults to null, meaning unkown
     */
    isConnected: null,

    /**
     * Current step of the wizard
     */
    currentStep: 0,

    snippets: {
        title: '{s name=window/title}Shopware First Run Wizard{/s}',
        close: {
            title: '{s name=window/close/title}Close wizard?{/s}',
            message: '{s name=window/close/message}Are you sure you want to close the configuration wizard? You will not be able to access it later, and must configure Shopware manually.{/s}'
        },
        buttons: {
            back: '{s name=window/buttons/back}Back{/s}',
            next: '{s name=window/buttons/next}Next{/s}',
            skip: '{s name=window/buttons/skip}Skip{/s}',
            finish: '{s name=window/buttons/finish}Finish{/s}'
        }
    },

    basePath: '{link file=""}',

    navigationIndex: {
        localization: 0,
        demo_data: 1,
        paypal: 2,
        recommendation: 3,
        config: 4,
        finish: 5
    },

    initComponent: function() {
        var me = this;

        me.title = me.snippets.title;

        if (me.basePath.substr(-1) === '/') {
            me.basePath = me.basePath.substr(0, me.basePath.length - 1);
        }

        me.items = me.createItems();

        me.dockedItems = me.createDockedItems();

        me.on('beforeclose', me.beforeCloseWindow, me);

        me.callParent(arguments);
    },

    beforeCloseWindow: function() {
        var me = this;

        if (me.confirmedClose === false) {
            Ext.MessageBox.confirm(me.snippets.close.title, me.snippets.close.message, function(btn) {
                if (btn === 'yes') {
                    me.confirmedClose = true;
                    me.close();
                }
            });
        } else {
            me.fireEvent('update-step', 0);
        }

        return me.confirmedClose;
    },

    createItems: function() {
        var me = this, items = [];

        items.push(
            Ext.create('Ext.container.Container', {
                region: 'north',
                name: 'header',
                cls: 'header',
                height: 76,
                items: [
                    Ext.create('Ext.Img', {
                        src: me.basePath + '/themes/Backend/ExtJs/backend/_resources/resources/themes/images/shopware-ui/frw-logo.png',
                        renderTo: Ext.getBody()
                    })
                ]
            })
        );

        me.cardContainer = Ext.create('Ext.container.Container', {
            layout: 'card',
            region: 'center',
            autoScroll: true,
            name: 'card-container',
            cls: 'card-container',
            items: me.createProcessItems()
        });

        items.push(
            Ext.create('Ext.container.Container', {
                region: 'center',
                layout: 'border',
                items: [
                    me.createNavigation(),
                    me.cardContainer
                ]
            })
        );

        return items;
    },

    createProcessItems: function() {
        var me = this, items = [];

        items.push(
            Ext.create('Shopware.apps.FirstRunWizard.view.main.Localization', {
                connectionResult: me.isConnected
            }),
            Ext.create('Shopware.apps.FirstRunWizard.view.main.DemoData'),
            Ext.create('Shopware.apps.FirstRunWizard.view.main.PayPal'),
            Ext.create('Shopware.apps.FirstRunWizard.view.main.Recommendation'),
            Ext.create('Shopware.apps.FirstRunWizard.view.main.Config'),
            Ext.create('Shopware.apps.FirstRunWizard.view.main.Finish')
        );

        return items;
    },

    createNavigation: function() {
        this.navigationStore = Ext.create('Ext.data.Store', {
            fields: ['name', 'needsConnection'],
            data: [
                { id: this.navigationIndex.localization, name: '{s name=localization/content/title}Localization{/s}', needsConnection: false },
                { id: this.navigationIndex.demo_data, name: '{s name=demo_data/content/title}Demo Data{/s}', needsConnection: true },
                { id: this.navigationIndex.paypal, name: '{s name=pay_pal/content/title}PayPal{/s}', needsConnection: true },
                { id: this.navigationIndex.recommendation, name: '{s name=recommendation/content/title}Recommendations{/s}', needsConnection: true },
                { id: this.navigationIndex.config, name: '{s name=config/content/title}Configuration{/s}', needsConnection: false },
                { id: this.navigationIndex.finish, name: '{s name=finish/content/title}Finished{/s}', needsConnection: false }
            ]
        });

        this.updateNavigation();

        this.navigation = Ext.create('Ext.view.View', {
            tpl: this.createNavigationTemplate(),
            width: 200,
            name: 'navigation',
            store: this.navigationStore,
            region: 'west',
            itemSelector: '.item',
            cls: 'wizard-navigation'
        });

        return this.navigation;
    },

    updateNavigation: function() {
        var me = this;

        me.navigationStore.each(
            function(elem) {
                elem.set('disabled', (elem.get('needsConnection') === true && me.isConnected !== true));
                return true;
            }
        );
    },

    createNavigationTemplate: function() {
        var me = this;

        return new Ext.XTemplate(
            '{literal}',
            '<tpl for=".">',
                '<tpl if="disabled">',
                    '<div class="item disabled"><span>{name}</span></div>',
                '<tpl elseif="this.current() == xindex">',
                    '<div class="item current"><span>{name}</span></div>',
                '<tpl elseif="this.current() &gt; xindex">',
                    '<div class="item previous"><span>{name}</span></div>',
                '<tpl elseif="this.current() &lt; xindex">',
                    '<div class="item next"><span>{name}</span></div>',
                '</tpl>',
            '</tpl>',
            '{/literal}',
            {
                current: function() {
                    return me.currentStep;
                }
            }
        );
    },

    createDockedItems: function() {
        var me = this, items = [];

        me.previousButton = Ext.create('Ext.button.Button', {
            text: me.snippets.buttons.back,
            cls: 'secondary',
            name: 'previous-button',
            width: 180,
            handler: function() {
                var currentContainer = me.cardContainer.getLayout().getActiveItem(),
                    name = currentContainer.name;

                if (!Ext.isEmpty(name) && currentContainer.hasListener('navigate-back-' + name)) {
                    me.fireEvent('navigate-back-' + name, me, function() {
                        me.fireEvent('navigate-back', me);
                    });
                } else {
                    me.fireEvent('navigate-back', me);
                }
            }
        });

        me.skipButton = Ext.create('Ext.button.Button', {
            text: me.snippets.buttons.skip,
            cls: 'secondary',
            name: 'skip-button',
            hidden: true,
            width: 180,
            handler: function() {
                var currentContainer = me.cardContainer.getLayout().getActiveItem(),
                    name = currentContainer.name;

                if (!Ext.isEmpty(name) && currentContainer.hasListener('navigate-skip-' + name)) {
                    me.fireEvent('navigate-skip-' + name, me, function() {
                        me.fireEvent('navigate-skip', me);
                    });
                } else {
                    me.fireEvent('navigate-skip', me);
                }
            }
        });

        me.nextButton = Ext.create('Ext.button.Button', {
            text: me.snippets.buttons.next,
            cls: 'primary',
            name: 'next-button',
            width: 180,
            handler: function() {
                var currentContainer = me.cardContainer.getLayout().getActiveItem(),
                    name = currentContainer.name;

                if (!Ext.isEmpty(name) && currentContainer.hasListener('navigate-next-' + name)) {
                    me.fireEvent('navigate-next-' + name, me, function () {
                        me.fireEvent('navigate-next', me);
                    });
                } else {
                    me.fireEvent('navigate-next', me);
                }
            }
        });

        items.push(me.previousButton);
        items.push('->');
        items.push(me.skipButton);
        items.push(me.nextButton);

        me.toolbar = Ext.create('Ext.toolbar.Toolbar', {
            items: items,
            name: 'button-toolbar',
            dock: 'bottom'
        });

        return [me.toolbar];
    }

});

// {/block}
