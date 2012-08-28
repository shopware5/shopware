{block name="frontend_index_header_css_print" append}
<link type="text/css" media="screen, projection" rel="stylesheet" href="{link file='engine/Shopware/Plugins/Default/Frontend/PigmbhKlarnaPayment/css/klarnastyles.css' fullPath}" />
{/block}
{if $KlarnaJS}
	{block name="frontend_index_header_javascript" append}
		{$serverport='https'}
		{if $pi_klarna_active && $pi_klarna_rate_active}
		    <script type="text/javascript" src="{$serverport}://static.klarna.com/external/js/klarnainvoice.js"></script>
		    <script type="text/javascript" src="{$serverport}://static.klarna.com/external/js/klarnapart.js"></script>
		    <script type="text/javascript" >
		        addKlarnaInvoiceEvent(function(){
		             InitKlarnaInvoiceElements('klarna_invoice', {$pi_klarna_shopid}, '{$piKlarnaShopLang}','{$pi_klarna_surcharge}');
		        });
		        {if $piKlarnaArticles || $piKlarnaOffers}
			        {foreach from=$pi_klarna_counter item=aktuelle_id}
			            addKlarnaPartPaymentEvent(function(){
			                InitKlarnaPartPaymentElements('klarna_partpayment{$aktuelle_id}', {$pi_klarna_shopid}, '{$piKlarnaShopLang}');
			            });
			        {/foreach}
			    {else}
				    addKlarnaPartPaymentEvent(function(){
		                InitKlarnaPartPaymentElements('klarna_partpayment', {$pi_klarna_shopid}, '{$piKlarnaShopLang}');
		            });
				{/if}
		    </script>
		{elseif $pi_klarna_active && !$pi_klarna_rate_active}
		    <script type="text/javascript" src="{$serverport}://static.klarna.com/external/js/klarnainvoice.js"></script>
		    <script type="text/javascript" >
		            addKlarnaInvoiceEvent(function(){
		                    InitKlarnaInvoiceElements('klarna_invoice', {$pi_klarna_shopid}, '{$piKlarnaShopLang}','{$pi_klarna_surcharge}');
		            });
		    </script>
		{elseif !$pi_klarna_active && $pi_klarna_rate_active}
		    <script type="text/javascript" src="{$serverport}://static.klarna.com/external/js/klarnapart.js"></script>
		    <script type="text/javascript" >
		       {if $piKlarnaArticles || $piKlarnaOffers}
	               {foreach from=$pi_klarna_counter item=aktuelle_id}
		              addKlarnaPartPaymentEvent(function(){
		                       InitKlarnaPartPaymentElements('klarna_partpayment{$aktuelle_id}', {$pi_klarna_shopid}, '{$piKlarnaShopLang}');
		             });
		  	       {/foreach}
  	    	   {else}
			       addKlarnaPartPaymentEvent(function(){
	                   InitKlarnaPartPaymentElements('klarna_partpayment', {$pi_klarna_shopid}, '{$piKlarnaShopLang}');
	               });
		       {/if}
		    </script>
		{/if}
	{/block}
{/if}
