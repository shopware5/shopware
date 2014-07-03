<!doctype html>
<!--[if lt IE 7]> <html class="no-js lt-ie9 lt-ie8 lt-ie7" lang="en"> <![endif]-->
<!--[if IE 7]>    <html class="no-js lt-ie9 lt-ie8" lang="en"> <![endif]-->
<!--[if IE 8]>    <html class="no-js lt-ie9" lang="en"> <![endif]-->
<!--[if gt IE 8]><!-->
<html class="no-js" lang="en"> <!--<![endif]-->

<head>
    <meta charset="utf-8"/>
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1"/>

    <title><?php echo $language['title']; ?></title>
    <link rel="shortcut icon" href="<?php echo $baseUrl ?>../common/assets/images/favicon.ico" type="image/x-icon" />
    <link rel="stylesheet" type="text/css" href="<?php echo $baseUrl ?>../common/assets/styles/bootstrap.min.css" media="all"/>
    <link rel="stylesheet" type="text/css" href="<?php echo $baseUrl ?>../common/assets/styles/styles.css" media="all"/>
    <link rel="stylesheet" type="text/css" href="<?php echo $baseUrl ?>assets/styles/styles.css" media="all"/>
    <script>
        var shopwareTranslations = {
            'counterTextUnpack':     '<?php echo $language['migration_counter_text_unpack']; ?>',
            'counterTextMigrations': '<?php echo $language['migration_counter_text_migrations']; ?>',
            'counterTextSnippets':   '<?php echo $language['migration_counter_text_snippets']; ?>',
            'updateSuccess':         '<?php echo $language['migration_update_success']; ?>'
        }
    </script>
</head>
<body class="<?php echo (!UPDATE_IS_MANUAL && $tab == "dbmigration" || !UPDATE_IS_MANUAL && $tab == "done") ? 'auto' : '' ?>">
<div class="info">
    <img src="<?php echo $baseUrl ?>assets/images/logo_updater.png" alt="<?php echo $language['title']; ?>" class="logo"/>
</div>

<div class="wrapper">
    <header>
        <ul class="navi-tabs clearfix">
            <li class="<?php if ($tab == "start") echo "active"; else { echo "disabled";}; ?>"><?php echo $language["tab_start"];?></li>
            <li class="<?php if ($tab == "system") echo "active"; else { echo "disabled";}; ?>"><?php echo $language["tab_check"];?></li>
            <li class="<?php if ($tab == "dbmigration") echo "active"; else { echo "disabled";}; ?>"><?php echo $language["tab_migration"];?></li>
            <li class="<?php if ($tab == "cleanup") echo "active"; else { echo "disabled";}; ?>"><?php echo $language["tab_cleanup"];?></li>
            <li class="<?php if ($tab == "done") echo "active"; else { echo "disabled";}; ?>"><?php echo $language["tab_done"];?></li>
        </ul>
    </header>
    <section class="content">
        <div class="inner-container">
