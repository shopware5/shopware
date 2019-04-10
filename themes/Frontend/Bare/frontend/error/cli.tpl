{if $exception}
ERROR:
{$exception->getMessage()} in {$error_file|escape} on line {$exception->getLine()}

TRACE:
{$error_trace}
{else}
    {se name="InformText"}Wir wurden bereits über das Problem informiert und arbeiten an einer Lösung, bitte versuchen Sie es in Kürze erneut.{/se}
{/if}
