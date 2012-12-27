<?php if (!defined('isHOP')) die();?>
<div id="block_searchresult" style="overflow-x: hidden; overflow-y: scroll; height: 320px; width: 518px;">
<table style="width:98%">
  <tr>
    <td><?php echo x('Date')?></td>
    <td><?php echo x('TXN-ID')?></td>
    <td><?php echo x('SHORT-ID')?></td>
    <td><?php echo x('Result')?></td>
    <td><?php echo x('Name')?></td>
    <td><?php echo x('Amount')?></td>
    <td><?php echo x('Code')?></td>
  </tr>
  <?php $i=0; foreach($searchResult AS $k => $v){ $i++;?>
  <tr class="<?php if ($i%2==1) echo 'col1'; else echo 'col3';?>">
    <td><a href="<?php echo $query.'&uid='.$v['IDENTIFICATION_UNIQUEID']?>&act=search" target="search_view"><?php echo $v['PROCESSING_TIMESTAMP']?></a></td>
    <td><div style="overflow: hidden; width: 100px"><?php echo $v['IDENTIFICATION_TRANSACTIONID']?></div></td>
    <td><?php echo $v['IDENTIFICATION_SHORTID']?></td>
    <td><?php echo $v['PROCESSING_RESULT']?></td>
    <td><?php echo $v['NAME_GIVEN'].' '.$v['NAME_FAMILY']?></td>
    <td><?php echo $v['CLEARING_AMOUNT'].' '.$v['CLEARING_CURRENCY']?></td>
    <td><?php echo $v['PAYMENT_CODE']?></td>
  </tr>
  <?php }?></table>
</div>
<iframe src="about:blank" name="search_view" id="search_view">
