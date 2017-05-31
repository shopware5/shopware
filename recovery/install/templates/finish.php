<?php $app->render('_header.php') ?>

<h2><?= $t->t('finish_header') ?></h2>

<div class="alert alert-success">
    <span class="icon-checkmark huge"></span>
    <h3 class="alert-heading"><?= $t->t('finish_info_heading') ?></h3>
    <?= $t->t('finish_info') ?>
</div>

<?= $t->t('finish_message') ?>

<div class="actions clearfix">
    <a class="btn btn-default is--right" href="http://<?= $shop['domain'] . '' . $shop['basepath'] ?>" target="_blank"><?= $t->t('finish_frontend') ?></a>
    <a class="btn btn-default is--right" href="<?= $selectedLanguage === 'de' ? 'http://community.shopware.com/_detail_930.html' : 'http://en.community.shopware.com/_detail_1195.html' ?>" target="_blank"><?= $t->t('finish_first_steps') ?></a>
    <a class="btn btn-primary btn-new-line is--right" href="http://<?= $shop['domain'] . '' . $shop['basepath'] . '/backend' ?>" target="_blank"><?= $t->t('finish_backend') ?></a>
</div>

<?php $app->render('_footer.php') ?>
