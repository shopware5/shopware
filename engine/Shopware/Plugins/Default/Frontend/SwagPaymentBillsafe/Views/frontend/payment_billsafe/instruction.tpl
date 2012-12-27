{$instruction = $BillsafePaymentInstruction}
<style type="text/css">
.payment_instruction, .payment_instruction td, .payment_instruction tr {
	margin: 0;
	padding: 0;
	border: 0;
	font-size:8px;
	font: inherit;
	vertical-align: baseline;
}
.payment_note {
	font-size: 10px;
	color: #333;
}
</style>
<div class="payment_note">
<br/>
{$instruction->legalNote}<br/>
{$instruction->note}<br/><br/>
</div>
<table class="payment_instruction">
<tr>
	<td>Empfänger:</td>
	<td>{$instruction->recipient}</td>
</tr>
<tr>
	<td>Kontonr.:</td>
	<td>{$instruction->accountNumber}</td>
</tr>
<tr>
	<td>BLZ:</td>
	<td>{$instruction->bankCode}</td>
</tr>
<tr>
	<td>Bank:</td>
	<td>{$instruction->bankName}</td>
</tr>
<tr>
	<td>Betrag:</td>
	<td>{$instruction->amount|currency}</td>
</tr>
<tr>
	<td>Verwendungszweck 1:</td>
	<td>{$instruction->reference}</td>
</tr>
<tr>
	<td>Verwendungszweck 2:</td>
	<td>{config name=host}</td>
</tr>
</table>
