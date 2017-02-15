{extends file="backend/index/parent.tpl"}

{block name="backend_index_css"}
<link rel="icon" href="{link file='backend/_resources/images/index/favicon.ico'}" type="image/x-icon">
<link rel="shortcut icon" href="{link file='backend/_resources/images/index/favicon.ico'}" type="image/x-icon"> 
{* <link rel="stylesheet" type="text/css" href="{link file='engine/backend/css/icons.css'}" /> *}
<link rel="stylesheet" type="text/css" href="{link file='ExtJs/resources/css/ext-all.css'}" />
<link rel="stylesheet" type="text/css" href="{link file='backend/_resources/resources/css/core-icon-set.css'}" />
{/block}

{block name="backend_index_javascript"}
<script type="text/javascript" src="{link file='ExtJs/ext-all.js'}" charset="utf-8"></script>
<script type="text/javascript" src="{link file="ExtJs/locale/ext-lang-de.js"}" charset="utf-8"></script>
<script type="text/javascript">
//<![CDATA[
Ext.Loader.setConfig({
    enabled: true,
    paths: {
        'Shopware': '{url action=load}'
    },
    suffixes: {
        'Shopware': ''
    },
    disableCaching: false
});

Ext.Loader.getPath = function(className) {
    var tempClass = className;
    var path = '',
    paths = this.config.paths,
    prefix = this.getPrefix(className);
    suffix = this.config.suffixes[prefix] !== undefined ? this.config.suffixes[prefix] : '';

    if (prefix.length > 0) {
        if (prefix === className) {
            return paths[prefix];
        }
        path = paths[prefix];
        className = className.substring(prefix.length + 1);
    }

    if (path.length > 0) {
        path += '?file=';
    }

    return path.replace(/\/\.\//g, '/') + className.replace(/\./g, "/") + suffix;
};

{block name="backend_index_javascript_inline"}{/block}
//]]>
</script>
{/block}