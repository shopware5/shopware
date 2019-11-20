<?xml version="1.0" ?>
{block name="backend_index_doctype"}
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
{/block}
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="de">
    {block name='backend_index_header'}
    <head>
        {* Http-Tags *}
        {block name="backend_index_meta_http_tags"}
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
        {/block}

        {* Meta-Tags *}
        {block name='backend_index_meta_tags'}
        <meta name="robots" content="noindex,nofollow" />
        {/block}

        {* Page title *}
        <title>{block name='backend_index_header_title'}{s name='IndexTitle'}{/s}{/block}</title>

        {* Stylesheets and Javascripts *}
        {block name="backend_index_css_screen"}{/block}
        {block name="backend_index_css"}{/block}
        {block name="backend_index_javascript"}
        {block name="backend_index_javascript_inline"}{/block}
        {/block}
    </head>
    {/block}
    <body {block name="backend_index_body_attributes"}{/block}>
        {block name='backend_index_body_inline'}{/block}
    </body>
</html>
