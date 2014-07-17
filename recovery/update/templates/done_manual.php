<?php $app->render('_header.php', ['tab' => 'done']) ?>

<div class="alert alert-success">
    <?= $language["done_info"];?>
</div>

<div class="alert alert-error">
    <?= $language["done_delete"];?>
</div>

<?php $app->render('_footer.php') ?>
