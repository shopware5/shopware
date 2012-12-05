{extends file="backend/base/header.tpl"}

{* Page title *}
{block name='backend/base/header/title'}Shopware {Shopware::VERSION} {Shopware::VERSION_TEXT} (Rev. {Shopware::REVISION}) - Backend (c) 2012 shopware AG{/block}

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
    var userName = '{$user->name}';

    Ext.define('Shopware.app.Application', {
    	extend: 'Ext.app.Application',
    	name: 'Shopware',
    	singleton: true,
        autoCreateViewport: false,
        requires: [ 'Shopware.container.Viewport' ],
        viewport: null,
        launch: function() {

            /**
             * Activates the Ext.fx.Anim class globally and
             * drives the animations our CSS 3 if supported.
             */
            Ext.enableFx = true;

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
				params: {$params}
			});
{else}
            this.addSubApplication({
                name: "Shopware.apps.Login"
            });
{/if}
        }
    });
    Ext.Loader.setConfig({
		enabled: true,
		disableCaching: true,
		disableCachingParam: 'no-cache',
		disableCachingValue: '{time()}{if $user && $user->locale}+{$user->locale->getId()}+{$user->role->getId()}{/if}'
	});
    Ext.Loader.setPath('Shopware.apps', '{url module=backend action=index}', '?file=app');
</script>
{/block}