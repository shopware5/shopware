<?php if (!defined('isHOP')) die();?><?php /*{{{Styles*/?>
  <style>
	.toggler { width: 510px; xheight: 50px; position: relative}
  #button_action { padding: .5em 1em; text-decoration: none; display: <?php echo $displayTabs?>}
  #effect { width: 500px; xheight: 250px; padding: 0.4em; position: relative; display: <?php echo $displayActions?>}
  #effect h3 { margin: 0; padding: 0.4em; text-align: center; }
  
  #effect_settings { width: 500px; xheight: 250px; padding: 0.4em; position: relative; display: block}
  #effect_settings h3 { margin: 0; padding: 0.4em; text-align: center; }
  #effectSettingsabo{display: none}
  #effectSettingsrate{display: none}
  #effectSettingsdeposit{display: none}

  .togglerSearch { width: 410px; position: relative;}
  #button_search { padding: .5em 1em; text-decoration: none; display: block}
  #effectSearch { width: 400px; height: 160px; padding: 0.4em; position: relative; display: none}
  #effectSearch h3 { margin: 0; padding: 0.4em; text-align: center; }

  .togglerMenu { width: 510px; position: relative;}
  #button_menu { padding: .5em 1em; text-decoration: none; display: block;}
  #effectMenu { width: 510px; height: 25px; padding: 0.4em; position: relative; display: none; background-color: #eee; border: 1px solid #ddd}
  #effectMenu h3 { margin: 0; padding: 0.4em; text-align: center;}

  #button_openpa { padding: .5em 1em; text-decoration: none; display: block}
  #button_settings { padding: .5em 1em; text-decoration: none; display: block}

  #accordion {display: <?php echo $displayTabs?>}
  #dialog {color: #<?php echo $dialogColor?>; display: none}
  #confirmdialog{color: #000; display: none}
  </style>
  <link type="text/css" media="all" rel="stylesheet" href="style.css" />
  <?php if (!empty($extStyle)) echo '<style>'.file_get_contents(dirname(__FILE__) . '/'.$extStyle).'</style>'?>
<?php /*}}}*/?>
