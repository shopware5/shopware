<!DOCTYPE html>
<html lang="en">
<head>
    {block name="benchmark_index_head"}
        <meta charset="UTF-8">
        <title>{block name="benchmark_index_title"}{/block}</title>

        <link href="{link file="backend/benchmark/template/local/all.css"}" media="all" rel="stylesheet" type="text/css" />
        <link href="{link file="backend/benchmark/template/local/vendor/components/LiquidButton/css/style.css"}" media="all" rel="stylesheet" type="text/css" />

        <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.2.1/jquery.min.js"></script>

        <script type="text/javascript">
            var onClickLiquidButton = function () {
                $('html, body').animate({
                    scrollTop: $(".special-note").offset().top
                }, 800);
            };

            (function () {
                {if $benchmarkData}
                    window.benchmarkData = JSON.parse('{$benchmarkData}');
                {/if}

                {if $benchmarkTranslations}
                    window.benchmarkTranslations = JSON.parse('{$benchmarkTranslations}');
                {/if}

                {if $benchmarkDefaultLanguage}
                    window.benchmarkDefaultLanguage = '{$benchmarkDefaultLanguage}';
                {/if}
            } )();
        </script>

        {block name="benchmark_index_head_scripts"}
            <script src="https://cdn.jsdelivr.net/npm/vue@2.5.16"></script>
            <script src="https://unpkg.com/vue-i18n/dist/vue-i18n.js"></script>
            <script src="https://cdnjs.cloudflare.com/ajax/libs/d3/3.5.5/d3.min.js"></script>
            <script src="{link file='backend/benchmark/template/local/js/translation.js'}"></script>
            <script src="{link file='backend/benchmark/template/local/js/vue_wrapper.js'}"></script>
        {/block}
    {/block}
</head>

{block name="benchmark_index_body"}{/block}

</html>
