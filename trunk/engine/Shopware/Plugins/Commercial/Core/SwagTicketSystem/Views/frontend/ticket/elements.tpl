{extends file='frontend/forms/elements.tpl'}

{block name='frontend_forms_elements'}
<form id="support" name="{$sSupport.name}" class="{$sSupport.class}" method="post" action="{url controller='ticket' action='request' id=$id}" enctype="multipart/form-data">
{/block}