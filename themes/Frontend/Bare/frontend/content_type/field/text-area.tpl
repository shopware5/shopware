{extends file="frontend/content_type/field/base.tpl"}

{block name='frontend_content_type_field_base_content'}
    <div class="panel">

        {block name='frontend_content_type_field_textarea_panel_head'}
            <h3 class="panel--title is--underline">{$detail.label}</h3>
        {/block}

        {block name='frontend_content_type_field_textarea_panel_body'}
            <div class="panel--body is--wide">
                <p>
                    {$content|escape}
                </p>
            </div>
        {/block}

    </div>
{/block}
