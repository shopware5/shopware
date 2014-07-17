<?php $app->render('_header.php', ['tab' => 'system']) ?>

<?php if ($error): ?>
    <div class="alert alert-error">
        <?= $language["step2_error"];?>
    </div>
<?php endif ?>

<h2><?= $language["step2_header_files"];?></h2>

<span class="help-block">
    <?= $language["step2_files_info"];?>
</span>
<table class="table table-striped">
    <thead>
    <tr>
        <th><?= $language["step2_tablefiles_colcheck"];?></th>
        <th><?= $language["step2_tablefiles_colstatus"];?></th>
    </tr>
    </thead>
    <tbody>
    <?php foreach ($systemCheckResultsWritePermissions as $systemCheckResult): ?>
        <?php $class = ($systemCheckResult["result"]) ? 'success' : 'error'; ?>
        <tr class="<?= $class; ?>">
            <td><?= $systemCheckResult["name"] ?></td>
            <td><?= $systemCheckResult["result"] == true ? '<i class="icon-ok-sign"></i>' : '<i class="icon-minus-sign"></i>' ?></td>
        </tr>
    <?php endforeach; ?>
    </tbody>
</table>

<form action="<?= $app->urlFor('checks') ?>" method="get">
    <div class="actions clearfix">
        <a href="<?= $app->urlFor('welcome'); ?>" class="btn btn-default btn-arrow-left"><?= $language["back"] ?></a>
        <button type="submit" class="btn btn-primary btn-arrow-right is--right"><?= $language["forward"] ?></button>
    </div>
</form>

<?php $app->render('_footer.php') ?>
