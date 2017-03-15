<?php $app->render('_header.php') ?>

<h2><?= $t->t('requirements_header') ?></h2>

<?php if ($error): ?>
    <div class="alert alert-error">
        <?= $t->t('requirements_error') ?>
    </div>
<?php endif ?>

<?php if (!$error): ?>
    <div class="alert alert-success">
        <span class="icon-checkmark huge"></span>
        <?= $t->t("requirements_success") ?><?php if (!$ioncube): ?>*<?php endif ?>
    </div>
<?php endif ?>
<?php if (!$ioncube): ?>
    <?= $t->t("requirements_ioncube") ?>
<?php endif ?>

<h4 <?php if (!$error): ?>class="success"<?php endif ?>><?= $t->t('requirements_header_files') ?> <small><a href="#permissions" data-shown="<?= $t->t("requirements_hide_all") ?>" data-hidden="<?= $t->t("requirements_show_all") ?>"><?= $t->t("requirements_show_all") ?></a></small></h4>

<div class="is--hidden" id="permissions">
    <p>
        <?= $t->t('requirements_files_info') ?>
    </p>

    <table>
        <tbody>
            <?php foreach ($systemCheckResultsWritePermissions as $systemCheckResult): ?>
                <tr class="<?= $systemCheckResult['existsAndWriteable'] ? 'success' : 'error'; ?>">
                    <td><?= $systemCheckResult['name'] ?></td>
                </tr>
            <?php endforeach ?>
        </tbody>
    </table>
</div>

<h4 <?php if (!$error): ?>class="success"<?php endif ?>><?= $t->t('requirements_header_system') ?> <small><a href="#systemchecks" data-shown="<?= $t->t("requirements_hide_all") ?>" data-hidden="<?= $t->t("requirements_show_all") ?>"><?= $t->t("requirements_show_all") ?></a></small></h4>

<div class="is--hidden" id="systemchecks">
    <p>
        <?= $t->t('requirements_php_info') ?>
    </p>

    <table>
        <thead>
            <tr>
                <th><?= $t->t('requirements_system_colcheck') ?></th>
                <th><?= $t->t('requirements_system_colrequired') ?></th>
                <th><?= $t->t('requirements_system_colfound') ?></th>
            </tr>
        </thead>

        <tbody>
        <?php foreach ($systemCheckResults as $systemCheckResult): ?>
            <?php
            if ($systemCheckResult['status'] == 'ok') {
                $class = 'success';
            } else {
                if ($systemCheckResult['status'] == 'error') {
                    $class = 'error';
                } else {
                    $class = 'warning';
                }
            }
            ?>
            <tr class="<?= $class; ?>">
                <td><?= $systemCheckResult['name'] ?></td>
                <td><?= $systemCheckResult['required'] ?></td>
                <td><?= empty($systemCheckResult['version']) ? '0' : $systemCheckResult['version'] ?></td>
            </tr>
            <?php if (!empty($systemCheckResult['notice'])): ?>
            <tr class="notice-text ">
                <td colspan="4">
                    <p><i class="icon-info-sign"></i> <?= $systemCheckResult['notice'] ?></p>
                </td>
            </tr>
            <?php endif ?>
        <?php endforeach ?>
        </tbody>
    </table>
</div>

<form action="<?= $menuHelper->getCurrentUrl() ?>" method="post">
    <div class="actions clearfix">
        <a href="<?= $menuHelper->getPreviousUrl() ?>" class="btn btn-default btn-arrow-left"><?= $t->t('back') ?></a>
        <button type="submit" class="btn btn-primary btn-arrow-right is--right"><?= $t->t('forward') ?></button>
    </div>
</form>

<?php $app->render('_footer.php') ?>
