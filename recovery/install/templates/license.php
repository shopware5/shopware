<?php $app->render('_header.php'); ?>

<h2><?= $t->t('license_agreement_header'); ?></h2>

<?php if ($error): ?>
    <div class="alert alert-error">
        <?= $t->t('license_agreement_error'); ?>
    </div>
<?php endif; ?>

<p>
    <?= $t->t('license_agreement_info'); ?>
</p>

<form action="<?= $menuHelper->getCurrentUrl(); ?>" method="post">
    <iframe id="license--agreement" class="license--agreement" src="<?= $tosUrl; ?>"></iframe>

    <p>
        <label>
            <input type="checkbox" name="tos"/>
            <?= $t->t('license_agreement_checkbox'); ?>
        </label>
    </p>

    <div class="actions clearfix">
        <a href="<?= $menuHelper->getPreviousUrl(); ?>" class="btn btn-default btn-arrow-left"><?= $t->t('back'); ?></a>
        <button type="submit" class="btn btn-primary btn-arrow-right is--right"><?= $t->t('forward'); ?></button>
    </div>
</form>

<?php $app->render('_footer.php'); ?>
