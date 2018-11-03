<?php $app->render('_header.php', ['tab' => 'dbmigration']); ?>

<h2><?= $language['migration_header']; ?></h2>

<div style="display: none;" class="alert alert-error">
    &nbsp;
</div>

<div class="progress-container">
    <div class="progress">
        <div class="progress-bar"></div>
    </div>

    <div class="counter-text is--hidden">
        <strong class="counter-numbers">&nbsp;</strong>
        <p class="counter-content">
            &nbsp;
        </p>
    </div>

    <div class="progress-text is--hidden">
        <?= $language['migration_progress_text']; ?>
    </div>

    <div class="progress-actions actions clearfix">
        <input type="submit" id="start-ajax" class="btn btn-primary btn-arrow-right is--right" value="<?= $language['start']; ?>" />
    </div>
</div>

<form action="<?= $app->urlFor('cleanup'); ?>" method="get">
    <div class="actions clearfix">
        <input type="submit" class="btn btn-primary btn-arrow-right is--right is--hidden" id="forward-button" value="<?= $language['forward']; ?>"" />
    </div>
</form>

<?php $app->render('_footer.php'); ?>
