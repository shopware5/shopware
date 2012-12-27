<?php if (!defined('isHOP')) die();?>
<div class="toggler">
	<div id="effect" class="ui-widget-content ui-corner-all">
  		<div id="accordion" style="border: 0px;">

        <h3 id="tabshead-0"><a href="#"><?php echo x('Refund')?></a></h3>
        <div id="tabs-1">
          <form id="form_refund" method="post" action="<?php echo $queryform?>">
            <input type="hidden" name="refund[currency]" value="<?php echo $booking['CLEARING_CURRENCY']?>">
            <input type="hidden" name="refund[paymethod]" value="<?php echo $booking['CODES']['method']?>">
            <input type="hidden" name="refund[uniqueid]" value="<?php echo $booking['IDENTIFICATION_UNIQUEID']?>">
            <input type="hidden" name="submit_action[refund]" value="Send">
          <table>
            <tr><td><?php echo x('Amount')?>:</td><td><input type="text" name="refund[amount]" value="<?php echo $booking['CLEARING_AMOUNT']?>" size="50"> <?php echo $booking['CLEARING_CURRENCY']?></td></tr>
            <tr><td><?php echo x('Usage')?>:</td><td><input type="text" name="refund[usage]" value="<?php echo $booking['CLEARING_DESCRIPTOR']?>" size="50"></td></tr>
            <tr><td><?php echo x('TXN-ID')?>:</td><td><input type="text" name="refund[txnid]" value="<?php echo $booking['IDENTIFICATION_TRANSACTIONID']?>" size="50"></td></tr>
            <tr><td><?php echo x('Date')?>:</td><td><input type="text" name="refund[date]" value="<?php echo $booking['CLEARING_DATE']?>" size="50" readonly class="readonly"></td></tr>
            <tr><td><?php echo x('Comment')?>:</td><td><textarea name="refund[comment]" cols="47" rows="3"></textarea></td></tr>
          </table>
          <input type="submit" id="submit_refund" name="submit_action[refund]" value="<?php echo x('Send')?>">
          </form>
        </div>

        <h3 id="tabshead-1"><a href="#"><?php echo x('Rebill')?></a></h3>
        <div id="tabs-2">
          <form id="form_rebill" method="post" action="<?php echo $queryform?>">
            <input type="hidden" name="rebill[currency]" value="<?php echo $booking['CLEARING_CURRENCY']?>">
            <input type="hidden" name="rebill[paymethod]" value="<?php echo $booking['CODES']['method']?>">
            <input type="hidden" name="rebill[uniqueid]" value="<?php echo $booking['IDENTIFICATION_UNIQUEID']?>">
            <input type="hidden" name="submit_action[rebill]" value="Send">
          <table>
            <tr><td><?php echo x('Amount')?>:</td><td><input type="text" name="rebill[amount]" value="<?php echo $booking['CLEARING_AMOUNT']?>" size="50"> <?php echo $booking['CLEARING_CURRENCY']?></td></tr>
            <tr><td><?php echo x('Usage')?>:</td><td><input type="text" name="rebill[usage]" value="<?php echo $booking['CLEARING_DESCRIPTOR']?>" size="50"></td></tr>
            <tr><td><?php echo x('TXN-ID')?>:</td><td><input type="text" name="rebill[txnid]" value="<?php echo $booking['IDENTIFICATION_TRANSACTIONID']?>" size="50"></td></tr>
            <tr><td><?php echo x('Date')?>:</td><td><input type="text" name="rebill[date]" value="<?php echo $booking['CLEARING_DATE']?>" size="50" readonly class="readonly"></td></tr>
            <tr><td><?php echo x('Comment')?>:</td><td><textarea name="rebill[comment]" cols="47" rows="3"></textarea></td></tr>
          </table>
          <input type="submit" id="submit_rebill" name="submit_action[rebill]" value="<?php echo x('Send')?>">
          </form>
        </div>

        <h3 id="tabshead-2"><a href="#"><?php echo x('Capture')?></a></h3>
        <div id="tabs-3">
          <form id="form_capture" method="post" action="<?php echo $queryform?>">
            <input type="hidden" name="capture[currency]" value="<?php echo $booking['CLEARING_CURRENCY']?>">
            <input type="hidden" name="capture[paymethod]" value="<?php echo $booking['CODES']['method']?>">
            <input type="hidden" name="capture[uniqueid]" value="<?php echo $booking['IDENTIFICATION_UNIQUEID']?>">
            <input type="hidden" name="submit_action[capture]" value="Send">
          <table>
            <tr><td><?php echo x('Amount')?>:</td><td><input type="text" name="capture[amount]" value="<?php echo $booking['CLEARING_AMOUNT']?>" size="50"> <?php echo $booking['CLEARING_CURRENCY']?></td></tr>
            <tr><td><?php echo x('Usage')?>:</td><td><input type="text" name="capture[usage]" value="<?php echo $booking['CLEARING_DESCRIPTOR']?>" size="50"></td></tr>
            <tr><td><?php echo x('TXN-ID')?>:</td><td><input type="text" name="capture[txnid]" value="<?php echo $booking['IDENTIFICATION_TRANSACTIONID']?>" size="50"></td></tr>
            <tr><td><?php echo x('Date')?>:</td><td><input type="text" name="capture[date]" value="<?php echo $booking['CLEARING_DATE']?>" size="50" readonly class="readonly"></td></tr>
            <tr><td><?php echo x('Comment')?>:</td><td><textarea name="capture[comment]" cols="47" rows="3"></textarea></td></tr>
          </table>
          <input type="submit" id="submit_capture" name="submit_action[capture]" value="<?php echo x('Send')?>">
          </form>
        </div>

        <h3 id="tabshead-3"><a href="#"><?php echo x('Reservation')?></a></h3>
        <div id="tabs-4">
          <form id="form_reservation" method="post" action="<?php echo $queryform?>">
            <input type="hidden" name="reservation[currency]" value="<?php echo $booking['CLEARING_CURRENCY']?>">
            <input type="hidden" name="reservation[paymethod]" value="<?php echo $booking['CODES']['method']?>">
            <input type="hidden" name="reservation[uniqueid]" value="<?php echo $booking['IDENTIFICATION_UNIQUEID']?>">
            <input type="hidden" name="reservation[regid]" value="<?php echo $booking['IDENTIFICATION_REFERENCEID']?>">
            <input type="hidden" name="submit_action[reservation]" value="Send">
          <table>
            <tr><td><?php echo x('Amount')?>:</td><td><input type="text" name="reservation[amount]" value="<?php if (!empty($_POST['reservation']['amount'])) echo $_POST['reservation']['amount']; else echo '0.00';?>" size="42">
                   <input type="text" name="reservation[currency]" value="<?php if (!empty($_POST['reservation']['currency'])) echo $_POST['reservation']['currency']; else echo 'EUR';?>" size="4"></td></tr>
            <tr><td><?php echo x('Usage')?>:</td><td><input type="text" name="reservation[usage]" value="<?php echo $_POST['reservation']['usage']?>" size="50"></td></tr>
            <tr><td><?php echo x('TXN-ID')?>:</td><td><input type="text" name="reservation[txnid]" value="<?php echo $_POST['reservation']['txnid']?>" size="50"></td></tr>
            <tr><td><?php echo x('Date')?>:</td><td><input type="text" name="reservation[date]" value="<?php echo date('d.m.Y H:i:s')?>" size="50" readonly class="readonly"></td></tr>
            <tr><td><?php echo x('Comment')?>:</td><td><textarea name="reservation[comment]" cols="47" rows="3"><?php echo $_POST['reservation']['comment']?></textarea></td></tr>
          </table>
          <input type="submit" id="submit_reservation" name="submit_action[reservation]" value="<?php echo x('Send')?>">
          </form>
        </div>

        <h3 id="tabshead-4"><a href="#"><?php echo x('Debit')?></a></h3>
        <div id="tabs-5">
          <form id="form_debit" method="post" action="<?php echo $queryform?>">
            <input type="hidden" name="debit[currency]" value="<?php echo $booking['CLEARING_CURRENCY']?>">
            <input type="hidden" name="debit[paymethod]" value="<?php echo $booking['CODES']['method']?>">
            <input type="hidden" name="debit[uniqueid]" value="<?php echo $booking['IDENTIFICATION_UNIQUEID']?>">
            <input type="hidden" name="debit[regid]" value="<?php echo $booking['IDENTIFICATION_REFERENCEID']?>">
            <input type="hidden" name="submit_action[debit]" value="Send">
          <table>
            <tr><td><?php echo x('Amount')?>:</td><td><input type="text" name="debit[amount]" value="<?php if (!empty($_POST['debit']['amount'])) echo $_POST['debit']['amount']; else echo '0.00';?>" size="42"> 
                   <input type="text" name="debit[currency]" value="<?php if (!empty($_POST['debit']['currency'])) echo $_POST['debit']['currency']; else echo 'EUR';?>" size="4"></td></tr>
            <tr><td><?php echo x('Usage')?>:</td><td><input type="text" name="debit[usage]" value="<?php echo $_POST['debit']['usage']?>" size="50"></td></tr>
            <tr><td><?php echo x('TXN-ID')?>:</td><td><input type="text" name="debit[txnid]" value="<?php echo $_POST['debit']['txnid']?>" size="50"></td></tr>
            <tr><td><?php echo x('Date')?>:</td><td><input type="text" name="debit[date]" value="<?php echo date('d.m.Y H:i:s')?>" size="50" readonly class="readonly"></td></tr>
            <tr><td><?php echo x('Comment')?>:</td><td><textarea name="debit[comment]" cols="47" rows="3"><?php echo $_POST['debit']['comment']?></textarea></td></tr>
          </table>
          <input type="submit" id="submit_debit" name="submit_action[debit]" value="<?php echo x('Send')?>">
          </form>
        </div>

        <h3 id="tabshead-5"><a href="#"><?php echo x('Reversal')?></a></h3>
        <div id="tabs-6">
          <form id="form_reversal" method="post" action="<?php echo $queryform?>">
            <input type="hidden" name="reversal[currency]" value="<?php echo $booking['CLEARING_CURRENCY']?>">
            <input type="hidden" name="reversal[paymethod]" value="<?php echo $booking['CODES']['method']?>">
            <input type="hidden" name="reversal[uniqueid]" value="<?php echo $booking['IDENTIFICATION_UNIQUEID']?>">
            <input type="hidden" name="submit_action[reversal]" value="Send">
          <table>
            <tr><td><?php echo x('Amount')?>:</td><td><input type="text" name="reversal[amount]" value="<?php echo $booking['CLEARING_AMOUNT']?>" size="50"> <?php echo $booking['CLEARING_CURRENCY']?></td></tr>
            <tr><td><?php echo x('Usage')?>:</td><td><input type="text" name="reversal[usage]" value="<?php echo $booking['CLEARING_DESCRIPTOR']?>" size="50"></td></tr>
            <tr><td><?php echo x('TXN-ID')?>:</td><td><input type="text" name="reversal[txnid]" value="<?php echo $booking['IDENTIFICATION_TRANSACTIONID']?>" size="50"></td></tr>
            <tr><td><?php echo x('Date')?>:</td><td><input type="text" name="reversal[date]" value="<?php echo $booking['CLEARING_DATE']?>" size="50" readonly class="readonly"></td></tr>
            <tr><td><?php echo x('Comment')?>:</td><td><textarea name="reversal[comment]" cols="47" rows="3"></textarea></td></tr>
          </table>
          <input type="submit" id="submit_reversal" name="submit_action[reversal]" value="<?php echo x('Send')?>">
          </form>
        </div>

        <h3 id="tabshead-6"><a href="#"><?php echo x('Deschedule')?></a></h3>
        <div id="tabs-7">
          <form id="form_deschedule" method="post" action="<?php echo $queryform?>">
            <input type="hidden" name="deschedule[currency]" value="<?php echo $booking['CLEARING_CURRENCY']?>">
            <input type="hidden" name="deschedule[paymethod]" value="<?php echo $booking['CODES']['method']?>">
            <input type="hidden" name="deschedule[uniqueid]" value="<?php echo $booking['IDENTIFICATION_UNIQUEID']?>">
            <input type="hidden" name="submit_action[deschedule]" value="Send">
          <table>
            <tr><td><?php echo x('Amount')?>:</td><td><input type="text" name="deschedule[amount]" value="<?php echo $booking['CLEARING_AMOUNT']?>" size="50" readonly class="readonly"> <?php echo $booking['CLEARING_CURRENCY']?></td></tr>
            <tr><td><?php echo x('Usage')?>:</td><td><input type="text" name="deschedule[usage]" value="<?php echo $booking['CLEARING_DESCRIPTOR']?>" size="50" readonly class="readonly"></td></tr>
            <tr><td><?php echo x('TXN-ID')?>:</td><td><input type="text" name="deschedule[txnid]" value="<?php echo $booking['IDENTIFICATION_TRANSACTIONID']?>" size="50"></td></tr>
            <tr><td><?php echo x('Date')?>:</td><td><input type="text" name="deschedule[date]" value="<?php echo $booking['CLEARING_DATE']?>" size="50" readonly class="readonly"></td></tr>
            <tr><td><?php echo x('Comment')?>:</td><td><textarea name="deschedule[comment]" cols="47" rows="3"></textarea></td></tr>
          </table>
          <input type="submit" id="submit_deschedule" name="submit_action[deschedule]" value="<?php echo x('Send')?>">
          </form>
        </div>

        <h3 id="tabshead-7"><a href="#"><?php echo x('Schedule')?></a></h3>
        <div id="tabs-8">
          <form id="form_schedule" method="post" action="<?php echo $queryform?>">
            <input type="hidden" name="schedule[currency]" value="<?php echo $booking['CLEARING_CURRENCY']?>">
            <input type="hidden" name="schedule[paymethod]" value="<?php echo $booking['CODES']['method']?>">
            <input type="hidden" name="schedule[uniqueid]" value="<?php echo $booking['IDENTIFICATION_UNIQUEID']?>">
            <input type="hidden" name="submit_action[schedule]" value="Send">
          <table>
            <tr><td><?php echo x('Amount')?>:</td><td><input type="text" name="schedule[amount]" value="<?php echo $booking['CLEARING_AMOUNT']?>" size="50"> <?php echo $booking['CLEARING_CURRENCY']?></td></tr>
            <tr><td><?php echo x('Usage')?>:</td><td><input type="text" name="schedule[usage]" value="<?php echo $booking['CLEARING_DESCRIPTOR']?>" size="50"></td></tr>
            <tr><td><?php echo x('TXN-ID')?>:</td><td><input type="text" name="schedule[txnid]" value="<?php echo $booking['IDENTIFICATION_TRANSACTIONID']?>" size="50"></td></tr>
            <tr><td><?php echo x('Date')?>:</td><td><input type="text" name="schedule[date]" value="<?php echo $booking['CLEARING_DATE']?>" size="50" readonly class="readonly"></td></tr>
            <tr><td><?php echo x('Comment')?>:</td><td><textarea name="schedule[comment]" cols="47" rows="3"></textarea></td></tr>
          </table>
          <input type="submit" id="submit_schedule" name="submit_action[schedule]" value="<?php echo x('Send')?>">
          </form>
        </div>

      </div>     
  </div>
</div>
