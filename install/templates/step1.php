<?php
if (!defined("installer")) {
    exit;
}
?>
<!-- Start page -->
<div id="start">
    <div class="page-header">
        <h2><?php echo $language["start_install"];?></h2>
    </div>

    <div class="alert alert-success">
        <?php echo $language["thank_you_message"];?>
    </div>

    <form action="<?php echo $app->urlFor('step2', array()); ?>" method="post">
        <input type="hidden" name="tab" value="system" />
        <input type="hidden" class="hidden-action" value="<?php echo $app->urlFor('step1', array()); ?>" />
        <label><?php echo $language["select_language"];?></label>

        <select name="language" class="language-selection">
            <option value="0"><?php echo $language["select_language_choose"];?></option>
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
