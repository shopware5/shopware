<!doctype html>
<!--[if lt IE 7]>
<html class="no-js lt-ie9 lt-ie8 lt-ie7" lang="en"> <![endif]-->
<!--[if IE 7]>
<html class="no-js lt-ie9 lt-ie8" lang="en"> <![endif]-->
<!--[if IE 8]>
<html class="no-js lt-ie9" lang="en"> <![endif]-->
<!--[if gt IE 8]><!-->
<html class="no-js" lang="en"> <!--<![endif]-->

<head>
    <meta charset="utf-8"/>
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1"/>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Shopware 5 - Installer</title>

    <link rel="shortcut icon" href="<?= $baseUrl; ?>../common/assets/images/favicon.ico" type="image/x-icon" />

    <link rel="stylesheet" type="text/css" href="<?= $baseUrl; ?>../common/assets/styles/reset.css" media="all"/>
    <link rel="stylesheet" type="text/css" href="<?= $baseUrl; ?>../common/assets/styles/icons.css" media="all"/>
    <link rel="stylesheet" type="text/css" href="<?= $baseUrl; ?>../common/assets/styles/style.css" media="all"/>

    <script>
        var shopwareTranslations = {
            'counterTextMigrations': '<?= $t->t('migration_counter_text_migrations'); ?>',
            'counterTextSnippets':   '<?= $t->t('migration_counter_text_snippets'); ?>',
            'updateSuccess':         '<?= $t->t('migration_update_success'); ?>'
        }
    </script>
</head>
<body>

<div class="page--wrap">

    <!-- Header -->
    <header class="header--main">
        <img class="header-logo" src="<?= $baseUrl; ?>assets/images/logo.png" alt="Shopware5 Installer" data-small="<?= $baseUrl; ?>assets/images/logo-small.png" data-normal="<?= $baseUrl; ?>assets/images/logo.png">
        <img src="<?= $baseUrl; ?>../common/assets/images/logo-sw5.png" alt="Shopware 5" class="header-shopware5-logo is--right">

        <div class="version--notice">
            <?= $t->t('version_text'); ?> <?= $version; ?>
        </div>
    </header>

    <div class="content--wrapper block-group">
        <?php $menuHelper->printMenu(); ?>
        <section class="content--main block">
