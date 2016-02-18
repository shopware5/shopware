<?php $app->render('_header.php') ?>

<h2><?= $t->t("finish_header") ?></h2>

<div class="alert alert-success">
    <?= $t->t("finish_info") ?>
</div>

<?= $t->t("finish_message") ?>

<div class="actions clearfix">
    <a class="btn btn-default is--left" href="http://<?= $shop["domain"]."".$shop["basepath"] ?>" target="_blank"><?= $t->t("finish_frontend") ?></a>

    <a class="btn btn-default is--right" href="http://<?= $shop["domain"]."".$shop["basepath"]."/backend" ?>" target="_blank"><?= $t->t("finish_backend") ?></a>
</div>

<?php $app->render('_footer.php') ?>
