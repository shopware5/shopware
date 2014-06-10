<?php $app->render('header.php', array('tab' => 'done')) ?>

<div id="start">
    <div class="alert alert-success">
        <?php echo $language["done_info"];?>
    </div>

    <div class="alert alert-error">
        <?php echo $language["done_delete"];?>
    </div>
</div>

<?php $app->render('footer.php') ?>
