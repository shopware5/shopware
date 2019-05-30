<!DOCTYPE html>
<html lang="en">
<head>
    {block name="benchmark_index_head"}
        <meta charset="UTF-8">
        <title>{block name="benchmark_index_title"}{/block}</title>

        <link href="{link file="backend/benchmark/template/local/all.css"}" media="all" rel="stylesheet" type="text/css" />
        <link href="{link file="backend/benchmark/template/local/vendor/components/LiquidButton/css/style.css"}" media="all" rel="stylesheet" type="text/css" />

        <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.2.1/jquery.min.js"
                integrity="sha384-xBuQ/xzmlsLoJpyjoggmTEz8OWUFM0/RC5BsqQBDX2v5cMvDHcMakNTNrHIW2I5f"
                crossorigin="anonymous"></script>

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
            <script src="https://cdnjs.cloudflare.com/ajax/libs/vue/2.5.22/vue.min.js"
                    integrity="sha384-/akamCRnflySW3CYATb1tG4cZUVrkVT2+s6Q150JJNJ4mv8nhvL/V7rPgmzX8scM"
                    crossorigin="anonymous"></script>
            <script src="https://cdnjs.cloudflare.com/ajax/libs/vue-i18n/8.10.0/vue-i18n.min.js"
                    integrity="sha384-gobPWY9nduovPXtrtPx0f3yAWM2dnKwEOy8BSxadNjhCECNk1TZTzGJqpnWHQGIV"
                    crossorigin="anonymous"></script>
            <script src="https://cdnjs.cloudflare.com/ajax/libs/d3/3.5.5/d3.min.js"
                    integrity="sha384-EU1dURc8oJ/Ec2n9odZegm1iva+lUs6tS2kIQyRWJc5KjzRFvTBuix+qvrbLSQYj"
                    crossorigin="anonymous"></script>
            <script src="{link file='backend/benchmark/template/local/js/translation.js'}"></script>
            <script src="{link file='backend/benchmark/template/local/js/vue_wrapper.js'}"></script>
        {/block}
    {/block}
</head>

{block name="benchmark_index_body"}{/block}

</html>
