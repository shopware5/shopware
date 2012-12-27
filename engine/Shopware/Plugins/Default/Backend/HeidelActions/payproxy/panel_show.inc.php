<?php if (!defined('isHOP')) die();?>
<table id="block_person">
  <tr>
    <td class="td_title"><?php echo x('Name')?>:</td>
    <td><?php echo $booking['NAME_SALUTATION'].' '.$booking['NAME_GIVEN'].' '.$booking['NAME_FAMILY']?></td>
  </tr>
  <tr>
    <td class="td_title" valign="top"><?php echo x('Address')?>:</td>
    <td><?php echo $booking['ADDRESS_STREET'].'<br>'
        .$booking['ADDRESS_ZIP'].' '
        .$booking['ADDRESS_CITY'].'<br>'
        .$booking['ADDRESS_STATE'].' '
        .$booking['ADDRESS_COUNTRY']?></td>
  </tr>
  <tr>
    <td class="td_title"><?php echo x('EMail')?>:</td>
    <td><?php echo $booking['CONTACT_EMAIL']?></td>
  </tr>  
</table>

<table id="block_booking">
  <tr>
    <td class="td_title"><?php echo x('Amount')?>:</td>
    <td>
      <a href="#" id="button_action" class="ui-state-default ui-corner-all" style="float: right;"><?php echo x('Actions')?></a>
      <?php echo $booking['CLEARING_AMOUNT'].' '.$booking['CLEARING_CURRENCY']?>
    </td>
  </tr>
  <tr>
    <td class="td_title"><?php echo x('Descriptor')?>:</td>
    <td><?php echo $booking['CLEARING_DESCRIPTOR']?></td>
  </tr>
  <tr>
    <td class="td_title"><?php echo x('Date')?>:</td>
    <td><?php echo $booking['CLEARING_DATE']?></td>
  </tr>
  <tr><td colspan="2"><!--{{{Actions--><?php require_once('panel_actions.inc.php');?><!--}}}--></td></tr>
</table>

<table id="block_status">
  <tr>
    <td class="td_title"><?php echo x('Status')?>:</td>
    <td><?php echo $booking['PROCESSING_RETURN']?></td>
  </tr>
  <tr>
    <td class="td_title"><?php echo x('Meth')?>. / <?php echo x('Type')?>:</td>
    <td><?php echo $booking['PAYMENT_CODE']?></td>
  </tr>
  <?php if (!empty($booking['CRITERION_COMMENT'])){?>
  <tr>
    <td class="td_title"><?php echo x('Comment')?>:</td>
    <td><?php echo $booking['CRITERION_COMMENT']?></td>
  </tr>
  <?php }?>
</table>

<table id="block_paymentinfo">
  <?php if (in_array($booking['CODES']['method'], array('CC', 'DC', 'OT', 'DD'))) {?>
  <tr>
    <td class="td_title"><?php echo x('Holder')?>:</td>
    <td><?php echo $booking['ACCOUNT_HOLDER']?></td>
  </tr>
  <?php }?>
  <?php if (in_array($booking['CODES']['method'], array('CC', 'DC'))) {?>
  <tr>
    <td class="td_title"><?php echo x('Number')?>:</td>
    <td><?php echo $booking['ACCOUNT_NUMBER']?></td>
  </tr>
  <tr>
    <td class="td_title"><?php echo x('Expiry')?>:</td>
    <td><?php echo $booking['ACCOUNT_MONTH'].'/'.$booking['ACCOUNT_YEAR']?></td>
  </tr>
  <tr>
    <td class="td_title"><?php echo x('Brand')?>:</td>
    <td><?php echo $booking['ACCOUNT_BRAND']?></td>
  </tr>
  <?php }?>
  <?php if (in_array($booking['CODES']['method'], array('OT', 'DD'))) {?>
  <tr>
    <td class="td_title"><?php echo x('Account Number')?>:</td>
    <td><?php echo $booking['ACCOUNT_NUMBER']?></td>
  </tr>
  <tr>
    <td class="td_title"><?php echo x('Bankcode')?>:</td>
    <td><?php echo $booking['ACCOUNT_BANK']?></td>
  </tr>
  <tr>
    <td class="td_title"><?php echo x('Account Country')?>:</td>
    <td><?php echo $booking['ACCOUNT_COUNTRY']?></td>
  </tr>
  <tr>
    <td class="td_title"><?php echo x('Bankname')?>:</td>
    <td><?php echo $booking['ACCOUNT_BANKNAME']?></td>
  </tr>
  <?php }?>
</table>

<table id="block_ids">
  <tr>
    <td class="td_title"><?php echo x('Short ID')?>:</td>
    <td><?php echo $booking['IDENTIFICATION_SHORTID']?></td>
  </tr>
  <tr>
    <td class="td_title"><?php echo x('Unique ID')?>:</td>
    <td><?php echo $booking['IDENTIFICATION_UNIQUEID']?></td>
  </tr>
  <tr>
    <td class="td_title"><?php echo x('Transaction ID')?>:</td>
    <td><?php echo $booking['IDENTIFICATION_TRANSACTIONID']?></td>
  </tr>
  <tr>
    <td class="td_title"><?php echo x('Reference ID')?>:</td>
    <td><a href="<?php echo $query.'&uid='.$booking['IDENTIFICATION_REFERENCEID']?>"><?php echo $booking['IDENTIFICATION_REFERENCEID']?></a></td>
  </tr>
  <tr>
    <td class="td_title"><?php echo x('Mode')?>:</td>
    <td><?php echo $booking['TRANSACTION_MODE']?></td>
  </tr>
  <tr>
    <td class="td_title"><?php echo x('Source')?>:</td>
    <td><?php echo $booking['TRANSACTION_SOURCE']?></td>
  </tr>
</table>

<table id="block_references">
  <tr>
    <td><?php echo x('Date')?></td>
    <td><?php echo x('TXN-ID')?></td>
    <td><?php echo x('SHORT-ID')?></td>
    <td><?php echo x('Result')?></td>
    <td><?php echo x('Name')?></td>
    <td><?php echo x('Amount')?></td>
    <td><?php echo x('Code')?></td>
  </tr>
  <?php foreach($refbookings AS $k => $v){?>
  <tr>
    <td><a href="<?php echo $query.'&uid='.$v['IDENTIFICATION_UNIQUEID']?>"><?php echo $v['PROCESSING_TIMESTAMP']?></a></td>
    <td title="<?php echo $v['IDENTIFICATION_TRANSACTIONID']?>"><div style="overflow: hidden; width: 100px"><?php echo $v['IDENTIFICATION_TRANSACTIONID']?></div></td>
    <td><?php echo $v['IDENTIFICATION_SHORTID']?></td>
    <td><?php echo $v['PROCESSING_RESULT']?></td>
    <td><?php echo $v['NAME_GIVEN'].' '.$v['NAME_FAMILY']?></td>
    <td><?php echo $v['CLEARING_AMOUNT'].' '.$v['CLEARING_CURRENCY']?></td>
    <td><?php echo $v['PAYMENT_CODE']?></td>
  </tr>
  <?php }?></table>
