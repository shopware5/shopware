<?php $app->render('_header.php', ['tab' => 'done']) ?>

<h2><?= $language["done_title"];?></h2>

<div class="alert alert-success">
    <?= $language["done_info"];?>
</div>

<div class="alert alert-error">
    <?= $language["done_delete"];?>
</div>

<?php $app->render('_footer.php') ?>
