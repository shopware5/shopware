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

    <title><?= $language['title']; ?></title>

    <link rel="shortcut icon" href="<?= $baseUrl; ?>../common/assets/images/favicon.ico" type="image/x-icon" />

    <link rel="stylesheet" type="text/css" href="<?= $baseUrl; ?>../common/assets/styles/reset.css" media="all"/>
    <link rel="stylesheet" type="text/css" href="<?= $baseUrl; ?>../common/assets/styles/icons.css" media="all"/>
    <link rel="stylesheet" type="text/css" href="<?= $baseUrl; ?>../common/assets/styles/style.css?<?= $version; ?>" media="all"/>

    <script>
        var shopwareTranslations = {
            'counterTextUnpack':     '<?= $language['migration_counter_text_unpack']; ?>',
            'counterTextMigrations': '<?= $language['migration_counter_text_migrations']; ?>',
            'counterTextSnippets':   '<?= $language['migration_counter_text_snippets']; ?>',
            'updateSuccess':         '<?= $language['migration_update_success']; ?>'
        }
    </script>
</head>

<body class="<?= (!UPDATE_IS_MANUAL && $tab == 'dbmigration' || !UPDATE_IS_MANUAL && $tab == 'done') ? 'auto' : ''; ?>">
<div class="page--wrap">

    <!-- Header -->
    <header class="header--main">
        <img src="<?= $baseUrl; ?>assets/images/logo.png" alt="Shopware 5 Updater">
        <img src="<?= $baseUrl; ?>../common/assets/images/logo-sw5.png" alt="Shopware 5" class="header-shopware5-logo is--right">

        <div class="version--notice">
            <?= $version; ?>
        </div>
    </header>

    <div class="content--wrapper block-group">
        <!-- Navigation list -->
        <nav class="navigation--main block">
            <ul class="navigation--list">
                <li class="navigation--entry <?= ($tab == 'start') ? 'is--active' : ''; ?>">
                    <span class="navigation--link"><?= $language['tab_start']; ?></span>
                </li>

                <li class="navigation--entry  <?= ($tab == 'system') ? 'is--active' : ''; ?>">
                    <span class="navigation--link"><?= $language['tab_check']; ?></span>
                </li>

                <li class="navigation--entry  <?= ($tab == 'dbmigration') ? 'is--active' : ''; ?>">
                    <span class="navigation--link"><?= $language['tab_migration']; ?></span>
                </li>

                <li class="navigation--entry <?= ($tab == 'cleanup') ? 'is--active' : ''; ?>">
                    <span class="navigation--link"><?= $language['tab_cleanup']; ?></span>
                </li>

                <li class="navigation--entry <?= ($tab == 'done') ? 'is--active' : ''; ?>">
                    <span class="navigation--link"><?= $language['tab_done']; ?></span>
                </li>
            </ul>
        </nav>
        <section class="content--main block">
