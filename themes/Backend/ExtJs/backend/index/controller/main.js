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
 * SHOPWARE UI - Index Controller
 *
 * This file contains the index application which represents
 * the basic backend structure.
 */

//{namespace name=backend/index/controller/main}
//{block name="backend/index/controller/main"}
Ext.define('Shopware.apps.Index.controller.Main', {
    extend: 'Ext.app.Controller',

    /**
     * Creates the necessary event listener for this
     * specific controller and opens a new Ext.window.Window
     * to display the subapplication
     *
     * @public
     * @return void
     */
    init: function() {
        var me = this,
            firstRunWizardStep = Ext.util.Cookies.get('firstRunWizardStep'),
            firstRunWizardEnabled = me.subApplication.firstRunWizardEnabled,
            enableInstallationFeedback = me.subApplication.enableInstallationFeedback,
            enableBetaFeedback = me.subApplication.enableBetaFeedback,
            biOverviewEnabled = me.subApplication.biOverviewEnabled;

        if (!firstRunWizardEnabled) {
            firstRunWizardStep = 0;
        } else if (Ext.isEmpty(firstRunWizardStep)) {
            firstRunWizardStep = firstRunWizardEnabled;
        }

        if (firstRunWizardStep > 0) {
            Ext.util.Cookies.set('firstRunWizardStep', firstRunWizardStep);

            Shopware.app.Application.addSubApplication({
                    name: 'Shopware.apps.PluginManager',
                    params: {
                        hidden: true
                    }
                },
                undefined,
                function() {
                    Shopware.app.Application.addSubApplication({
                        name: 'Shopware.apps.FirstRunWizard'
                    });
                }
            );


        } else {
            me.initBackendDesktop();

            if (enableInstallationFeedback) {
                Ext.Function.defer(function() {
                    Shopware.app.Application.addSubApplication({
                        name: 'Shopware.apps.Feedback',
                        params: {
                            installationFeedback: true
                        }
                    });
                }, 2000);
            }

            if (enableBetaFeedback && (typeof Storage !== "undefined")) {
                var item = window.localStorage.getItem("hideBetaFeedback");
                if (!item) {
                    Ext.Function.defer(function() {
                        Shopware.app.Application.addSubApplication({
                            name: 'Shopware.apps.Feedback',
                            params: {
                                previewFeedback: true
                            }
                        });
                    }, 2000);
                }
            }

            /*{if {acl_is_allowed privilege=manage resource=benchmark}}*/
            if (biOverviewEnabled) {
                Ext.Function.defer(function() {
                    Shopware.app.Application.addSubApplication({
                        name: 'Shopware.apps.Benchmark',
                        params: {
                            isTeaser: true
                        }
                    });
                }, 2000);
            }
            /* {/if} */
        }
    },

    initBackendDesktop: function() {
        var me = this,
            mainApp = Shopware.app.Application,
            viewport = mainApp.viewport = Ext.create('Shopware.container.Viewport');

        /** Create our menu and footer */
        me.menu =  me.getView('Menu').create();
        me.footer = me.getView('Footer').create();

        viewport.add(me.menu);
        viewport.add(me.footer);

        me.addKeyboardEvents();
        me.checkLoginStatus();
        /*{if {acl_is_allowed privilege=submit resource=benchmark}}*/
        if (me.subApplication.biIsActive) {
            me.checkBenchmarksStatus();
        }
        /*{/if}*/
    },

    /**
     * This method provides experimental support
     * for shortcuts in the Shopware Backend.
     *
     * @return void
     */
    addKeyboardEvents: function() {
        var me = this, map,
            msg = Shopware.Notification;

        map = new Ext.util.KeyMap(document, [
            /*{if {acl_is_allowed privilege=read resource=article}}*/
            // New article - CTRL + ALT + N
            {
                key: 'n',
                ctrl: true,
                alt: true,
                fn: function() {
                    msg.createGrowlMessage('{s name=title/key_pressed}{/s}', '{s name=content/article_open}{/s}');
                    openNewModule('Shopware.apps.Article', {
                        params: {
                            articleId: null
                        }
                    });
                }
            },
            /*{/if}*/

            /*{if {acl_is_allowed privilege=read resource=articlelist}}*/
            // Article overview - CTRL + ALT + O
            {
                key: "o",
                ctrl: true,
                alt: true,
                fn: function(){
                    msg.createGrowlMessage('{s name=title/key_pressed}{/s}', '{s name=content/article_overview_open}Article overview module will be opened.{/s}');
                    openNewModule('Shopware.apps.ArticleList');
                }
            },
            /*{/if}*/

            /*{if {acl_is_allowed privilege=read resource=order}}*/
            // Order overview - CTRL + ALT + B
            {
                key: "b",
                ctrl: true,
                alt: true,
                fn: function() {
                    msg.createGrowlMessage('{s name=title/key_pressed}{/s}', '{s name=content/order_open}{/s}');
                    openNewModule('Shopware.apps.Order');
                }
            },
            /*{/if}*/

            /*{if {acl_is_allowed privilege=read resource=customer}}*/
             // Order overview - CTRL + ALT + K
            {
                key: "k",
                ctrl: true,
                alt: true,
                fn: function(){
                    msg.createGrowlMessage('{s name=title/key_pressed}{/s}', '{s name=content/customer_open}{/s}');
                    openNewModule('Shopware.apps.Customer');
                }
            },
            /*{/if}*/

            // Keymap Overview - CTRL + ALT + H
            {
                key: 'h',
                ctrl: true,
                alt: true,
                fn: function() {
                    createKeyNavOverlay();
                }
            },

            /*{if {acl_is_allowed privilege=read resource=pluginmanager}}*/
            // Plugin Manager - CTRL + ALT + P
            {
                key: 'p',
                ctrl: true,
                alt: true,
                fn: function() {
                    msg.createGrowlMessage('{s name=title/key_pressed}{/s}', '{s name=content/plugin_open}{/s}');
                    openNewModule('Shopware.apps.PluginManager');
                }
            },
            /*{/if}*/

            /*{if {acl_is_allowed privilege=clear resource=performance}}*/
            // Cache Manager - CTRL + ALT + TFX
            {
                key: 'tfx',
                ctrl: true,
                alt: true,
                handler: function(keyCode, e) {
                    switch(keyCode) {
                        // Frontend Cache - CTRL + ALT + F
                        case 70: var action = 'Frontend'; break;
                        // Template Cache - CTRL + ALT + T
                        case 84: var action = 'Template'; break;
                        // Config Cache - CTRL + ALT + X
                        case 88: var action = 'Config'; break;
                        default: return;
                    }
                    Shopware.app.Application.addSubApplication({
                        name: 'Shopware.apps.Performance',
                        action: action
                    });
                }
            }
            /*{/if}*/
        ]);
    },

    /**
     * Helper method which checks every 30 seconds wether the user is logged in.
     *
     * @private
     * @return void
     */
    checkLoginStatus: function () {
        Ext.TaskManager.start({
            interval: 30000,
            run: function () {
                Ext.Ajax.request({
                    url: '{url controller=login action=getLoginStatus}',
                    success: function(response) {
                        var json = Ext.decode(response.responseText);

                        if(!json.success) {
                            window.location.href = '{url controller=index}';
                        }
                    },
                    failure: function() {
                        window.location.href = '{url controller=index}';
                    }
                });
            }
        });
    },

    /**
     * Helper method which checks for new Benchmark data periodically (every 10 seconds).
     *
     * @private
     * @return void
     */
    checkBenchmarksStatus: function () {
        var interval = 10000,
            checkBenchmarksFn = function () {
                Ext.Ajax.request({
                    url: '{url controller=benchmark action=checkBenchmarks}',
                    success: function(response) {
                        var res = Ext.decode(response.responseText);

                        interval = 10000;

                        // Set interval to 5 minutes if all data was sent
                        if (!res.statistics && res.bi) {
                            interval = 300000;
                        }

                        // If we received new BI statistics, we print a growl message
                        if (res.bi) {
                            Shopware.Notification.createStickyGrowlMessage({
                                title: '{s name=title/new_benchmark}{/s}',
                                text: '{s name=content/new_benchmark}{/s}',
                                btnDetail: {
                                    text: '{s name=open}{/s}',
                                    callback: function () {
                                        Shopware.app.Application.addSubApplication({
                                            name: 'Shopware.apps.Benchmark',
                                            params: {
                                                shopId: res.shopId
                                            }
                                        });
                                    }
                                }
                            });
                        }

                        // If neither sending nor receiving is necessary, set interval to 12 hours
                        if (!res.statistics && !res.bi && !res.message) {
                            interval = 43200000;
                        }

                        if (!res.success) {
                            interval = 43200000;
                            var cur = new Date();
                            cur.setSeconds(cur.getSeconds() + 900);

                            Ext.util.Cookies.set('benchmarkWait', '1', cur);
                        }

                        window.setTimeout(checkBenchmarksFn, interval);
                    }
                });
            };

        if (Ext.util.Cookies.get('benchmarkWait')) {
            return;
        }

        window.setTimeout(checkBenchmarksFn, interval);
    }
});

