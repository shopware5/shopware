<?php $this->display('header.php');?>
<div id="start">
    <div id="messages"></div>

<?php if(version_compare($app->config('updateVersion'), $app->config('currentVersion'), '>')) { ?>
<div class="alert alert-error">
    Achtung: Sie müssen erst das Update durchführen, bevor Sie die Anpassungen übernehmmen können.
</div>
<?php } ?>

<?php if(!empty($customs)) { ?>
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
                    <input type="checkbox" name="plugin[]" value="">
                </label>
            </th>
            <th>Name</th>
            <th>Aktiv</th>
            <th>Quelle</th>
            <th>grds. Kompatibel</th>
            <th>Store-Info</th>
            <th>Link</th>
        </tr>
        </thead>
        <tbody>
<?php foreach($customs as $plugin) { ?>
        <?php
        if (isset($plugin['compatibility']) && empty($plugin['compatibility'])) {
            $class = 'success';
        } elseif (!empty($plugin['updateVersion'] )) {
            $class = $plugin['updateVersion'] == 'default' ? 'success' : 'warning';
        } else {
            $class = 'error';
        }
        ?>
        <tr class="<?php echo $class; ?>">
            <td>
                <?php if(!empty($plugin['id']) && isset($plugin['compatibility']) && empty($plugin['compatibility'])) { ?>
                <label class="checkbox">
                    <input type="checkbox" name="plugin[]" value="<?php echo $plugin['id'];?>">
                </label>
                <?php } ?>
            </td>
            <td><?php echo $plugin['label'];?></td>
            <td><?php echo $plugin['active'] ? 'Ja' : 'Nein';?></td>
            <td><?php echo ucfirst($plugin['source']);?></td>
            <td>
                <?php if (!empty($plugin['compatibility'])) {?>
                Nein (<?php echo implode(', ', $plugin['compatibility']); ?>)
                <?php } elseif($plugin['source'] == 'Connector') { ?>
                Nein
                <?php } elseif(!isset($plugin['version'])) { ?>
                Nein
                <?php } else { ?>
                Ja
                <?php } ?>
            </td>
            <td>
                <?php if($plugin['source'] == 'Connector') { ?>
                Update bitte manuell überprüfen.
                <?php } elseif(!empty($plugin['updateVersion']) && $plugin['updateVersion'] == 'default') { ?>
                In der Standard-Installation enthalten.
                <?php } elseif (!empty($plugin['updateVersion'])) {?>
                Update ist im Store verfügbar.
                <?php } elseif(isset($plugin['updateVersion'])) { ?>
                Noch kein Update im Store verfügbar.
                <?php } ?>
            </td>
            <td>
                <?php if (isset($plugin['link'])) {?>
                <a href="<?php echo $plugin['link']; ?>" target="_blank">[link]</a>
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
                <select name="field[<?php echo $fieldIndex;?>]">
                    <option value="">Nicht übernehmen</option>
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