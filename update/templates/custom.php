<?php $this->display('header.php');?>
<div id="start">
    <div id="messages"></div>

<?php if(version_compare($app->config('updateVersion'), $app->config('currentVersion'), '>')) { ?>
<div class="alert alert-error">
    Achtung: Sie müssen erst das Update durchführen, bevor Sie die Anpassungen übernehmmen können.
</div>
<?php } ?>

<?php if(!empty($plugins)) { ?>
<div class="page-header page-restore">
    <h2>Plugins / Erweiterungen übernehmen</h2>
</div>
<div class="page">
<form class="ajax-loading" action="<?php echo $app->urlFor('action', array('action' => 'updatePlugins')); ?>">
    <table class="table table-striped">
        <thead>
        <tr>
            <th class="check-all">
                <label class="checkbox">
                    <input type="checkbox" name="plugin[]">
                </label>
            </th>
            <th>Name</th>
            <th>Aktiv</th>
            <th>Quelle</th>
            <th>Kompatibel</th>
        </tr>
        </thead>
        <tbody>
            <?php foreach($plugins as $plugin) { ?>
        <tr class="<?php echo empty($plugin['compatibility']) ? 'success' : 'warning'; ?>">
            <td>
                <label class="checkbox">
                    <input type="checkbox" name="plugin[]" value="<?php echo $plugin['id'];?>">
                </label>
            </td>
            <td><?php echo $plugin['label'];?></td>
            <td><?php echo $plugin['active'] ? 'Ja' : 'Nein';?></td>
            <td><?php echo ucfirst($plugin['source']);?></td>
            <td class="success">
                <?php if (!empty($plugin['compatibility'])) {?>
                    Nein (<?php echo implode(', ', $plugin['compatibility']); ?>)
                <?php } else { ?>
                    Ja
                <?php } ?>
            </td>
        </tr>
            <?php } ?>
        </tbody>
    </table>
    <div class="actions clearfix">
        <input type="submit" class="right primary" value="Anpassungen übernehmen" />
    </div>
</form>
</div>
<?php } ?>

<?php if(!empty($templates)) { ?>
<div class="page-header page-restore">
    <h2>Templates übernehmen</h2>
</div>
<div class="page">
<form class="ajax-loading" action="<?php echo $app->urlFor('action', array('action' => 'updateTemplates')); ?>">
    <table class=" table table-striped">
        <thead>
        <tr>
            <th class="check-all">
                <label class="checkbox">
                    <input type="checkbox">
                </label>
            </th>
            <th>Name</th>
        </tr>
        </thead>
        <tbody>
            <?php foreach($templates as $template) { ?>
        <tr>
            <td>
                <label class="checkbox">
                    <input type="checkbox" name="template" value="<?php echo $plugin['id'];?>">
                </label>
            </td>
            <td><?php echo $template;?></td>
        </tr>
            <?php } ?>
        </tbody>
    </table>
    <div class="actions clearfix">
        <input type="submit" class="right primary" value="Templates übernehmen" />
    </div>
</form>
</div>
<?php } ?>

<?php if(!empty($fields) && !empty($targetFields)) { ?>
<div class="page-header page-restore">
    <h2>Konfigurator-Felder übernehmen</h2>
</div>
<div class="page">
<form class="ajax-loading" action="<?php echo $app->urlFor('action', array('action' => 'updateFields')); ?>">
    <table class=" table table-striped">
        <thead>
        <tr>
            <th>Konfigurator-Feld</th>
            <th>Varianten-Feld</th>
        </tr>
        </thead>
        <tbody>
            <?php foreach($fields as $fieldIndex => $field) { ?>
        <tr>
            <td><?php echo $field;?></td>
            <td>
                <select name="targetField[<?php echo $fieldIndex;?>]">
                <?php foreach($targetFields as $targetName => $targetField) { ?>
                    <option value="<?php echo $targetName;?>"><?php echo $targetField;?></option>
                <?php } ?>
                </select>
            </td>
        </tr>
            <?php } ?>
        </tbody>
    </table>
    <div class="actions clearfix">
        <input type="submit" class="right primary" value="Felder übernehmen" />
    </div>
</form>
</div>
<?php } ?>

<div class="actions clearfix">
    <a href="<?php echo $app->urlFor('main'); ?>" class="secondary"><?php echo $translation["back"];?></a>
    <a id="link-next" href="<?php echo $app->urlFor('finish'); ?>" class="right primary"><?php echo $translation["forward"];?></a>
</div>

</div>
<?php $this->display('footer.php');?>