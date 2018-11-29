<?php $app->render('_header.php', ['tab' => 'cleanup']); ?>

<h2><?= $language['cleanup_header']; ?></h2>

<span class="help-block">
     <?php if ($error) {
    echo $language['cleanup_error'];
} else {
    echo $language['cleanup_disclaimer'];
} ?>
</span>

<table class="table table-striped">
    <tbody>
    <?php foreach ($cleanupList as $cleanupEntry): ?>
        <tr>
            <td <?php if ($error) {
    echo 'class="error"';
} ?>><?= $cleanupEntry; ?></td>
        </tr>
    <?php endforeach; ?>
    </tbody>
</table>

<form name="cleanupForm" action="<?= $app->urlFor('cleanup'); ?>" method="post">
    <!-- http://support.microsoft.com/kb/2977636 -->
    <input type="hidden" name="ie11-dummy-payload" value="some-payload"/>

    <div class="actions clearfix">
        <div class="fileCounterContainer is--left">
            <div class="counter">0</div>
            <div class="description"><?= $language['deleted_files']; ?></div>
        </div>

        <div class="clearCacheSpinner is--right">
            <i class="loading-indicator"></i>
        </div>

        <input type="button" class="btn btn-primary btn-arrow-right is--right startCleanUpProcess"
               data-clearCacheUrl="<?= $app->urlFor('clearCache'); ?>"
               value="<?= $language['forward']; ?>"/>
     </div>

    <div class="error-message-container alert alert-error">
        <p><?= $language['cache_clear_error']; ?></p>
    </div>
</form>

<?php $app->render('_footer.php'); ?>