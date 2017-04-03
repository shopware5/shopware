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
    <link rel="stylesheet" type="text/css" href="{link file='backend/_resources/resources/css/ext-all.css'}?{Shopware::REVISION}" />
    <link rel="stylesheet" type="text/css" href="{link file='backend/_resources/resources/css/core-icon-set.css'}?{Shopware::REVISION}" />
    <link rel="stylesheet" type="text/css" href="{link file='backend/_resources/resources/css/core-icon-set-new.css'}?{Shopware::REVISION}" />
    <link rel="stylesheet" type="text/css" href="{link file='CodeMirror/lib/codemirror.css'}?{Shopware::REVISION}" />
{/block}
{block name="backend/base/header/favicon"}
    <link rel="icon" href="{link file='backend/_resources/images/index/favicon.ico'}" type="image/x-icon" />
    <link rel="shortcut icon" href="{link file='backend/_resources/images/index/favicon.ico'}" type="image/x-icon" />
{/block}

{block name="backend/base/header/javascript"}
    <script type="text/javascript" src="{link file='ExtJs/ext-all.js'}?{Shopware::REVISION}"></script>
    <script type="text/javascript" src="{link file="ExtJs/locale/ext-lang-{s name=script/ext/lang}en_GB{/s}.js"}?{Shopware::REVISION}"></script>
    <script type="text/javascript" src="{link file='TinyMce/tiny_mce.js'}?{Shopware::REVISION}"></script>
    <script type="text/javascript" src="{link file='CodeMirror/lib/codemirror.js'}?{Shopware::REVISION}"></script>

    {* We need to put the language in there, due to the caching of the bootstrap.js *}
    <script type="text/javascript">
        {* Ext.editorLang is no longer used and is deprecated *}
        Ext.editorLang = '{s name=script/ext/lang}{/s}';
        Ext.shopwareRevision = '{Shopware::REVISION}';
        Ext.userLanguage = '{s name=script/ext/lang}{/s}';
    </script>

    {if $user}
        <script type="text/javascript" src="{url controller=base action=index}?file=bootstrap&loggedIn={$smarty.now}"></script>
    {else}
        <script type="text/javascript" src="{url controller=base action=index}?file=bootstrap&{Shopware::REVISION}"></script>
    {/if}


    <style>
        .x-customer-grid .x-grid-cell-inner {
            line-height: 16px !important;
        }
        .stream-name-column {
            line-height: 20px !important;
            font-size: 12px;
            color: #35404B;
        }

        .customer-filter-panel  {
            border-right: 1px solid #a4b5c0;
        }
        .customer-filter-panel .x-panel-header {
            border-right: none;
            border-left: none;
        }

        .x-customer-grid {
            border: none;
        }

        .customer-filter-panel .x-panel-body {
            border-right: none;
            border-left: none;
            border-bottom: 1px solid #a4b5c0;
            padding: 0;
        }
        .stream-listing .x-grid-body {
            border: none;
        }
        .stream-listing {
            border: none;
        }

        .stream-inline {

        }

    </style>
{/block}
</head>
