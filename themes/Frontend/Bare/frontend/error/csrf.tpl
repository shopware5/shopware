<h2>{s name="ExceptionHeaderCsrf"}Invalid form token!{/s}</h2>

<p>{s name="CsrfExceptionReason"}
        The action could not be completed due to a missing form token.
        <br>
        A new form token has been generated.
    {/s}</p>
<p class="is--bold">
    {s name="CsrfExceptionHelp"}
        Please go back in your browser and try the action again.
    {/s}
</p>

{if $backUrl}
    <p><a href="{$backUrl}">{s name="CsrfExceptionBack"}Go back to the previous page{/s}</a></p>
{/if}