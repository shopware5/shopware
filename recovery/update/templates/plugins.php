<?php $app->render('_header.php', ['tab' => 'start']); ?>

<table>
    <thead>
    <tr>
        <th>asdf</th>
        <th>asdfasdf</th>
        <th>asdfasdf</th>
    </tr>
    </thead>

    <tbody>
    <?php foreach ($plugins as $plugin): ?>
        <tr>
            <td><?= $plugin['plugin_name']; ?></td>
            <td><?= $plugin['in_store']; ?></td>
            <td><?= $plugin['compatible']; ?></td>
        </tr>
    <?php endforeach; ?>
    </tbody>
</table>

<form action="<?= $app->urlFor('dbmigration'); ?>" method="get">
    <div class="actions clearfix">
        <a href="<?= $app->urlFor('checks'); ?>" class="btn btn-default btn-arrow-left"><?= $language['back']; ?></a>
        <button type="submit" class="btn btn-primary btn-arrow-right is--right"><?= $language['forward']; ?></button>
    </div>
</form>

<?php $app->render('_footer.php'); ?>
