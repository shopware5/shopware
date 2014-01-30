<?php $app->render('header.php', array('tab' => 'database_import')) ?>

<div id="start" class="step4">
    <div class="page-header">
        <h2><?php echo $language["step4_header"];?></h2>
    </div>

    <?php
     if ($error == true) {
     ?>
     <div class="alert alert-error">
         <?php echo $error ?>
    </div>
    <?php
     }
    ?>

    <form action="<?php echo $app->urlFor('step4', array()); ?>" method="post">
        <input type="hidden" name="action" value="check" />
        <label class="checkbox">
            <input type="checkbox" value="1" name="c_skip" <?php echo !empty($parameters["c_skip"]) ? "checked=\"checked\"" : ""?> > <?php echo $language["step4_skip_import"];?>
        </label>

        <span class="help-block">
            <?php echo $language["step4_skip_info"];?>
        </span>

        <div class="actions clearfix">
            <a href="<?php echo $app->urlFor('step3', array()); ?>" class="secondary"><?php echo $language["back"];?></a>
            <input type="submit" class="right primary" value="<?php echo $language["forward"];?>" data-loading="true" data-loading-text="<?php echo $language["step_3_loading"]; ?>" />
        </div>
    </form>
</div>

<?php $app->render('footer.php') ?>
