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

<div class="actions clearfix">
    <a class="btn btn-primary is--left" href="<?= $app->urlFor('redirect', ['target' => 'frontend']); ?>" ><?= $language['done_frontend']; ?></a>
    <a class="btn btn-primary is--right" href="<?= $app->urlFor('redirect', ['target' => 'backend']); ?>"><?= $language['done_backend']; ?></a>
</div>

<?php $app->render('_footer.php'); ?>
