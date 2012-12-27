<?php if (!defined('isHOP')) die();?>
<form id="form_settings" method="post" action="<?php echo $queryshow.'ch='.$channel?>" style="width: 500px">
<?php 
  if (empty($channel)){
    echo '<div align="center">';
    echo x('Please choose your Channel').':<br><br>';
    foreach($settings AS $k => $v){
      echo '<a href="'.$queryshow.'ch='.$k.'" class="ui-widget-header ui-corner-all" style="padding: 4px;">'.$k.'</a><br><br>';
    }
    echo '</div>';
  } else if(!empty($channel) && !empty($settings[$channel])) {
    $channelDat = $settings[$channel];

    #echo '<pre>'.print_r($_POST, 1).'</pre>';
?>
<input type="hidden" name="oid" value="<?php echo $channelDat['id']?>">
<table id="block_settings">
  <tr>
    <td colspan="2">
      <div class="ui-state-default ui-corner-top" style="text-align: center; width: 80%; margin-left: 20px; font-weight: bold"><?php echo x('Settings for').' '.$channel?></div>

      <div class="toggler">
	    <div id="effect_settings" class="ui-widget-content ui-corner-all">
      <div id="accordion_settings" style="border: 0px;">

    <?php foreach($hp->allKinds AS $kind => $rates){?>

      <h3 id="button_setting_<?php echo $kind?>" class="button_setting ui-widget-header"><a href="#"><?php echo x($kind)?></a></h3>
      <div id="effectSettings<?php echo $kind?>" <?php if ($channelDat['allow'.strtoupper($kind)] == 1) echo 'style="display: block"';?>>

      <input type="hidden" name="settings[allow<?php echo strtoupper($kind);?>]" value="0">
      <input type="checkbox" name="settings[allow<?php echo strtoupper($kind);?>]" value="1" <?php if ($channelDat['allow'.strtoupper($kind)] == 1) echo 'checked';?>><?php echo x($kind).' '.x('enable')?><br>
      <table style="width: 100%" cellspacing=1 cellpadding=1>
        <tr>
          <td  valign="top" class="col1">
            <?php if ($kind=='rate'){ echo x('Duration'); } else if ($kind=='deposit'){ echo x('Payment target'); }?>
            <?php if ($kind!='abo') echo '<br>'.x('month')?>
          </td>
          <td valign="top" class="col3">
            <?php if ($kind=='rate' || $kind=='abo'){ echo x('Rhythm'); }?>
            <?php if ($kind!='deposit') echo '<br>'.x('month')?>
          </td>
          <td valign="top" class="col1" align="center">
            <?php if ($kind=='rate' || $kind=='abo'){ echo x('Fee / Discount'); } else if ($kind=='deposit'){ echo x('Initial Payment'); }?>
          </td>
          <td valign="top" class="col3" align="center">
            <?php echo x('Min.')?><br><?php echo x('euro')?>
          </td>
          <td valign="top" class="col1" align="center">
            <?php echo x('Max.')?><br><?php echo x('euro')?>
          </td>
          <td valign="top" class="col3">
            <?php echo x('Sort.')?>
          </td>
          <td valign="top" class="col1">
            <?php echo x('Del.')?>
          </td>
        </tr>
      <?php 
        if (!empty($channelDat['rates'][$kind])) {
          $x=0; 
          foreach($channelDat['rates'][$kind] AS $k => $v){
            $x++;
            $classA = 'col1';
            $classB = 'col3';
            if ($x%2==0) {
              $classA = 'col2';
              $classB = 'col4';
            }
      ?>
        <tr><td class="<?php echo $classA;?>">
        <select name="settings[<?php echo $kind;?>][<?php echo $k;?>][duration]" <?php if ($kind=='abo') echo 'style="display:none"';?>>
        <?php if ($kind=='abo'){?>
          <option value="0" <?php if ($v['duration']=='0') echo 'selected';?>>0</option>
        <?} else {?>
          <option value="1" <?php if ($v['duration']=='1') echo 'selected';?>>1</option>
          <option value="2" <?php if ($v['duration']=='2') echo 'selected';?>>2</option>
          <option value="3" <?php if ($v['duration']=='3') echo 'selected';?>>3</option>
          <option value="4" <?php if ($v['duration']=='4') echo 'selected';?>>4</option>
          <?php if ($kind=='deposit' || $kind=='rate'){?>
            <option value="5" <?php if ($v['duration']=='5') echo 'selected';?>>5</option>
          <?php }?>
          <option value="6" <?php if ($v['duration']=='6') echo 'selected';?>>6</option>
          <?php if ($kind=='deposit' || $kind=='rate'){?>
            <option value="7" <?php if ($v['duration']=='7') echo 'selected';?>>7</option>
            <option value="8" <?php if ($v['duration']=='8') echo 'selected';?>>8</option>
            <option value="9" <?php if ($v['duration']=='9') echo 'selected';?>>9</option>
            <option value="10" <?php if ($v['duration']=='10') echo 'selected';?>>10</option>
            <option value="11" <?php if ($v['duration']=='11') echo 'selected';?>>11</option>
          <?php }?>
          <option value="12" <?php if ($v['duration']=='12') echo 'selected';?>>12</option>
          <?php if ($kind=='rate'){?>
            <?php for ($i=13; $i<=96; $i++){?>
            <option value="<?php echo $i;?>" <?php if ($v['duration']==$i) echo 'selected';?>><?php echo $i;?></option>
            <?php }?>
          <?php }?>

        <?php }?>
        </select>
        <select name="settings[<?php echo $kind;?>][<?php echo $k;?>][durationtype]" <?php /*if ($kind=='abo')*/ echo 'style="display:none"';?>>
          <!--<option value="day" <?php if ($v['durationtype']=='day') echo 'selected';?>><?php echo x('day')?></option>-->
          <!--<option value="week" <?php if ($v['durationtype']=='week') echo 'selected';?>><?php echo x('week')?></option>-->
          <option value="month" <?php if ($v['durationtype']=='month') echo 'selected';?>><?php echo x('month')?></option>
          <!--<option value="year" <?php if ($v['durationtype']=='year') echo 'selected';?>><?php echo x('year')?></option>-->
        </select>

        </td><td class="<?php echo $classB;?>">
        
        <select name="settings[<?php echo $kind;?>][<?php echo $k;?>][freq]" <?php if ($kind=='deposit') echo 'style="display:none"';?>>
        <?php if ($kind=='deposit'){?>
          <option value="0" <?php if ($v['freq']=='0') echo 'selected';?>>0</option>
        <?} else {?>
          <option value="1" <?php if ($v['freq']=='1') echo 'selected';?>>1</option>
          <option value="2" <?php if ($v['freq']=='2') echo 'selected';?>>2</option>
          <option value="3" <?php if ($v['freq']=='3') echo 'selected';?>>3</option>
          <option value="4" <?php if ($v['freq']=='4') echo 'selected';?>>4</option>
          <option value="6" <?php if ($v['freq']=='6') echo 'selected';?>>6</option>
          <option value="12" <?php if ($v['freq']=='12') echo 'selected';?>>12</option>
        <?php }?>
        </select>
        <select name="settings[<?php echo $kind;?>][<?php echo $k;?>][freqtype]" <?php /*if ($kind=='deposit')*/ echo 'style="display:none"';?>>
          <!--<option value="day" <?php if ($v['freqtype']=='day') echo 'selected';?>><?php echo x('day')?></option>-->
          <!--<option value="week" <?php if ($v['freqtype']=='week') echo 'selected';?>><?php echo x('week')?></option>-->
          <option value="month" <?php if ($v['freqtype']=='month') echo 'selected';?>><?php echo x('month')?></option>
          <!--<option value="year" <?php if ($v['freqtype']=='year') echo 'selected';?>><?php echo x('year')?></option>-->
        </select>

        </td><td class="<?php echo $classA;?>" align="center">
         
        <?php 
          $fee = $v['fee'];
          $fee_sign = '+';
          if ($fee < 0) {
            $fee_sign = '-';
            $fee*= -1;
          }
          $fee_cent = $fee % 100;
          $fee_euro = ($fee - $fee_cent) / 100;
          #echo $v['fee'].'=>'.$fee_sign.'|'.$fee_euro.'|'.$fee_cent.'<br>';
        ?>
        <select name="settings[<?php echo $kind;?>][<?php echo $k;?>][fee_sign]" style="width: 40px; <?php if ($kind=='deposit') echo 'display:none';?>">
          <option value="+" <?php if ($fee_sign=='+') echo 'selected';?>>+</option>
          <option value="-" <?php if ($fee_sign=='-') echo 'selected';?>>-</option>
        </select>
        <select name="settings[<?php echo $kind;?>][<?php echo $k;?>][fee_euro]" style="width: 52px">
          <?php for($i=0; $i<=100; $i++){?>
          <option value="<?php echo sprintf('%02d', $i)?>" <?php if ($fee_euro==$i) echo 'selected';?>><?php echo sprintf('%02d', $i)?></option>
          <?php }?>
        </select>,
        <select name="settings[<?php echo $kind;?>][<?php echo $k;?>][fee_cent]" style="width: 44px">
          <?php for($i=0; $i<100; $i++){?>
          <option value="<?php echo sprintf('%02d', $i)?>" <?php if ($fee_cent==$i) echo 'selected';?>><?php echo sprintf('%02d', $i)?></option>
          <?php }?>
        </select>
        <select name="settings[<?php echo $kind;?>][<?php echo $k;?>][feetype]" style="width: 42px">
          <option value="percent" <?php if ($v['feetype']=='percent') echo 'selected';?>><?php echo x('percent')?></option>
          <option value="euro" <?php if ($v['feetype']=='euro') echo 'selected';?>><?php echo x('euro')?></option>
        </select>

        </td><td class="<?php echo $classB;?>" align="center">

        <input type="text" name="settings[<?php echo $kind;?>][<?php echo $k;?>][mini]" value="<?php echo $v['mini']?>" size="3">

        </td><td class="<?php echo $classA;?>" align="center">

        <input type="text" name="settings[<?php echo $kind;?>][<?php echo $k;?>][maxi]" value="<?php echo $v['maxi']?>" size="3">

        </td><td class="<?php echo $classB;?>">

        <select name="settings[<?php echo $kind;?>][<?php echo $k;?>][sortorder]">
          <?php for($i=0; $i<100; $i++){?>
          <option value="<?php echo sprintf('%02d', $i)?>" <?php if ($v['sortorder']==$i) echo 'selected';?>><?php echo sprintf('%02d', $i)?></option>
          <?php }?>
        </select>

        </td><td class="<?php echo $classA;?>">

        <input type="checkbox" name="settings[<?php echo $kind;?>][<?php echo $k;?>][delete]" value="1">

        </td></tr>
      <?php }} // mit IF?>

        <tr><td colspan="5"><input type="checkbox" name="new_settings[activate][]" value="<?php echo $kind;?>"><?php echo x('New entry').' '.x('enable')?></td></tr>
        <tr><td class="col1">
        <?php $v = $_POST['new_settings'];?>
        <select name="new_settings[<?php echo $kind;?>][duration]" <?php if ($kind=='abo') echo 'style="display:none"';?>>
        <?php if ($kind=='abo'){?>
          <option value="0" <?php if ($v['duration']=='0') echo 'selected';?>>0</option>
        <?} else {?>
          <option value="1" <?php if ($v['duration']=='1') echo 'selected';?>>1</option>
          <option value="2" <?php if ($v['duration']=='2') echo 'selected';?>>2</option>
          <option value="3" <?php if ($v['duration']=='3') echo 'selected';?>>3</option>
          <option value="4" <?php if ($v['duration']=='4') echo 'selected';?>>4</option>
          <?php if ($kind=='deposit' || $kind=='rate'){?>
            <option value="5" <?php if ($v['duration']=='5') echo 'selected';?>>5</option>
          <?php }?>
          <option value="6" <?php if ($v['duration']=='6') echo 'selected';?>>6</option>
          <?php if ($kind=='deposit' || $kind=='rate'){?>
            <option value="7" <?php if ($v['duration']=='7') echo 'selected';?>>7</option>
            <option value="8" <?php if ($v['duration']=='8') echo 'selected';?>>8</option>
            <option value="9" <?php if ($v['duration']=='9') echo 'selected';?>>9</option>
            <option value="10" <?php if ($v['duration']=='10') echo 'selected';?>>10</option>
            <option value="11" <?php if ($v['duration']=='11') echo 'selected';?>>11</option>
          <?php }?>
          <option value="12" <?php if ($v['duration']=='12') echo 'selected';?>>12</option>
          <?php if ($kind=='rate'){?>
            <?php for ($i=13; $i<=96; $i++){?>
            <option value="<?php echo $i;?>" <?php if ($v['duration']==$i) echo 'selected';?>><?php echo $i;?></option>
            <?php }?>
          <?php }?>
        <?php }?>
        </select>
        <select name="new_settings[<?php echo $kind;?>][durationtype]" <?php /*if ($kind=='abo')*/ echo 'style="display:none"';?>>
          <!--<option value="day" <?php if ($v['durationtype']=='day') echo 'selected';?>><?php echo x('day')?></option>-->
          <!--<option value="week" <?php if ($v['durationtype']=='week') echo 'selected';?>><?php echo x('week')?></option>-->
          <option value="month" <?php if ($v['durationtype']=='month') echo 'selected';?>><?php echo x('month')?></option>
          <!--<option value="year" <?php if ($v['durationtype']=='year') echo 'selected';?>><?php echo x('year')?></option>-->
        </select>

        </td><td class="col3">
        
        <select name="new_settings[<?php echo $kind;?>][freq]" <?php if ($kind=='deposit') echo 'style="display:none"';?>>
        <?php if ($kind=='deposit'){?>
          <option value="0" <?php if ($v['freq']=='0') echo 'selected';?>>0</option>
        <?} else {?>
          <option value="1" <?php if ($v['freq']=='1') echo 'selected';?>>1</option>
          <option value="2" <?php if ($v['freq']=='2') echo 'selected';?>>2</option>
          <option value="3" <?php if ($v['freq']=='3') echo 'selected';?>>3</option>
          <option value="4" <?php if ($v['freq']=='4') echo 'selected';?>>4</option>
          <option value="6" <?php if ($v['freq']=='6') echo 'selected';?>>6</option>
          <option value="12" <?php if ($v['freq']=='12') echo 'selected';?>>12</option>
        <?php }?>
        </select>
        <select name="new_settings[<?php echo $kind;?>][freqtype]" <?php /*if ($kind=='deposit')*/ echo 'style="display:none"';?>>
          <!--<option value="day" <?php if ($v['freqtype']=='day') echo 'selected';?>><?php echo x('day')?></option>-->
          <!--<option value="week" <?php if ($v['freqtype']=='week') echo 'selected';?>><?php echo x('week')?></option>-->
          <option value="month" <?php if ($v['freqtype']=='month') echo 'selected';?>><?php echo x('month')?></option>
          <!--<option value="year" <?php if ($v['freqtype']=='year') echo 'selected';?>><?php echo x('year')?></option>-->
        </select>

        </td><td class="col1" align="center">
         
        <?php 
          $fee_sign = $_POST['new_settings']['fee_sign'];
          $fee_cent = $_POST['new_settings']['fee_cent'];
          $fee_euro = $_POST['new_settings']['fee_euro'];
          #echo $v['fee'].'=>'.$fee_sign.'|'.$fee_euro.'|'.$fee_cent.'<br>';
        ?>
        <select name="new_settings[<?php echo $kind;?>][fee_sign]"  style="width: 40px; <?php if ($kind=='deposit') echo 'display:none';?>">
          <option value="+" <?php if ($fee_sign=='+') echo 'selected';?>>+</option>
          <option value="-" <?php if ($fee_sign=='-') echo 'selected';?>>-</option>
        </select>
        <select name="new_settings[<?php echo $kind;?>][fee_euro]" style="width: 52px">
          <?php for($i=0; $i<=100; $i++){?>
          <option value="<?php echo sprintf('%02d', $i)?>" <?php if ($fee_euro==$i) echo 'selected';?>><?php echo sprintf('%02d', $i)?></option>
          <?php }?>
        </select>,
        <select name="new_settings[<?php echo $kind;?>][fee_cent]" style="width: 44px">
          <?php for($i=0; $i<100; $i++){?>
          <option value="<?php echo sprintf('%02d', $i)?>" <?php if ($fee_cent==$i) echo 'selected';?>><?php echo sprintf('%02d', $i)?></option>
          <?php }?>
        </select>
        <select name="new_settings[<?php echo $kind;?>][feetype]" style="width: 42px">
          <option value="percent" <?php if ($v['feetype']=='percent') echo 'selected';?>><?php echo x('percent')?></option>
          <option value="euro" <?php if ($v['feetype']=='euro') echo 'selected';?>><?php echo x('euro')?></option>
        </select>

        </td><td class="col3" align="center">

        <input type="text" name="new_settings[<?php echo $kind;?>][mini]" value="<?php echo $v['mini']?>" size="3">

        </td><td class="col1" align="center">

        <input type="text" name="new_settings[<?php echo $kind;?>][maxi]" value="<?php echo $v['maxi']?>" size="3">

        </td><td class="col3"> 

        <select name="new_settings[<?php echo $kind;?>][sortorder]">
          <?php for($i=0; $i<=100; $i++){?>
          <option value="<?php echo sprintf('%02d', $i)?>" <?php if ($v['sortorder']==$i) echo 'selected';?>><?php echo sprintf('%02d', $i)?></option>
          <?php }?>
        </select>

        </td></tr>
      </table>

      </div>

    <?php }?>

    </div></div></div>
    </td></tr>
  </table>
  <input type="submit" name="submit_settings" value="<?php echo x('Save')?>">
  <?php }?>
  
</form>
