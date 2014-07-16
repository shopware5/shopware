{extends file="backend/base/index.tpl"}

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

			this.addSubApplication({
				name: "Shopware.apps.Config"
			});
        }
    });
    Ext.Loader.setConfig({
		enabled: true,
		disableCaching: true,
		disableCachingParam: 'no-cache',
		disableCachingValue: '{timestamp}{if $user && $user->locale}+{$user->locale->getId()}+{$user->role->getId()}{/if}'
	});
    Ext.Loader.setPath('Shopware.apps', '{url module=backend action=index}', '?file=app');
</script>
{/block}
