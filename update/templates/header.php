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
    <link rel="shortcut icon" href="assets/images/favicon.ico" type="image/x-icon" />
    <link rel="stylesheet" type="text/css" href="assets/styles/bootstrap.min.css" media="all"/>
    <link rel="stylesheet" type="text/css" href="assets/styles/styles.css" media="all"/>

</head>
<body>

<div class="info">
    <img src="assets/images/logo_installer.png" alt="Shopware Installer" class="logo"/>

    <div class="meta">
        <p>
            <strong>Shopware Version:</strong> 4.0.2
        </p>

        <p>
            <strong>Update Script Version:</strong> <?php echo Shopware_Install::VERSION; ?>
        </p>
    </div>
</div>
<div class="wrapper">
    <header>
        <ul class="navi-tabs clearfix">
          <li class="<?php if ($action == "index") echo "active"; else { echo "disabled"; }; ?>">
              <a href="<?php echo $app->urlFor('index', array()); ?>"><?php echo $translation["start_install"];?></a>
          </li>
          <li class="<?php if ($action == "system") echo "active"; else { echo "disabled";}; ?>">
              <a href="<?php echo $app->urlFor('system', array()); ?>"><?php echo $translation["system_requirements"];?></a>
          </li>
         </ul>
    </header>

    <section class="content">
        <div class="inner-container">
