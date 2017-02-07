<?php $app->render('_header.php') ?>

<h2><?= $t->t('database-import_header') ?></h2>

<?php if ($error): ?>
    <div class="alert alert-error">
        <?= $error ?>
    </div>
<?php endif ?>

<div style="display: none;" class="alert alert-error">
    &nbsp;
</div>

<div class="progress-container">
    <div class="progress">
        <div class="progress-bar" style="width: 0%"></div>
    </div>

    <div class="counter-text is--hidden">
        <strong class="counter-numbers">&nbsp;</strong>
        <p class="counter-content">
            &nbsp;
        </p>
    </div>

    <div class="progress-text">
        <?= $t->t('database-import_progress_text') ?>
    </div>

    <div class="progress-actions actions clearfix">
        <button id="start-ajax" class="btn btn-primary btn-database-right is--right"><?= $t->t('start') ?></button>
    </div>
</div>

<form action="<?= $menuHelper->getNextUrl() ?>">
    <div class="actions clearfix">
        <a href="<?= $menuHelper->getPreviousUrl() ?>" class="btn btn-default btn-arrow-left"><?= $t->t('back') ?></a>
        <?php if ($hasSchema): ?>
        <a href="<?= $menuHelper->getNextUrl() ?>" class="btn btn-default btn-arrow-right"><?= $t->t('database-import_skip_import') ?></a>
        <?php endif; ?>
        <button type="submit" class="btn btn-primary btn-arrow-right is--right is--hidden"><?= $t->t('forward') ?></button>
    </div>
</form>

<?php $app->render('_footer.php') ?>
