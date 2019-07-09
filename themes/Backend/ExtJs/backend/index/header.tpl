{extends file="backend/base/header.tpl"}

{* Page title *}
{block name='backend/base/header/title'}Shopware {$SHOPWARE_VERSION} {$SHOPWARE_VERSION_TEXT} (Rev. {$SHOPWARE_REVISION}) - Backend (c) shopware AG{/block}

{block name="backend/base/header/css" append}
<link rel="stylesheet" type="text/css" href="{link file="backend/_resources/styles/growl.css"}" />
<style type="text/css">
iframe { border: 0 none !important; width: 100%; height: 100%; }
#nav ul { top: 26px !important }
#header li.main { height: 28px !important }
.deprecated { color: #fff; font-size: 11px; font-weight: 700; text-align: center }
</style>
{/block}

{block name="backend/base/header/javascript" append}
<script type="text/javascript">

    var currentTabState = 'active';
    window.addEventListener('blur', function () {
        currentTabState = 'inactive';
    });

    window.addEventListener('focus', function () {
        currentTabState = 'active';
    });

    var userName = '{$user->name}',
        maxParameterLength = '{$maxParameterLength}';

    Ext.define('Shopware.app.Application', {
        extend: 'Ext.app.Application',
        name: 'Shopware',
        singleton: true,
        autoCreateViewport: false,
        requires: [ 'Shopware.container.Viewport' ],
        baseComponents: {
            'Shopware.container.Viewport': false,
            'Shopware.apps.Index.view.Menu': false,
            'Shopware.apps.Index.view.Footer': false
        },
        viewport: null,
        launch: function() {
            var me = this,
                preloader = Ext.create('Shopware.component.Preloader').bindEvents(Shopware.app.Application),
                errorReporter = Ext.create('Shopware.global.ErrorReporter').bindEvents(Shopware.app.Application);

            /**
             * Activates the Ext.fx.Anim class globally and
             * drives the animations our CSS 3 if supported.
             */
            Ext.enableFx = true;

            this.addEvents('baseComponentsReady', 'subAppLoaded');

            // Disable currency sign
            Ext.apply(Ext.util.Format, {
                currencySign: ''
            });
            // Fix default date format
            Ext.Date.defaultFormat = Ext.util.Format.dateFormat;

            this.callParent(arguments);
{if $user}
            this.addSubApplication({
                name: "Shopware.apps.{$app|escape}",
                controller: {$controller},
                params: {$params},
                localizedName: 'Shopware',
                firstRunWizardEnabled: {$firstRunWizardEnabled|intval},
                sbpLogin: {$sbpLogin},
                updateWizardStarted: {$updateWizardStarted|intval},
                enableInstallationFeedback: {$installationSurvey|intval},
                enableBetaFeedback: {$feedbackRequired|intval},
                biOverviewEnabled: {$biOverviewEnabled|intval},
                biIsActive: {$biIsActive|intval},
            });
{else}
            this.addSubApplication({
                name: "Shopware.apps.Login",
                localizedName: 'Login'
            });
{/if}

            // Start preloading the icon sets
            me.iconPreloader = Ext.create('Shopware.component.IconPreloader', {
                loadPath: "{link file='backend/_resources/resources/css' fullPath}"
            });
        },

        /**
         * Checks if all base components are loaded and rendered.
         * If truthy the preloader will be triggered.
         *
         * @param cmp - Component which calls the method
         * @return void
         */
        baseComponentIsReady: function(cmp) {
            var me = this,
                allReady = true;

            me.baseComponents[cmp.$className] = true;
            Ext.iterate(me.baseComponents, function(index, item) {
                if(!item) {
                    allReady = false;
                    return false;
                }
            });

            if(allReady) {
                window.setTimeout(function() {
                    me.fireEvent('baseComponentsReady', me);
                }, 1000);
            }
        }
    });

    /** Basic loader configuration  */
    Ext.Loader.setConfig({
        enabled: true,
        disableCaching: true,
        disableCachingParam: 'no-cache',
        disableCachingValue: '{timestamp}{if $user && $user->locale}+{$user->locale->getId()}+{$user->role->getId()}{/if}'
    });
    Ext.Loader.setPath('Shopware.apps', '{url module=backend action=index}', '?file=app');

    Ext.onReady(function() {
        var timeField = Ext.create('Ext.form.field.Time');
        this.timeFormat = timeField.format;
    });
</script>
{/block}
