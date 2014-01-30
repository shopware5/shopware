<!doctype html>
<!--[if lt IE 7]> <html class="no-js lt-ie9 lt-ie8 lt-ie7" lang="en"> <![endif]-->
<!--[if IE 7]>    <html class="no-js lt-ie9 lt-ie8" lang="en"> <![endif]-->
<!--[if IE 8]>    <html class="no-js lt-ie9" lang="en"> <![endif]-->
<!--[if gt IE 8]><!-->
<html class="no-js" lang="en"> <!--<![endif]-->

<head>
    <meta charset="utf-8"/>
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1"/>

    <title>Shopware 4 - Installer</title>
    <link rel="shortcut icon" href="<?php echo $basepath ?>/templates/_default/frontend/_resources/favicon.ico" type="image/x-icon" />
    <link rel="stylesheet" type="text/css" href="<?php echo $basepath ?>/install/assets/styles/bootstrap.min.css" media="all"/>
    <link rel="stylesheet" type="text/css" href="<?php echo $basepath ?>/install/assets/styles/styles.css" media="all"/>

</head>
<body>

<div class="info">
    <img src="<?php echo $basepath ?>/install/assets/images/logo_installer.png" alt="Shopware Installer" class="logo"/>

    <div class="meta">
        <p>
            <?php echo $language['meta_text']; ?>
        </p>
    </div>
</div>
<div class="wrapper">
    <header>
        <ul class="navi-tabs clearfix">
            <li class="<?php if ($tab == "start") echo "active"; else { echo "disabled";}; ?>"><a href="#start"><?php echo $language["start_install"];?></a></li>
            <li class="<?php if ($tab == "system") echo "active"; else { echo "disabled";}; ?>"><a href="#system"><?php echo $language["system_requirements"];?></a></li>
            <li class="<?php if ($tab == "database") echo "active"; else { echo "disabled";}; ?>"><a href="#database"><?php echo $language["configure_db"];?></a></li>
            <li class="<?php if ($tab == "database_import") echo "active"; else { echo "disabled";}; ?>"><a href="#databaseimport"><?php echo $language["import_db"];?></a></li>
            <li class="<?php if ($tab == "licence") echo "active"; else { echo "disabled";}; ?>"><a href="#license"><?php echo $language["licence"];?></a></li>
            <li class="<?php if ($tab == "configuration") echo "active"; else { echo "disabled";}; ?>"><a href="#configuration"><?php echo $language["configuration"];?></a></li>
            <li class="<?php if ($tab == "done") echo "active"; else { echo "disabled";}; ?>"><a href="#done"><?php echo $language["done"];?></a></li>
        </ul>
    </header>

    <section class="content">
        <div class="inner-container">
