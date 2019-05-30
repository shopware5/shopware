<?php $app->render('_header.php'); ?>

<h2><?= $t->t('requirements_header'); ?></h2>

<?php if ($error) {
    ?>
    <div class="alert alert-error">
        <span class="icon-cross huge"></span>
        <?= $t->t('requirements_error'); ?>
    </div>

<?php
} elseif ($phpVersionNotSupported) {
        ?>
    <div class="alert alert-warning">
        <i class="icon-info22 huge"></i> <?= $phpVersionNotSupported; ?>
    </div>

<?php
    } else {
        ?>
    <div class="alert alert-success">
        <span class="icon-checkmark huge"></span>
        <?= $t->t('requirements_success'); ?>
    </div>
<?php
    } ?>

<h4 class="<?php if (!$pathError): ?>success<?php endif; ?><?php if ($pathError): ?>error<?php endif; ?>"><?= $t->t('requirements_header_files'); ?> <small><a href="#permissions" data-shown="<?= $t->t('requirements_hide_all'); ?>" data-hidden="<?= $t->t('requirements_show_all'); ?>"><?= $t->t('requirements_show_all'); ?></a></small></h4>

<div id="permissions" class="<?php if (!$pathError): ?>is--hidden<?php endif; ?> <?php if ($pathError): ?> hide-successful<?php endif; ?>" <?php if ($pathError): ?>data-hide-successful="true"<?php endif; ?>>
    <p>
        <?= $t->t('requirements_files_info'); ?>
    </p>

    <table>
        <tbody>
            <?php foreach ($systemCheckResultsWritePermissions as $systemCheckResult): ?>
                <tr class="<?= $systemCheckResult['existsAndWriteable'] ? 'success' : 'error'; ?>">
                    <td><?= $systemCheckResult['name']; ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<h4 class="<?php if (!$systemError): ?>success<?php endif; ?><?php if ($systemError): ?>error<?php endif; ?>"><?= $t->t('requirements_header_system'); ?> <small><a href="#systemchecks" data-shown="<?= $t->t('requirements_hide_all'); ?>" data-hidden="<?= $t->t('requirements_show_all'); ?>"><?= $t->t('requirements_show_all'); ?></a></small></h4>

<div id="systemchecks" class="<?php if (!$systemError): ?>is--hidden<?php endif; ?> <?php if ($systemError): ?>hide-successful<?php endif; ?>" <?php if ($systemError): ?>data-hide-successful="true"<?php endif; ?>>
    <p>
        <?= $t->t('requirements_php_info'); ?>
    </p>

    <table>
        <thead>
            <tr>
                <th><?= $t->t('requirements_system_colcheck'); ?></th>
                <th><?= $t->t('requirements_system_colrequired'); ?></th>
                <th><?= $t->t('requirements_system_colfound'); ?></th>
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
                <td><?= $systemCheckResult['name']; ?></td>
                <td><?= $systemCheckResult['required']; ?></td>
                <td><?= empty($systemCheckResult['version']) ? '0' : $systemCheckResult['version']; ?></td>
            </tr>
            <?php if (!empty($systemCheckResult['notice'])): ?>
            <tr class="notice-text ">
                <td colspan="4">
                    <p><i class="icon-info22"></i> <?= $systemCheckResult['notice']; ?></p>
                </td>
            </tr>
            <?php endif; ?>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>

<form action="<?= $menuHelper->getCurrentUrl(); ?>" method="post">
    <div class="actions clearfix">
        <a href="<?= $menuHelper->getPreviousUrl(); ?>" class="btn btn-default btn-arrow-left"><?= $t->t('back'); ?></a>
        <button type="submit" class="btn btn-primary btn-arrow-right is--right" <?php if ($error): ?>disabled="disabled"<?php endif; ?>><?= $t->t('forward'); ?></button>
    </div>
</form>

<?php $app->render('_footer.php'); ?>