Ext.define('Shopware.apps.Index.view.Main', {
    extend: 'Ext.panel.Panel',
    alias: 'widget.index-desktoppanel',
    cls: 'main-backend-holder',
    height: '100%',
    width: '100%',
    border: false,
    plain: true,
    frame: false,
    region: 'center',
    layout: 'fit',
    bodyStyle: 'background: transparent'
});


/**
 * Wrapper methods which allows to open deprecated
 * modules in the new ExtJS 4 structure.
 *
 * Note that this method is only an alias and isn't
 * needed for new modules. New modules will be loaded
 * with the method Shopware.app.Application.addSubApplication
 * or the shorthand openNewModule()
 *
 * @param [string] module - the module to load
 * @param [boolean] forceNewWindow - has no impact
 * @param [object] requestConfig - additional params which will passed to the module
 * @return void
 */
loadSkeleton = function(module, forceNewWindow, requestConfig) {

    var options = { };
    options.name = 'Shopware.apps.Deprecated';
    options.moduleName = module;
    options.requestConfig = requestConfig || {};

    Shopware.app.Application.addSubApplication(options);
};

/**
 * Wrapper method which loads newer modules. This method
 * is mostly used by backend modules which are shipped
 * within a plugin
 *
 * @param [string] controller - the controllername to load
 * @return void
 */
