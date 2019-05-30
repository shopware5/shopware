<?php $app->render('_header.php', ['tab' => 'start']); ?>

<h2><?= $language['noaccess_title']; ?></h2>

<div class="alert alert-error">
    <?php printf($language['noaccess_info'], $clientIp, $filePath); ?>
</div>

<?php $app->render('_footer.php'); ?>
