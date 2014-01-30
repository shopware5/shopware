<?php $app->render('header.php', array('tab' => 'done')) ?>

<div id="start">
    <div class="page-header">
        <h2><?php echo $language["step7_title"];?></h2>
    </div>
    <div class="alert alert-success">
        <?php echo $language["step7_info"];?>
        <a href="http://<?php echo $shop["domain"]."".$shop["basepath"] ?>" target="_blank"><?php echo $language["step7_frontend"];?></a><br /><br />
        <a href="http://<?php echo $shop["domain"]."".$shop["basepath"]."/backend" ?>" target="_blank"><?php echo $language["step7_backend"];?></a>
    </div>
</div>

<?php $app->render('footer.php') ?>
