<?php $this->display('header.php');?>
<!-- Start page -->
<div id="start">
    <div class="page-header">
        <h2>Update starten</h2>
    </div>

    <div class="alert alert-success">
        <?php echo $translation["thank_you_message"];?>
    </div>

<?php if(isset($flash['loginError'])) { ?>
    <div class="alert alert-error">
        Ihr Login war nicht erfolgreich. Bitte überprüfen Sie Ihre Eingabe und probieren es erneut.
    </div>
<?php } ?>

    <form class="form-horizontal login" action="<?php echo $app->urlFor('login'); ?>" method="post">
        <div class="control-group">
            <label class="control-label" for="username">Benutzername:</label>
            <div class="controls">
                <input type="text" id="username" name="username">
            </div>
        </div>
        <div class="control-group">
            <label class="control-label" for="password">Passwort:</label>
            <div class="controls">
                <input type="password" id="password" name="password">
            </div>
        </div>
        <div class="control-group">
            <label class="control-label" for="language">Sprache:</label>
            <div class="controls">
                <select id="language" name="language">
                    <option value="de"<?php if ($language == "de") { ?> selected="selected"<?php } ?>>
                        <?php echo $translation["select_language_de"];?>
                    </option>
                    <option value="en"<?php if ($language == "en") { ?> selected="selected"<?php } ?>>
                        <?php echo $translation["select_language_en"];?>
                    </option>
                </select>
            </div>
        </div>
        <div class="actions clearfix">
            <input type="submit" class="right primary" value="<?php echo $translation["forward"];?>" />
        </div>
    </form>

</div>
<?php $this->display('footer.php');?>