openAction = function(controller, action) {
    var options =  {};
    options.name = 'Shopware.apps.Deprecated';
    options.controllerName = controller;
    options.actionName = action;
    Shopware.app.Application.addSubApplication(options);
};

/**
 * Initialize a new sub application. This method
 * will be used in the future to load new
 * backend modules
 *
 * @param [string] subapp - the complete name of the controller
 * @param [object] options - additional options
 * @return void
 *
 * @example openModule('Shopware.apps.Auth')
 */
openNewModule = function(subapp, options) {
    options = options || { };
    options.name = subapp;
    Shopware.app.Application.addSubApplication(options);
};

createKeyNavOverlay = function() {
    var store = Ext.create('Ext.data.Store', {
            fields: [ 'name', 'key', 'alt', 'ctrl' ],
            data: [
                /*{if {acl_is_allowed privilege=read resource=article}}*/
                { name: '{s name=title/article}Article{/s}', key: 'n', alt: true , ctrl: true },
                /*{/if}*/
                /*{if {acl_is_allowed privilege=read resource=articlelist}}*/
                { name: '{s name=title/article_overview}Article overview{/s}', key: 'o', alt: true , ctrl: true },
                /*{/if}*/
                /*{if {acl_is_allowed privilege=read resource=order}}*/
                { name: '{s name=title/order}Order{/s}', key: 'b', alt: true , ctrl: true },
                /*{/if}*/
                /*{if {acl_is_allowed privilege=read resource=customer}}*/
                { name: '{s name=title/customer}Customer{/s}', key: 'k', alt: true , ctrl: true },
                /*{/if}*/
                /*{if {acl_is_allowed privilege=read resource=pluginmanager}}*/
                { name: '{s name=title/plugin_manager}Plugin manager{/s}', key: 'p', alt: true , ctrl: true },
                /*{/if}*/
                /*{if {acl_is_allowed privilege=clear resource=performance}}*/
                { name: '{s name=title/cache_template}Clear template cache{/s}', key: 't', alt: true , ctrl: true },
                { name: '{s name=title/cache_config}Clear config cache{/s}', key: 'x', alt: true , ctrl: true },
                { name: '{s name=title/cache_frontend}Clear shop cache{/s}', key: 'f', alt: true , ctrl: true }
                /*{/if}*/
            ]
        }),
        tpl = new Ext.XTemplate(
            '{literal}<tpl for=".">',
                '<div class="row">',
                    '<span class="title">{name}:</span>',
                    '<div class="keys">',

                        // Ctrl key
                        '<tpl if="ctrl === true">',
                            '<span class="sprite-key_ctrl_alternative">ctrl</span>',
                        '</tpl>',

                        // Alt key
                        '<tpl if="alt === true">',
                            '<span class="key_sep">+</span>',
                            '<span class="sprite-key_alt_alternative">alt</span>',
                        '</tpl>',

                        // Output the actual key
                        '<span class="key_sep">+</span>',
                        '<span class="sprite-key_{key}">{key}</span>',
                    '</div>',
                '</div>',
            '</tpl>{/literal}'
        ),
        emptyTpl = '<span class="no-shortcuts">{s name=shortcuts/no_shortcuts_acl}Due to your permissions, there are no shortcuts available{/s}</span>',
        itemCount = store.totalCount,
        dataView = Ext.create('Ext.view.View', {
            store: store,
            tpl: itemCount ? tpl : emptyTpl
        });

    var win = Ext.create('Ext.window.Window', {
        modal: true,
        layout: 'fit',
        title: '{s name=title/keyboard_shortcuts}Keyboard shortcuts{/s}',
        width: 500,
        height: 400,
        bodyPadding: 20,
        autoScroll: true,
        cls: Ext.baseCSSPrefix + 'shortcut-overlay',
        items: [ dataView ]
    });
    win.show();
};

