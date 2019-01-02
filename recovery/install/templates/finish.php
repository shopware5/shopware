<?php $app->render('_header.php'); ?>

<h2><?= $t->t('finish_header'); ?></h2>

<div class="alert alert-success">
    <span class="icon-checkmark huge"></span>
    <h3 class="alert-heading"><?= $t->t('finish_info_heading'); ?></h3>
    <?= $t->t('finish_info'); ?>
</div>

<?= $t->t('finish_message'); ?>

<div class="actions clearfix">
    <a class="btn btn-default is--right" href="<?php echo $url; ?>" target="_blank"><?= $t->t('finish_frontend'); ?></a>
    <a class="btn btn-default is--right" href="<?= $selectedLanguage === 'de' ? 'https://docs.shopware.com/de/shopware-5-de/erste-schritte/erste-schritte-in-shopware' : 'https://docs.shopware.com/en/shopware-5-en/first-steps/first-steps-in-shopware'; ?>" target="_blank"><?= $t->t('finish_first_steps'); ?></a>
    <a class="btn btn-primary btn-new-line is--right" href="<?php echo $url; ?>/backend" target="_blank"><?= $t->t('finish_backend'); ?></a>
</div>

<?php $app->render('_footer.php'); ?>
