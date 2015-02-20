<?php $app->render('_header.php', ['tab' => 'done']) ?>

<h2><?= $language["done_title"];?></h2>

<h4 class="alert alert-success">
    <?= $language["done_info"];?>
</h4>

<p>
    <a class="big-button" href="<?= $app->urlFor('redirect', ['target' => 'frontend']); ?>" ><?= $language["done_frontend"];?></a>
    <a class="big-button" href="<?= $app->urlFor('redirect', ['target' => 'backend']); ?>"><?= $language["done_backend"];?></a>
</p>

<?php $app->render('_footer.php') ?>
