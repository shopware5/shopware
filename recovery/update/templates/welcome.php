<?php $app->render('_header.php', ['tab' => 'start']); ?>

<h2><?= $language['start_update']; ?></h2>

<form action="<?= $app->urlFor('checks', []); ?>" method="POST">
    <input type="hidden" class="hidden-action" value="<?= $app->urlFor('welcome', []); ?>" />

    <label><?= $language['select_language']; ?></label>

    <select name="language" class="language-selection">
        <option value="de"<?php if ($selectedLanguage == 'de') {
    ?>
                selected="selected"<?php
} ?>><?= $language['select_language_de']; ?></option>
        <option value="en"<?php if ($selectedLanguage == 'en') {
        ?>
                selected="selected"<?php
    } ?>><?= $language['select_language_en']; ?></option>
    </select>

    <div class="actions clearfix">
        <input type="submit" class="btn btn-primary btn-arrow-right is--right" value="<?= $language['forward']; ?>"" />
    </div>
</form>

<?php $app->render('_footer.php'); ?>
