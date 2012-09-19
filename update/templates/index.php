<?php $this->display('header.php');?>
<!-- Start page -->
<div id="start">
    <div class="page-header">
        <h2><?php echo $translation["start_install"];?></h2>
    </div>

    <div class="alert alert-success">
        <?php echo $translation["thank_you_message"];?>
    </div>

    <form action="<?php echo $app->urlFor('system'); ?>" method="post">
        <label for="language"><?php echo $translation["select_language"];?></label>

        <select id="language" name="language" class="language-selection">
            <option value="0"><?php echo $translation["select_language_choose"];?></option>
            <option value="de"<?php if ($language == "de") { ?>
                    selected="selected"<?php } ?>><?php echo $translation["select_language_de"];?></option>
            <option value="en"<?php if ($language == "en") { ?>
                    selected="selected"<?php } ?>><?php echo $translation["select_language_en"];?></option>
        </select>

        <div class="actions clearfix">

            <input type="submit" class="right primary" value="<?php echo $translation["forward"];?>" />
        </div>
    </form>
</div>
<?php $this->display('footer.php');?>