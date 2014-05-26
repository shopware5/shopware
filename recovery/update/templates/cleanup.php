<?php $app->render('header.php', array('tab' => 'cleanup')) ?>

<div id="start">
    <form action="<?php echo $app->urlFor('cleanup'); ?>" method="post">
        <div class="page-header">
            <h2><?php echo $language["cleanup_header"];?></h2>
        </div>

        <span class="help-block">
             <?php if ($error) { echo $language["cleanup_error"]; } else { echo $language["cleanup_disclaimer"]; } ?>
        </span>

        <table class="table table-striped">
            <tbody>
            <?php foreach ($cleanupList as $cleanupEntry): ?>
                <tr>
                    <td <?php if ($error) { echo 'class="error"'; }?>><?php echo $cleanupEntry ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>

        <div class="actions clearfix">
            <input type="submit" class="right primary" value="<?php echo $language["forward"];?>"" />
        </div>
    </form>
</div>

<?php $app->render('footer.php') ?>
