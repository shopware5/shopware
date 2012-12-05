<!doctype html>
<!--[if lt IE 7]> <html class="no-js lt-ie9 lt-ie8 lt-ie7" lang="en"> <![endif]-->
<!--[if IE 7]>    <html class="no-js lt-ie9 lt-ie8" lang="en"> <![endif]-->
<!--[if IE 8]>    <html class="no-js lt-ie9" lang="en"> <![endif]-->
<!--[if gt IE 8]><!-->
<html class="no-js" lang="en"> <!--<![endif]-->

<head>
    <meta charset="utf-8"/>
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1"/>
    <base href="<?php echo $app->getBasePath(); ?>/" />
    <title>Shopware 4 - Updater</title>
    <link rel="shortcut icon" href="assets/images/favicon.ico" type="image/x-icon" />
    <link rel="stylesheet" type="text/css" href="assets/styles/bootstrap.min.css" media="all"/>
    <link rel="stylesheet" type="text/css" href="assets/styles/styles.css" media="all"/>
    <script src="//code.jquery.com/jquery-1.8.0.min.js"></script>
    <script>window.jQuery || document.write('<script src="assets/javascript/jquery-1.8.0.min.js"><\/script>')</script>
    <script type="text/javascript" src="assets/javascript/jquery.installer.js"></script>
</head>
<body>

<div class="info">
    <img src="assets/images/logo_updater.png" alt="Shopware Updater" class="logo"/>

    <div class="meta">
        <p>
            <strong>Shopware Version:</strong> <?php echo Shopware_Update::UPDATE_VERSION; ?>
        </p>
        <p>
            <strong>Update Script Version:</strong> <?php echo Shopware_Update::VERSION; ?>
        </p>
    </div>
</div>
<div class="wrapper">
    <header>
        <ul class="navi-tabs clearfix">
            <li class="<?php if ($action == "index") echo "active"; else { echo "disabled"; }; ?>">
              <a href="<?php echo $app->urlFor('index', array()); ?>">Start / Login</a>
            </li>
            <li class="<?php if ($action == "system") echo "active"; else { echo "disabled";}; ?>">
              <a href="<?php echo $app->urlFor('system', array()); ?>">System / Kompatibilität</a>
            </li>
            <li class="<?php if ($action == "license") echo "active"; else { echo "disabled";}; ?>">
                <a href="<?php echo $app->urlFor('license'); ?>">Lizenz</a>
            </li>
            <li class="<?php if ($action == "main") echo "active"; else { echo "disabled";}; ?>">
                <a href="<?php echo $app->urlFor('main'); ?>">Update durchführen</a>
            </li>
            <li class="<?php if ($action == "restore") echo "active"; else { echo "disabled";}; ?>">
                <a href="<?php echo $app->urlFor('restore'); ?>">Backup einspielen</a>
            </li>
            <li class="<?php if ($action == "custom") echo "active"; else { echo "disabled";}; ?>">
                <a href="<?php echo $app->urlFor('custom'); ?>">Anpassungen übernehmen</a>
            </li>
            <li class="<?php if ($action == "finish") echo "active"; else { echo "disabled";}; ?>">
                <a href="">Abschluss</a>
            </li>
         </ul>
    </header>
    <section class="content">
        <div class="inner-container">