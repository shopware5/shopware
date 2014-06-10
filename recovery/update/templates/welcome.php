<?php $app->render('header.php', array('tab' => 'start')) ?>

<div id="start">
    <div class="page-header">
        <h2><?php echo $language["start_update"];?></h2>
    </div>

    <form action="<?php echo $app->urlFor('checks', array()); ?>" method="POST">
        <input type="hidden" class="hidden-action" value="<?php echo $app->urlFor('welcome', array()); ?>" />

        <label><?php echo $language["select_language"];?></label>

        <select name="language" class="language-selection">
            <option value="de"<?php if ($selectedLanguage == "de") { ?>
                    selected="selected"<?php } ?>><?php echo $language["select_language_de"];?></option>
            <option value="en"<?php if ($selectedLanguage == "en") { ?>
                    selected="selected"<?php } ?>><?php echo $language["select_language_en"];?></option>
        </select>

        <div class="actions clearfix">
            <input type="submit" class="right primary" value="<?php echo $language["forward"];?>"" />
        </div>
    </form>
</div>

<?php $app->render('footer.php') ?>