/**
 * Proxy method which opens up the specific module
 * if the user clicks on an entry in the search result.
 *
 * @public
 * @param [string] module - Name of the module
 * @param [integer] id - id of the item
 * @return [boolean]
 */
openSearchResult = function(module, id) {
    // Force the id to be an integer
    id = ~~(1 * id);

    // Hide search drop down
    Ext.defer(function() {
        Shopware.searchField.searchDropDown.hide();
    }, 100);

    switch(module) {
        case 'articles':
            Shopware.app.Application.addSubApplication({
                name: 'Shopware.apps.Article',
                action: 'detail',
                params: {
                    articleId: id
                }
            });
            break;
        case 'customers':
            Shopware.app.Application.addSubApplication({
                name: 'Shopware.apps.Customer',
                action: 'detail',
                params: {
                    customerId: id
                }
            });
            break;
        case 'orders':
            Shopware.app.Application.addSubApplication({
                name: 'Shopware.apps.Order',
                params: {
                    orderId: id
                }
            });
            break;
        default:
            break;
    }
    return false;
};

/**
 * Proxy method which just shows a growl like
 * message with the current version of Shopware.
 *
 * @public
 * @return void
 */
createShopwareVersionMessage = function() {

    var aboutWindow = Ext.create('Ext.window.Window', {
        autoShow: true,
        unstyled: true,
        baseCls: Ext.baseCSSPrefix + 'about-shopware',
        layout: 'border',
        width: 402,
        header: false,
        height: 302,
        resizable: false,
        closable: false,
        items: [{
            region: 'north',
            xtype: 'container',
            height: 126,
            cls: Ext.baseCSSPrefix + 'about-shopware-header-logo'
        }, {
            height: 35,
            xtype: 'container',
            region: 'south',
            cls: Ext.baseCSSPrefix + 'about-shopware-footer',
            html: '<a  href="https://www.shopware.com" target="_blank">{s name=about/footer}Copyright &copy; shopware AG. All rights reserved.{/s}</a>'
        }, {
            xtype: 'container',
            region: 'center',
            padding: '15 75',
            autoScroll: true,
            cls: Ext.baseCSSPrefix + 'about-shopware-content',
            html: '<p>' +
                    '<strong>Shopware {$SHOPWARE_VERSION} {$SHOPWARE_VERSION_TEXT}</strong>' +
                    '<span>Build Rev {$SHOPWARE_REVISION}</span></p>' +
                    '{if $product == "CE"}<p><strong>Community Edition under <a href="http://www.gnu.org/licenses/agpl.html" target="_blank">AGPL license</a></strong><span>No support included in this shopware package.</span></p>{else}' +
                    '<p><strong>{if $product == "PE"}Professional Edition{elseif $product == "PP"}Professional Plus Edition{elseif $product == "EE"}Enterprise Edition{elseif $product == "EB"}Enterprise Business Edition{elseif $product == "EC"}Enterprise Cluster Edition{/if} under commercial / proprietary license</strong><span>See <a href="https://api.shopware.com/gtc/en_GB.html" target="_blank">TOS</a> for details</span></p>{/if}' +

                    '<p><strong>Shopware 5 uses the following components</strong></p>' +
                    '<p><strong>Enlight 2</strong><span>BSD License</span><span>&nbsp;Origin: shopware AG</span></p>' +
                    '<p><strong>Zend Framework</strong><span>New BSD License</span><span>&nbsp;Origin: Zend Technologies</span></p>' +
                    '<p><strong>ExtJS 4</strong><span>GPL v3 License</span><span>&nbsp;Origin: Sencha Corp.</span></p>' +
                    '<p>If you want to develop proprietary extensions that makes use of ExtJS (ie extensions that are not licensed under the GNU Affero General Public License, version 3, or a compatible license), you´ll need to license shopware SDK to get the necessary rights for the distribution of your extensions / plugins.</p>' +
                    '<p><strong>Doctrine 2</strong><span>MIT License</span><span>&nbsp;Origin: http://www.doctrine-project.org/</span></p>' +
                    '<p><strong>TinyMCE 3</strong><span>LGPL 2.1 License</span><span>&nbsp;Origin: Moxiecode Systems AB.</span></p>' +
                    '<p><strong>Symfony 3</strong><span>MIT License</span><span>&nbsp;Origin: SensioLabs</span></p>' +
                    '<p><strong>Smarty 3</strong><span>LGPL 2.1 License</span><span>&nbsp;Origin: New Digital Group, Inc.</span></p>' +
                    '<p><strong>Ace</strong><span>BSD License</span><span>&nbsp;Origin: https://ace.c9.io/</span></p>' +
                    '<p><strong>MPDF</strong><span>GPL License</span><span>&nbsp;Origin: https://mpdf.github.io</span></p>' +
                    '<p><strong>FPDF</strong><span>License</span><span>&nbsp;Origin: http://www.fpdf.org/</span></p>' +
                    '<p><strong>Guzzle</strong><span>MIT License</span><span>&nbsp;Origin: http://guzzlephp.org</span></p>' +
                    '<p><strong>Less.php</strong><span>Apache-2.0</span><span>&nbsp;Origin: http://lessphp.gpeasy.com</span></p>' +
                    '<p><strong>Monolog</strong><span>MIT License</span><span>&nbsp;Origin: https://github.com/Seldaek/monolog</span></p>' +
                    '<p><strong>ElasticSearch</strong><span>LGPL License</span><span>&nbsp;Origin: https://github.com/elastic/elasticsearch-php</span></p>' +
                    '<p><strong>ongr/elasticsearch-dsl</strong><span>License</span><span>&nbsp;Origin: https://github.com/ongr-io/ElasticsearchDSL</span></p>' +
                    '<p><strong>egulias/email-validator</strong><span>MIT License</span><span>&nbsp;Origin: https://github.com/egulias/EmailValidator</span></p>' +
                    '<p><strong>Flysystem</strong><span>MIT License</span><span>&nbsp;Origin: http://flysystem.thephpleague.com</span></p>' +
                    '<p><strong>paragonie/random_compat</strong><span>MIT License</span><span>&nbsp;Origin: https://github.com/paragonie/random_compat</span></p>' +
                    '<p><strong>beberlei/assert</strong><span>License</span><span>&nbsp;Origin: https://github.com/beberlei/assert</span></p>' +
                "</p>"
        }]
    });

    // Add event listener method closes the about window
    Ext.getBody().on('click', function() {
        this.destroy();
    }, aboutWindow, {
        single: true
    });
};
//{/block}
