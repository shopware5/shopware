<?php $app->render('header.php', array('tab' => 'licence')) ?>

<div id="start">
    <div class="page-header">
        <h2><?php echo $language["step5_header"];?></h2>
    </div>

    <?php if ($error): ?>
        <div class="alert alert-error">
            <?php echo $error ?>
        </div>
    <?php endif ?>

    <form action="<?php echo $app->urlFor('step5', array()); ?>" method="post">
        <input type="hidden" name="action" value="check" />

        <label class="radio">
        <input type="radio" name="c_edition" id="optionsRadios1" value="ce" <?php echo ($parameters["c_edition"] == "ce" || empty($parameters["c_edition"])) ? "checked=\"checked\"" : "" ?> onclick="$('#c_license').attr ( 'disabled' , true );">
            <?php echo $language["step5_ce"];?>
        </label>
        <label class="radio">
        <input type="radio" name="c_edition" id="optionsRadios2" value="pe"  <?php echo ($parameters["c_edition"] == "pe") ? "checked=\"checked\"" : "" ?> onclick="$('#c_license').attr ( 'disabled' , false );">
            <?php echo $language["step5_pe"];?>
        </label>

        <label class="radio">
         <input type="radio" name="c_edition" id="optionsRadios3" value="eb"  <?php echo ($parameters["c_edition"] == "eb") ? "checked=\"checked\"" : "" ?> onclick="$('#c_license').attr ( 'disabled' , false );">
             <?php echo $language["step5_ee"];?>
         </label>

        <label class="radio">
         <input type="radio" name="c_edition" id="optionsRadios4" value="ec"  <?php echo ($parameters["c_edition"] == "ec") ? "checked=\"checked\"" : "" ?> onclick="$('#c_license').attr ( 'disabled' , false );">
             <?php echo $language["step5_ec"];?>
         </label>

        <label><?php echo $language["step5_license"];?></label>
        <textarea id="c_license" name="c_license" rows="3" <?php echo ($parameters["c_edition"]=="ce" || !isset($parameters["c_edition"])) ? "disabled" : ""?>><?php echo $parameters["c_license"] ?></textarea>
        <span class="help-block">
           <?php echo $language["step5_info"];?>
        </span>

        <div class="actions clearfix">
            <a href="<?php echo $app->urlFor('step4', array()); ?>" class="secondary"><?php echo $language["back"];?></a>
            <input type="submit" class="right primary" value="<?php echo $language["forward"];?>"" />
        </div>
    </form>
</div>

<?php $app->render('footer.php') ?>
