{namespace name='backend/first_run_wizard/main'}

{block name='first_run_wizard_paypal_start'}
<!doctype html>
<html lang="{s name='pay_pal/start/lang'}{/s}">
<head>
    <title>{s name='pay_pal/start/paypal'}{/s} - {s name='pay_pal/start/headline'}{/s}</title>
    <meta charset="utf-8">
    <style>
        body {
            padding: 0;
            margin: 0;
            font-family: "Helvetica Neue", Helvetica, Arial, sans-serif;
            font-size: .75rem;
        }

        .wrapper {
            box-sizing: border-box;
            background: #fff;
            padding: 1em;
            border: 1px solid #8ca0c1;
        }

        .headline {
            font-style: italic;
            margin-top: 0;
        }

        img.logo {
            margin-bottom: 1em;
        }

        .paragraph {
            margin-bottom: 1em;
        }

        table {
            font-size: 1em;
        }

        table td::before {
            font-weight: bold;
            font-size: 1.5em;
            line-height: .5em;
            margin-right: .1em;
            content: '✔';
        }

        table td:last-child {
            padding-left: 1em;
        }

        .footnote {
            margin: 0;
            font-size: .66em;
        }

        .super {
            vertical-align: super;
            font-size: .75em;
        }

        footer {
            margin-top: 3em;
            text-align: right;
        }
    </style>
</head>
<body>
    <div class="wrapper">
        <p class="headline">{s name='pay_pal/start/headline'}{/s}</p>
        <img class="logo" src="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAMgAAAAzCAYAAADSDUdEAAAFP2lUWHRYTUw6Y29tLmFkb2JlLnhtcAAAAAAAPD94cGFja2V0IGJlZ2luPSLvu78iIGlkPSJXNU0wTXBDZWhpSHpyZVN6TlRjemtjOWQiPz4KPHg6eG1wbWV0YSB4bWxuczp4PSJhZG9iZTpuczptZXRhLyIgeDp4bXB0az0iQWRvYmUgWE1QIENvcmUgNS41LWMwMTIgMS4xNDk2MDIsIDIwMTIvMTAvMTAtMTg6MTA6MjQgICAgICAgICI+CiA8cmRmOlJERiB4bWxuczpyZGY9Imh0dHA6Ly93d3cudzMub3JnLzE5OTkvMDIvMjItcmRmLXN5bnRheC1ucyMiPgogIDxyZGY6RGVzY3JpcHRpb24gcmRmOmFib3V0PSIiCiAgICB4bWxuczpkYz0iaHR0cDovL3B1cmwub3JnL2RjL2VsZW1lbnRzLzEuMS8iCiAgICB4bWxuczpkYW09Imh0dHA6Ly93d3cuZGF5LmNvbS9kYW0vMS4wIgogICAgeG1sbnM6dGlmZj0iaHR0cDovL25zLmFkb2JlLmNvbS90aWZmLzEuMC8iCiAgICB4bWxuczpQYXlQYWw9Ind3dy5wYXlwYWwuY29tL2Jhc2UvdjEiCiAgIGRjOmZvcm1hdD0iaW1hZ2UvcG5nIgogICBkYzptb2RpZmllZD0iMjAxNC0wNy0wOVQxMDozNzo1NC40NDgtMDc6MDAiCiAgIGRhbTpzaXplPSIzMzg4IgogICBkYW06UGh5c2ljYWx3aWR0aGluaW5jaGVzPSItMS4wIgogICBkYW06ZXh0cmFjdGVkPSIyMDE0LTA3LTA5VDEwOjM3OjQ3LjU3My0wNzowMCIKICAgZGFtOnNoYTE9IjU5YjFlN2Q1YzJlZjEwYjYzOWM1ZmE1YzcxMzVkZWY4M2I4ZmZkMmQiCiAgIGRhbTpOdW1iZXJvZnRleHR1YWxjb21tZW50cz0iMCIKICAgZGFtOkZpbGVmb3JtYXQ9IlBORyIKICAgZGFtOlByb2dyZXNzaXZlPSJubyIKICAgZGFtOlBoeXNpY2FsaGVpZ2h0aW5kcGk9Ii0xIgogICBkYW06TUlNRXR5cGU9ImltYWdlL3BuZyIKICAgZGFtOk51bWJlcm9maW1hZ2Vz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" alt="PayPal Logo" />
        <p class="paragraph">
            {s name='pay_pal/start/main_paragraph'}{/s}
        </p>
        <p class="paragraph">
            {s name='pay_pal/start/sub_paragraph'}{/s}
        </p>
        <table>
            <tr>
                <td>{s name='pay_pal/start/paypal'}{/s}</td>
                <td>{s name='pay_pal/start/credit_card'}{/s}</td>
            </tr>
            <tr>
                <td>{s name='pay_pal/start/direct_debit'}{/s}</td>
                <td>{s name='pay_pal/start/pay_upon_invoice'}{/s}</td>
            </tr>
        </table>
        <footer>
            <p class="footnote">{s name='pay_pal/start/footnote_ecc_study'}{/s}</p>
            <p class="footnote">{s name='pay_pal/start/footnote_risk_assessment'}{/s}</p>
        </footer>
    </div>
</body>
</html>
{/block}
