{extends file='widgets/jira/index/parent.tpl'}

{block name='frontend_index_parent_javascript' append}
<link rel="stylesheet" type="text/css" href="{link file='backend/_resources/resources/css/ext-all.css'}" />
<link rel="stylesheet" type="text/css" href="{link file='widgets/jira/view/edit/comments.css'}" />
<link rel="stylesheet" type="text/css" href="{link file='widgets/jira/view/edit/commits.css'}" />
{*<link rel="stylesheet" type="text/css" href="{link file='backend/_resources/resources/css/icon-set.css'}" />*}

<script type="text/javascript" src="{link file='../../engine/Library/ExtJs/ext-all.js'}"></script>
<script type="text/javascript" src="{link file='../../engine/Library/ExtJs/locale/ext-lang-de.js'}"></script>
<script type="text/javascript" src="{url module=backend controller=base action=index}?file=bootstrap"></script>
 {if $viewport != true}
<script type="text/javascript" src="{link file='widgets/jira/bootstrap.js'}"></script>
 {else}
 <script type="text/javascript" src="{link file='widgets/jira/bootstrap_viewport.js'}"></script>
 {/if}
{/block}

{block name='frontend_index_parent_css' append}

<style type="text/css">
    {if $viewport != true}
    div#content #center {
        width: 690px;
    }

    .block-message .success {
        text-shadow: 0;
    }
    .x-viewport, .x-viewport body, body {
      border: 0 none;
      background:none;
      height: 100%;
      margin: 0;
      padding: 0;
    }
    .x-form-item-label {
        text-align: left;
    }
    .x-form-item-label-right {
        float: right;
    }
    .notice {
        color: #5B626B;
        line-height: 22px;
    }
    #left *, #footer *, .wiki_nav *, .x-border-box, .x-border-box * {
        -moz-box-sizing: content-box;
        box-sizing: content-box;
        font-family: Arial,Geneva,Arial,Helvetica,sans-serif;
    }

    #content * {
        -moz-box-sizing: border-box;
        box-sizing: border-box;
    }

    label {
        height: 15px;
    }

    .x-boundlist {
        text-align: left;
    }

    body {
        overflow-y: scroll;
    }
    
    fieldset p {
        height: auto;
    }
    table {
        background-color: transparent !important;
    }

    {else}

    .block-message > div:first-child {
        color: #475C6A;
        font-size: 11.5px;
    }

    .x-viewport, .x-viewport body, body {
        background: none repeat scroll 0 0 transparent;
    }
    {/if}

    .x-tab button {
        color: #475C6A;
    }

    .sprite-pencil {
        background: url("data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABAAAAAQCAYAAAAf8/9hAAACCUlEQVR42oXTXUhTcRjH8ef5rw09TTJyjC5ChEgyLCy7CKQXdO2ignNzwso3nBcTAysxtFxUYzIjFYVhbQiLmqwosugFmRdDkSKWpdGNMLMcdiEkSQ0vgqfnHEoa43Qe+Nyd7+/icA4YXWkxQnc7gqIomhTirgXE4VnEQy8QjcOnIdX6QOG4zTZMfj9NIY49Q6w0CDMGtsuy3JxOp2nN7abVmhqKAjwxDDVBPBzw2u6qcSKRoGQySV6L5XkYYC+UFAJ46oAf1I8fBfAlffNT7ME+Lbbb7d6y3NyditkMEOkCGPGAYfxr2Uvzb0/SzUs5o1ardY8kScAyBnTjta9XaOHdaXoYMMVHg6ZKNcwa0It/LHbQ4mwt3esVE/cHRdXj2ybQGciOV+bPU+pDA4V8YvLODeGIDgjQG8iKl+eatXiwS0zdui4c4R4B/w6Y+QXyrQ9kxEsfXRzXU08bvurvRMfQNQF/B6pPCMg4HlAVR3txRI2/zNTS5/dn6GoLvu4+h0f7OhDUAdmh89mG2sEydAHOfk/FVxKxKvo0XU2dLnzjcaPT14rgrED47/W14IFYpDG+ujRByenwz9ZTOHmxAZwHy7XQ+C7XoU+NIwPKTNNxDB4pQxkAbGwL28w2sTwmsRxmYRuYiSEDajqGwYrd6MqTcL/6W7AStoMVsW1sKytg+WzjnyEzE78BiyEoyjHLSo0AAAAASUVORK5CYII=") no-repeat scroll 0 0 transparent !important;
    }

    [class*="sprite"] {
        height: 16px !important;
        width: 16px !important;
    }
</style>
{/block}

{block name='frontend_index_parent_content'}
    {if $ticket}
    <div id="ticket" style="display:none">{$ticket}</div>
    {/if}
    {if $version}
    <div id="version" style="display:none">{$version}</div>
    {/if}
    <div class="clearfix" id="content">
    {block name='frontend_jira_index_content'}{/block}
    </div>
{/block}