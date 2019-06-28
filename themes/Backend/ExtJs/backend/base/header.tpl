{namespace name=backend/base/index}
<head>

{* Meta-Tags *}
{block name='backend/base/header/meta_tags'}
<meta charset="UTF-8" />
<meta name="robots" content="{s name=meta/robots}noindex,nofollow{/s}" />
{* Force the IE9 to always use the latest (e.g. bleeding egde) document and compability mode *}
<meta http-equiv="X-UA-Compatible" content="IE=Edge" />
{/block}

{* Page title *}
<title>{block name='backend/base/header/title'}{/block}</title>

{block name="backend/base/header/css"}
    <link rel="stylesheet" type="text/css" href="{link file='backend/_resources/resources/css/ext-all.css'}?{$SHOPWARE_REVISION}" />
    <link rel="stylesheet" type="text/css" href="{link file='backend/_resources/resources/css/core-icon-set.css'}?{$SHOPWARE_REVISION}" />
    <link rel="stylesheet" type="text/css" href="{link file='backend/_resources/resources/css/core-icon-set-new.css'}?{$SHOPWARE_REVISION}" />
{/block}
{block name="backend/base/header/favicon"}
    <link rel="icon" href="{link file='backend/_resources/images/index/favicon.ico'}" type="image/x-icon" />
    <link rel="shortcut icon" href="{link file='backend/_resources/images/index/favicon.ico'}" type="image/x-icon" />
{/block}

{block name="backend/base/header/javascript"}
    {if $extJsDeveloperModeActive}
        <script type="text/javascript" src="{link file='ExtJs/ext-all-dev.js'}?{$SHOPWARE_REVISION}"></script>
    {else}
        <script type="text/javascript" src="{link file='ExtJs/ext-all.js'}?{$SHOPWARE_REVISION}"></script>
    {/if}
    <script type="text/javascript" src="{link file="ExtJs/locale/ext-lang-{s name=script/ext/lang}en_GB{/s}.js"}?{$SHOPWARE_REVISION}"></script>
    <script type="text/javascript" src="{link file='TinyMce/tiny_mce.js'}?{$SHOPWARE_REVISION}"></script>
    <script type="text/javascript" src="{link file='backend/_resources/ace_editor/ace.js'}?{$SHOPWARE_REVISION}"></script>
    <script type="text/javascript" src="{link file='backend/_resources/ace_editor/ext-language_tools.js'}?{$SHOPWARE_REVISION}"></script>

    {* We need to put the language in there, due to the caching of the bootstrap.js *}
    <script type="text/javascript">
        {* Ext.editorLang is no longer used and is deprecated *}
        Ext.editorLang = '{s name=script/ext/lang}{/s}';
        Ext.shopwareRevision = '{$SHOPWARE_REVISION}';
        Ext.userLanguage = '{s name=script/ext/lang}{/s}';
    </script>

    {if $user}
        <script type="text/javascript" src="{url controller=base action=index}?file=bootstrap&loggedIn={$smarty.now}"></script>
    {else}
        <script type="text/javascript" src="{url controller=base action=index}?file=bootstrap&{$SHOPWARE_REVISION}"></script>
    {/if}
{/block}
</head>
