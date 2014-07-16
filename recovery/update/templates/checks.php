<?php $app->render('header.php', array('tab' => 'system')) ?>

<div id="start">
    <?php if ($error): ?>
        <div class="alert alert-error">
            <?php echo $language["step2_error"];?>
        </div>
    <?php endif ?>

    <form action="<?php echo $app->urlFor('checks', array()); ?>" method="get">
        <div class="page-header">
            <h2><?php echo $language["step2_header_files"];?></h2>
        </div>
        <span class="help-block">
            <?php echo $language["step2_files_info"];?>
        </span>
        <table class="table table-striped">
            <thead>
            <tr>
                <th><?php echo $language["step2_tablefiles_colcheck"];?></th>
                <th><?php echo $language["step2_tablefiles_colstatus"];?></th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($systemCheckResultsWritePermissions as $systemCheckResult): ?>
                <?php $class = ($systemCheckResult["result"]) ? 'success' : 'error'; ?>
                <tr class="<?php echo $class; ?>">
                    <td><?php echo $systemCheckResult["name"] ?></td>
                    <td><?php echo $systemCheckResult["result"] == true ? '<i class="icon-ok-sign"></i>' : '<i class="icon-minus-sign"></i>' ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>

        <div class="actions clearfix">
            <a href="<?php echo $app->urlFor('welcome', array()); ?>" class="secondary"><?php echo $language["back"];?></a>
            <input type="hidden" name="action" value="true" />
            <input type="submit" class="right primary" value="<?php echo $language["forward"];?>"" />
        </div>
    </form>
</div>

<?php $app->render('footer.php') ?>
