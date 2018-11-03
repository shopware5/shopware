<?php $app->render('_header.php'); ?>

<h2><?= $t->t('language-selection_header'); ?></h2>

<?= $t->t('language-selection_welcome_message'); ?>

<form action="<?= $menuHelper->getNextUrl(); ?>" method="get">
    <input type="hidden" class="hidden-action" value="<?= $menuHelper->getCurrentUrl(); ?>" />

    <p>
        <label for="language"><?= $t->t('language-selection_select_language'); ?></label>

        <?= $t->t('language-selection_info_message'); ?>
    </p>
    <select id="language" name="language" class="language-selection">
        <?php foreach ($languages as $language): ?>
            <option value="<?= $language; ?>" <?= ($selectedLanguage == $language) ? 'selected' : ''; ?>>
                <?= $t->t('select_language_' . $language); ?>
            </option>
        <?php endforeach; ?>
    </select>

    <div class="actions clearfix">
        <button type="submit" class="btn btn-primary btn-arrow-right is--right"><?= $t->t('forward'); ?></button>
    </div>
</form>

<?php $app->render('_footer.php'); ?>
