<?php $this->display('header.php'); ?>
<div id="start">
    <div class="page-header">
        <h2>Lizenz</h2>
    </div>

<?php if($error){ ?>
    <div class="alert alert-error">
    <?php switch($error) {
        case 'EMPTY':
            echo "Bitte geben Sie ein Lizenz-Schlüssel an.";
            break;
        case 'INVALID':
            echo "Der Lizenz-Schlüssel ist nicht gültig / vollständig.";
            break;
        case 'PRODUCT':
            echo "Der Lizenz-Schlüssel passt nicht zum Produkt.";
            break;
        case 'HOST':
            echo "Die Lizenz ist nicht gültig für diese Domain: " . $host;
            break;
        case 'OTHER':
            echo "Ein Fehler beim Installieren der Lizenz ist aufgetreten: <br>" . $message;
            break;
    } ?>
    </div>
<?php } ?>

    <form action="<?php echo $app->urlFor('license'); ?>" method="post">
        <input type="hidden" name="action" value="check" />

        <label class="radio">
        <input type="radio" name="product" id="license_ce" value="CE" <?php echo ($product == "CE") ? "checked=\"checked\"" : "" ?> onclick="$('#license').attr('disabled', true);">
            <?php echo $translation["step5_ce"];?>
        </label>
        <label class="radio">
        <input type="radio" name="product" id="license_pe" value="PE" <?php echo ($product == "PE") ? "checked=\"checked\"" : "" ?> onclick="$('#license').attr('disabled', false);">
            <?php echo $translation["step5_pe"];?>
        </label>

        <label class="radio">
         <input type="radio" name="product" id="license_eb" value="EB" <?php echo ($product == "EB") ? "checked=\"checked\"" : "" ?> onclick="$('#license').attr('disabled', false);">
             <?php echo $translation["step5_ee"];?>
         </label>

        <label class="radio">
         <input type="radio" name="product" id="license_ec" value="EC" <?php echo ($product == "EC") ? "checked=\"checked\"" : "" ?> onclick="$('#license').attr('disabled', false);">
             <?php echo $translation["step5_ec"];?>
         </label>

        <label for="license"><?php echo $translation["step5_license"];?></label>
        <textarea id="license" name="license" cols="" rows="4" style="width: 99%" <?php echo ($product == "CE") ? "disabled" : ""; ?>><?php echo htmlspecialchars($license) ?></textarea>
        <span class="help-block">
           <?php echo $translation["step5_info"];?>
        </span>

        <div class="actions clearfix">
            <a href="<?php echo $app->urlFor('custom', array()); ?>" class="secondary"><?php echo $translation["back"];?></a>
            <input type="submit" class="right primary" value="<?php echo $translation["forward"];?>" />
        </div>
    </form>
</div>
<?php $this->display('footer.php'); ?>