<?php $app->render('header.php', array('tab' => 'done')) ?>

<div id="start">
    <h4 class="alert alert-success">
        <?php echo $language["done_info"];?>
    </h4>

	<p>
        <a class="big-button" href="<?php echo $app->urlFor('redirect', array('target' => 'frontend')); ?>" ><?php echo $language["done_frontend"];?></a>
        <a class="big-button" href="<?php echo $app->urlFor('redirect', array('target' => 'backend')); ?>"><?php echo $language["done_backend"];?></a>
	</p>
</div>

<?php $app->render('footer.php') ?>
