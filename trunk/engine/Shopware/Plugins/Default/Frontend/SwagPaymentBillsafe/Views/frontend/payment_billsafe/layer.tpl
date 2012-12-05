{block name="frontend_index_header_javascript" append}
{if $sUserData.additional.payment.name == 'billsafe_invoice'}
<script type="text/javascript" src="https://content.billsafe.de/lpg/js/client.js"></script>
<script type="text/javascript">
//<![CDATA[
jQuery(document).ready(function() {
	var formElement = document.getElementById('basketButton').form;
	var lpg = new BillSAFE.LPG.client({
		form: formElement,
		conditions: [{ element: 'sAGB', value: 'on' }],
		sandbox: {if $BillsafeConfig->debug}true{else}false{/if}
	});
});
//]]>
</script>
{/if}
{/block}