<?php $app->render('_header.php'); ?>

<h2><?= $t->t('edition_header'); ?></h2>

<?php if ($error): ?>
    <div class="alert alert-error">
        <?= $error; ?>
    </div>
<?php endif; ?>

<form action="<?= $menuHelper->getCurrentUrl(); ?>" method="post" class="edition--selection">

    <label>
        <input type="radio" name="c_edition" id="optionsRadios1" class="toggle removeElem" value="ce" <?= ($parameters['c_edition'] == 'ce' || empty($parameters['c_edition'])) ? 'checked="checked"' : ''; ?> data-href="#license" data-href-remove=".alert-error">
        <?= $t->t('edition_ce'); ?>
    </label>

    <label>
        <input type="radio" name="c_edition" id="optionsRadios2" class="toggle" value="cm"  <?= ($parameters['c_edition'] == 'cm') ? 'checked="checked"' : ''; ?> data-href="#license">
        <?= $t->t('edition_cm'); ?>
    </label>

    <div id="license"<?= ($parameters['c_edition'] === 'cm') ? '' : ' class="is--hidden"'; ?>>
        <label for="c_license" class="label--license"><?= $t->t('edition_license'); ?></label>
        <textarea class="license--agreement" id="c_license" name="c_license" rows="3"><?= $parameters['c_license']; ?></textarea>
    </div>

    <div class="actions clearfix">
        <a href="<?= $menuHelper->getPreviousUrl(); ?>" class="btn btn-default btn-arrow-left"><?= $t->t('back'); ?></a>
        <button type="submit" class="btn btn-primary btn-arrow-right is--right"><?= $t->t('forward'); ?></button>
    </div>
</form>

<?php $app->render('_footer.php'); ?>
