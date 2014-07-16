<?php $app->render('header.php', array('tab' => 'database_import')) ?>

<div id="start" class="step4">
    <div class="page-header">
        <h2><?php echo $language["step4_header"];?></h2>
    </div>

    <?php if ($error): ?>
        <div class="alert alert-error">
            <?php echo $error ?>
        </div>
    <?php endif ?>

    <div style="display: none;" class="alert alert-error">
        &nbsp;
    </div>

    <div class="progress-container">
        <div class="progress progress-info progress-striped">
            <div class="bar" style="width: 0%"></div>
        </div>

        <div class="counter-text hidden">
            <strong class="counter-numbers">&nbsp;</strong>
            <p class="counter-content">
                &nbsp;
            </p>
        </div>

        <div class="progress-text">
            <?php echo $language["migration_progress_text"];?>
        </div>

        <div class="progress-actions actions clearfix">
            <input type="submit" id="start-ajax" class="right primary" value="<?php echo $language["start"];?>" />
        </div>
    </div>

    <form action="<?php echo $app->urlFor('step4', array()); ?>" method="post">
        <input type="hidden" name="action" value="check" />

        <div class="actions clearfix">
            <a href="<?php echo $app->urlFor('step3', array()); ?>" class="secondary"><?php echo $language["back"];?></a>

            <a href="<?php echo $app->urlFor('step5'); ?>" class="rigth secondary"><?php echo $language["step4_skip_import"];?></a>
            <input type="submit" class="right primary invisible" value="<?php echo $language["forward"];?>" />
        </div>
    </form>
</div>

<?php $app->render('footer.php') ?>
