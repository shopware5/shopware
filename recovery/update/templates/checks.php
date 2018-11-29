<?php $app->render('_header.php', ['tab' => 'system']); ?>

<h2><?= $language['step2_header_files']; ?></h2>

<?php if ($error): ?>
    <div class="alert alert-error">
        <?= $language['step2_error']; ?>
    </div>
<?php endif; ?>

<p>
    <?= $language['step2_files_info']; ?>
</p>

<table class="table table-striped">
    <tbody>
    <?php foreach ($systemCheckResultsWritePermissions as $systemCheckResult): ?>
        <?php $class = ($systemCheckResult['result']) ? 'success' : 'error'; ?>
        <tr class="<?= $class; ?>">
            <td><?= $systemCheckResult['name']; ?></td>
        </tr>
    <?php endforeach; ?>
    </tbody>
</table>

<form action="<?= $app->urlFor('checks'); ?>" method="get">
    <div class="actions clearfix">
        <a href="<?= $app->urlFor('welcome'); ?>" class="btn btn-default btn-arrow-left"><?= $language['back']; ?></a>
        <button type="submit" class="btn btn-primary btn-arrow-right is--right"><?= $language['forward']; ?></button>
    </div>
</form>

<?php $app->render('_footer.php'); ?>
