{block name="frontend_data_protection_information"}
    <p class="privacy-information">
        {if {config name=ACTDPRCHECK} && !$hideCheckbox}
            {block name="frontend_data_protection_information_checkbox"}
                {s name="PrivacyText" namespace="frontend/index/privacy" assign="snippetPrivacyText"}{/s}
                <input name="privacy-checkbox" type="checkbox" id="privacy-checkbox" required="required" aria-label="{$snippetPrivacyText|escape}" aria-required="true" value="1" class="is--required"{if $smarty.post['privacy-checkbox']} checked{/if} />
                <label for="privacy-checkbox">
                    {s name="PrivacyText" namespace="frontend/index/privacy"}{/s}
                </label>
            {/block}
        {elseif {config name=ACTDPRTEXT}}
            {block name="frontend_data_protection_information_text"}
                {s name="PrivacyText" namespace="frontend/index/privacy"}{/s}
            {/block}
        {/if}
    </p>
{/block}
