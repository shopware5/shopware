{extends file='backend/index/parent.tpl'}
{block name='frontend_index_header_title' prepend}{s name="ErrorIndexTitle"}{/s}{/block}
{block name='backend_index_body_inline'}
<div id="center" class="grid_13">{include file='frontend/error/exception.tpl'}</div>
{/block}