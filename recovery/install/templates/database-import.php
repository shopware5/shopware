<?php $app->render('_header.php'); ?>

<h2><?= $t->t('database-import_header'); ?></h2>

<?php if ($error): ?>
    <div class="alert alert-error">
        <?= $error; ?>
    </div>
<?php endif; ?>

<div style="display: none;" class="alert alert-error">
    &nbsp;
</div>

<div class="database-hint">
    <?= $t->t('database-import-hint'); ?>
</div>

<div class="progress-container">

    <div class="counter-container is--hidden">
        <p class="counter-text"><?= $t->t('database-import_progress'); ?></p><p class="counter-numbers">&nbsp;</p>

        <p class="counter-content is--hidden">
            &nbsp;
        </p>
    </div>

    <div class="progress">
        <div class="progress-bar" style="width: 0%"></div>
    </div>

    <div>
        <div class="install-buttons">
            <form action="<?= $menuHelper->getNextUrl(); ?>">
                <div class="actions clearfix">
                    <a href="<?= $menuHelper->getPreviousUrl(); ?>" id="back" class="btn btn-default btn-arrow-left"><?= $t->t('back'); ?></a>
                    <?php if ($hasSchema): ?>
                        <a href="<?= $menuHelper->getNextUrl(); ?>" id="skip-import" class="btn btn-default btn-arrow-right"><?= $t->t('database-import_skip_import'); ?></a>
                    <?php endif; ?>
                    <a href="<?= $menuHelper->getNextUrl(); ?>" class="btn btn-primary btn-arrow-right is--right is--hidden"><?= $t->t('forward'); ?></a>
                </div>
            </form>
        </div>

        <div class="progress-actions actions clearfix install-buttons is--right">
            <button id="start-ajax" class="btn btn-primary is--right"><?= $t->t('start_installation'); ?></button>
        </div>
    </div>
</div>

<?php $app->render('_footer.php'); ?>
