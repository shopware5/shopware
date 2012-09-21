<?php
if (!defined("installer")){
    exit;
}
?>
<!doctype html>
<!--[if lt IE 7]> <html class="no-js lt-ie9 lt-ie8 lt-ie7" lang="en"> <![endif]-->
<!--[if IE 7]>    <html class="no-js lt-ie9 lt-ie8" lang="en"> <![endif]-->
<!--[if IE 8]>    <html class="no-js lt-ie9" lang="en"> <![endif]-->
<!--[if gt IE 8]><!-->
<html class="no-js" lang="en"> <!--<![endif]-->

<head>
    <meta charset="utf-8"/>
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1"/>

    <title>Shopware 4 - Systemcheck</title>
    <link rel="shortcut icon" href="<?php echo $basepath ?>/templates/_default/frontend/_resources/favicon.ico" type="image/x-icon" />
    <link rel="stylesheet" type="text/css" href="<?php echo $basepath ?>/check/assets/styles/bootstrap.min.css" media="all"/>
    <link rel="stylesheet" type="text/css" href="<?php echo $basepath ?>/check/assets/styles/styles.css" media="all"/>

</head>
<body>

<div class="info">
    <img src="<?php echo $basepath ?>/check/assets/images/logo_installer.png" alt="Shopware Installer" class="logo"/>

    <div class="meta">
        <p>
            <strong>Shopware-Version:</strong> 4.0.3
        </p>

        <p>
            <strong>Check-Script-Version:</strong> 1.0.3
        </p>
    </div>
</div>
<div class="wrapper">
    <header>
        <ul class="navi-tabs clearfix">
              <li class="<?php if ($tab == "system") echo "active"; else { echo "disabled";}; ?>"><a href="#system"><?php echo $language["system_requirements"];?></a></li>
             </ul>
    </header>

    <section class="content">
        <div class="inner-container">
