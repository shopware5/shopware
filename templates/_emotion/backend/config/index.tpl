<head>

{* Meta-Tags *}
{block name='backend/article/meta_tags'}
<meta charset="UTF-8" />
<meta name="robots" content="{s name=meta/robots}noindex,nofollow{/s}" />
{* Force the IE9 to always use the latest (e.g. bleeding egde) document and compability mode *}
<meta http-equiv="X-UA-Compatible" content="IE=Edge" />
{/block}

{* Page title *}
<title>{block name='backend/article/title'}{/block}</title>

{block name="backend/article/css"}
    <link rel="stylesheet" type="text/css" href="{link file='backend/_resources/resources/css/ext-all.css'}" />
    <link rel="stylesheet" type="text/css" href="{link file='backend/_resources/resources/css/icon-set.css'}" />
    <link rel="stylesheet" type="text/css" href="{link file='CodeMirror/lib/codemirror.css'}" />
    <link rel="stylesheet" type="text/css" href="{link file='CodeMirror/theme/monokai.css'}" />
<style>
    html, body {
        background: #ebedef !important;
    }
</style>
{/block}
{block name="backend/article/favicon"}
    <link rel="icon" href="{link file='backend/_resources/images/index/favicon.ico'}" type="image/x-icon" />
    <link rel="shortcut icon" href="{link file='backend/_resources/images/index/favicon.ico'}" type="image/x-icon" />
{/block}

{block name="backend/base/header/javascript"}
    <script type="text/javascript" src="{link file='ExtJs/ext-all-debug.js'}"></script>
    <script type="text/javascript" src="{link file="ExtJs/locale/ext-lang-{s name=script/ext/lang}en_GB{/s}.js"}"></script>
    <script type="text/javascript" src="{link file='TinyMce/tiny_mce.js'}"></script>
    <script type="text/javascript" src="{link file='CodeMirror/lib/codemirror.js'}"></script>
    <script type="text/javascript" src="{url controller=base action=index}?file=bootstrap"></script>
{/block}
</head>

{block name="backend/article/skeleton/javascript"}
<script type="text/javascript">

    /**
     * Client-side protection for path manipulation. Please comment out the following 3 lines to
     * use the ExtJS Page Analyzer.
     */
//	if (self != top) {
//    	parent.location.href=self.location.href;
//	}

    Ext.define('Shopware.app.Application', {
    	extend: 'Ext.app.Application',
    	name: 'Shopware',
    	singleton: true,
        autoCreateViewport: false,
        launch: function() {
            // Disable all fx effects (sliding, fading, etc...)
            Ext.enableFx = false;
            this.callParent(arguments);
			this.addSubApplication({
				name: "Shopware.apps.Config",
				mode: 'iframe-mode'
			});
        }
    });
    Ext.Loader.setConfig({
		enabled: true,
		disableCaching: true,
		disableCachingParam: 'no-cache',
		disableCachingValue: '{time()}{if $user}+{$user->locale->getId()}+{$user->role->getId()}{/if}'
	});
    Ext.Loader.setPath('Shopware.apps', '{url module=backend action=index}', '?file=app');
</script>
{/block}