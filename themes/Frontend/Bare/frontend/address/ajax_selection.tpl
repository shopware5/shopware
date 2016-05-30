{block name='frontend_address_selection_modal'}
    <div class="panel">

        {block name="frontend_address_selection_modal_title"}
            <div class="panel--title is--underline">{s name="ModalTitle"}{/s}</div>
        {/block}

        {block name="frontend_address_selection_modal_body"}
            <div class="panel--body is--wide">

                {block name="frontend_address_selection_modal_create_text"}
                <p>
                    {s name="CreateNewAddressText"}{/s}
                    <a href="{url controller=address action=create}"
                       title="{s name="CreateNewAddressTitle"}{/s}"
                       data-address-editor="true"
                       data-showSelectionOnClose="true">{s name="CreateNewAddressLinkText"}{/s}</a>.
                </p>
                {/block}

                {if count($addresses) > 0}
                    {block name="frontend_address_selection_modal_container"}
                        <div class="modal--container" data-panel-auto-resizer="true">
                            {foreach $addresses as $address}
                                {block name='frontend_address_selection_modal_container_item'}
                                    <div class="modal--container-item">
                                        <div class="panel has--border is--rounded block">
                                            {block name='frontend_address_selection_modal_container_item_body'}
                                                <div class="panel--body is--wide">
                                                    <span class="is--bold">{$address.firstname} {$address.lastname}</span><br />
                                                    {if $address.company}<span class="is--bold">{$address.company}</span><br/>{/if}
                                                    {$address.street}<br />
                                                    {if $address.additionalAddressLine1}{$address.additionalAddressLine1}<br />{/if}
                                                    {if $address.additionalAddressLine2}{$address.additionalAddressLine2}<br />{/if}
                                                    {if {config name=showZipBeforeCity}}{$address.zipcode} {$address.city}{else}{$address.city} {$address.zipcode}{/if}<br />
                                                    {$address.country.name}
                                                </div>
                                            {/block}

                                            {block name='frontend_address_selection_modal_container_item_actions'}
                                                <div class="panel--actions">
                                                    <form class="address-manager--selection-form" action="{url controller=address action=handleExtra}" method="post">
                                                        <input type="hidden" name="id" value="{$address.id}" />

                                                        {block name="frontend_address_selection_modal_container_item_extra_data"}
                                                            {foreach $extraData as $key => $val}
                                                                <input type="hidden" name="extraData[{$key}]" value="{$val}" />
                                                            {/foreach}
                                                        {/block}

                                                        {block name="frontend_address_selection_modal_container_item_select_button"}
                                                            <button class="btn is--block is--primary is--icon-right"
                                                                    type="submit"
                                                                    data-checkFormIsValid="false"
                                                                    data-preloader-button="true">
                                                                {s name="SelectAddressButton"}Use this address{/s}
                                                                <span class="icon--arrow-right"></span>
                                                            </button>
                                                        {/block}
                                                    </form>
                                                </div>
                                            {/block}
                                        </div>
                                    </div>
                                {/block}
                            {/foreach}
                        </div>
                    {/block}
                {else}
                    {block name="frontend_address_select_address_modal_empty_addresses"}
                        {include file='frontend/_includes/messages.tpl' type="info" content="{s name='EmptyAddressesText'}{/s}"}
                    {/block}
                {/if}
            </div>
        {/block}
    </div>
{/block}