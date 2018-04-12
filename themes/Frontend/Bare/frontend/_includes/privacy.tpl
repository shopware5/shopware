{block name="frontend_data-protection-information"}
    <p class="privacy-information">
        {if {config name=ACTDPRCHECK}}
            <input name="privacy-checkbox" type="checkbox" id="privacy-checkbox" required="required" aria-required="true" value="1" class="is--required" />
            <label for="privacy-text">
        {/if}
        {s name="PrivacyText" namespace="frontend/index/privacy"}{/s}
        {if {config name=ACTDPRCHECK}}
            </label>
        {/if}
    </p>
{/block}