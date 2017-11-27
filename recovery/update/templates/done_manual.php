<?php $app->render('_header.php', ['tab' => 'done']); ?>

<h2><?= $language['done_title']; ?></h2>

<div class="alert alert-success">
    <?= $language['done_info']; ?>
</div>

<?php if ($changedTheme): ?>
    <div class="alert alert-warning">
        <?= $language['done_template_changed']; ?>
    </div>
<?php endif; ?>

<div class="alert alert-error">
    <?= $language['done_delete']; ?>
</div>

<?php $app->render('_footer.php'); ?>
