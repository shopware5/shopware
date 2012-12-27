<?php if (!defined('isHOP')) die();?><?php /*{{{ Javascripts*/?>
<?php echo '<style>'.file_get_contents(dirname(__FILE__) . '/css/smoothness/jquery-ui-1.8.21.custom.css').'</style>'?>
<?php echo '<script type="text/javascript">'.file_get_contents(dirname(__FILE__) . '/js/jquery-1.7.2.min.js').'</script>'?>
<?php echo '<script type="text/javascript">'.file_get_contents(dirname(__FILE__) . '/js/jquery-ui-1.8.21.custom.min.js').'</script>'?>
		<script type="text/javascript">
$(function(){

    // run the currently selected effect
		function runEffect() {
      var options = {
        'hide': true,
      };			
			// run the effect
			$( "#effect" ).toggle( "blind", options, 500 );
		};
		// set effect from select menu value
		$( "#button_action" ).click(function() {
			runEffect();
			return false;
    });

    function runEffectSearch() {
      var options = {
        'hide': true,
      };			
			// run the effect
			$( "#effectSearch" ).toggle( "slide", options, 500 );
		};
		// set effect from select menu value
		$( "#button_search" ).click(function() {
			runEffectSearch();
			return false;
    });

    var actualSetting;
    var actualSettingId;
    var actualSettingMenu = new Array(<?php
    if($settings[$channel]['allowABO'] == 1) echo 'true,'; else  echo 'false,'; 
    if($settings[$channel]['allowRATE'] == 1) echo 'true,'; else  echo 'false,'; 
    if($settings[$channel]['allowDEPOSIT'] == 1) echo 'true,'; else  echo 'false,'; 
    ?>false);
    function runEffectSettings() {
      var options = {
        'hide': true,
        'direction': 'up',
      };
			// run the effect
      if (!actualSettingMenu[actualSettingId]){
        $( "#effectSettings"+actualSetting ).slideDown(200, "swing");
        actualSettingMenu[actualSettingId] = true;
      } else {
        $( "#effectSettings"+actualSetting ).slideUp(200, "swing");
        actualSettingMenu[actualSettingId] = false;
      }
    };

    $( "#button_setting_abo" ).click(function() {
      actualSetting = 'abo';
      actualSettingId = 0;
			runEffectSettings();
			return false;
    });
    $( "#button_setting_rate" ).click(function() {
      actualSetting = 'rate';
      actualSettingId = 1;
			runEffectSettings();
			return false;
    });
    $( "#button_setting_deposit" ).click(function() {
      actualSetting = 'deposit';
      actualSettingId = 2;
			runEffectSettings();
			return false;
    });

    var activeMenu = false;
    function runEffectMenu() {
      if (!activeMenu){
        $( "#effectMenu" ).slideDown(200, "swing");
        activeMenu = true;
      } else {
        $( "#effectMenu" ).slideUp(200, "swing");
        activeMenu = false;
      }
		};
		// set effect from select menu value
		$( "#button_menu" ).click(function() {
			runEffectMenu();
			return false;
    });

  <?php if (empty($settings)){?>
    // Tabs
    $("#accordion").accordion({ 
      header: "h3",
      autoHeight: false,
			navigation: true,
      <?php echo $tabSelected?>
      <?php #echo $tabDisabled?>
    });

    // Add the class ui-state-disabled to the headers that you want disabled
    <?php echo $tabHeads?>

    // Now the hack to implement the disabling functionnality
    var accordion = $( "#accordion" ).data("accordion");

    accordion._std_clickHandler = accordion._clickHandler;

    accordion._clickHandler = function( event, target ) {
      var clicked = $( event.currentTarget || target );
      if (! clicked.hasClass("ui-state-disabled")) {
        this._std_clickHandler(event, target);
      }
    };
  <?php }?>

    // Dialog
    <?php if (!empty($msg)){?>$('#dialog').dialog({
      resizable: false,
      modal: true,
      position: "top",
			buttons: {
				Ok: function() {
          $(this).dialog("close");
          <?php if ($forceRelaod) {?>
          var url = "<?php echo $queryform?>";
          $(location).attr('href',url);
          
          <?php }?>
				}
			}
    });<?php }?>
    // Confirm Dialog
    <?php if (!empty($confirm)){?>
    var currentForm;
    $('#confirmdialog').dialog({
      autoOpen: false,
			show: "blind",
			hide: "blind",
      position: "top",
      resizable: false,
			height:140,
			modal: true,
			buttons: {
				'Ja': function() {
          currentForm.submit();
				},
				'Nein': function() {
          $(this).dialog("close");
  			}
			}
    });
    $("#form_refund").submit(function() {
      currentForm = this;
      $("#confirmdialog").dialog("open");
      return false;
    });
    $("#form_rebill").submit(function() {
      currentForm = this;
      $("#confirmdialog").dialog("open");
      return false;
    });
    $("#form_reversal").submit(function() {
      currentForm = this;
      $("#confirmdialog").dialog("open");
      return false;
    });
    $("#form_deschedule").submit(function() {
      currentForm = this;
      $("#confirmdialog").dialog("open");
      return false;
    });
    <?php }?>
    
  });
    </script>
<?php /*}}}*/?>
