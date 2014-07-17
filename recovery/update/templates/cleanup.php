<?php $app->render('_header.php', ['tab' => 'cleanup']) ?>

<h2><?= $language["cleanup_header"];?></h2>

<span class="help-block">
     <?php if ($error) {
    echo $language["cleanup_error"];
} else {
    echo $language["cleanup_disclaimer"];
} ?>
</span>

<table class="table table-striped">
    <tbody>
    <?php foreach ($cleanupList as $cleanupEntry): ?>
        <tr>
            <td <?php if ($error) {
    echo 'class="error"';
}?>><?= $cleanupEntry ?></td>
        </tr>
    <?php endforeach; ?>
    </tbody>
</table>

<form action="<?= $app->urlFor('cleanup'); ?>" method="post">
    <!-- http://support.microsoft.com/kb/2977636 -->
    <input type="hidden" name="ie11-dummy-payload" value="some-payload" />

    <div class="actions clearfix">
        <input type="submit" class="btn btn-primary btn-arrow-right is--right" value="<?= $language["forward"];?>"" />
    </div>
</form>

<?php $app->render('_footer.php') ?>
