<?php $this->display('header.php');?>
<div id="start">

<?php if(!empty($plugins)) { ?>
<div class="page-header page-restore">
    <h2>Plugins / Erweiterungen übernehmen</h2>
</div>
<div class="page">
    <table class="table table-striped">
        <thead>
        <tr>
            <th class="check-all">
                <label class="checkbox">
                    <input type="checkbox" name="plugin">
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
                    <input type="checkbox" name="plugin" value="<?php echo $plugin['id'];?>">
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
        <a id="link-update" href="<?php echo $app->urlFor('action', array('action' => 'database')); ?>" class="right primary ajax-loading">
            Anpassungen übernehmen
        </a>
    </div>
</div>
<?php } ?>

<?php if(!empty($templates)) { ?>
<div class="page-header page-restore">
    <h2>Templates übernehmen</h2>
</div>
<div class="page">
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
                    <input type="checkbox" name="plugin" value="<?php echo $plugin['id'];?>">
                </label>
            </td>
            <td><?php echo $template;?></td>
        </tr>
            <?php } ?>
        </tbody>
    </table>
    <div class="actions clearfix">
        <a href="<?php echo $app->urlFor('action', array('action' => 'database')); ?>" class="right primary ajax-loading">
            Templates übernehmen
        </a>
    </div>
</div>
<?php } ?>

<?php if(!empty($fields)) { ?>
<div class="page-header page-restore">
    <h2>Konfigurator-Felder übernehmen</h2>
</div>
<div class="page">
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
        <a href="<?php echo $app->urlFor('action', array('action' => 'database')); ?>" class="right primary ajax-loading">
            Felder übernehmen
        </a>
    </div>
</div>
<?php } ?>

</div>
<?php $this->display('footer.php');?>