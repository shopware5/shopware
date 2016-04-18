{block name="frontend_address_editor_modal"}
    <div data-register="true" class="panel">
        {block name="frontend_address_editor_modal_title"}
            <div class="panel--title is--underline">{s name="AddressesTitleEdit" namespace="frontend/address/index"}{/s}</div>
        {/block}
        {block name="frontend_address_editor_modal_body"}
            <div class="panel--body address-editor--body">
                {block name="frontend_address_editor_modal_form"}
                    <form name="frmAddresses" method="post" action="{url controller=address action=ajaxSave}">
                        {include file="frontend/address/ajax_form.tpl"}

                        {block name="frontend_address_editor_modal_extra_data"}
                            {foreach $extraData as $key => $val}
                                <input type="hidden" name="extraData[{$key}]" value="{$val}" />
                            {/foreach}
                        {/block}
                    </form>
                {/block}
            </div>
        {/block}
    </div>
{/block}