<?php $this->display('header.php'); ?>
<script type="text/javascript">
    jQuery(document).ready(function() {
        $('.page').hide();
        $('.page-header').prepend('<i>');
        $('.page-header i').addClass('icon-chevron-up');

        $next = $('.page-system');
        $next.next('.page').show();
        $next.find('i').removeClass('icon-chevron-down').addClass('icon-chevron-down');
    });
</script>
<div id="start">

<?php if ($error) { ?>
<div class="alert alert-error">
    <?php echo $translation["step2_error"];?>
</div>
<?php } ?>

<?php if(!empty($system)) { ?>
<div class="page-system page-header">
    <h2><?php echo $translation["system_requirements_header"];?></h2>
</div>
<div class="page">
<span class="help-block">
    <?php echo $translation["step2_php_info"];?>
</span>
<table id="table-system" class="table table-striped">
    <thead>
    <tr>
        <th><?php echo $translation["step2_system_colcheck"];?></th>
        <th><?php echo $translation["step2_system_colrequired"];?></th>
        <th><?php echo $translation["step2_system_colfound"];?></th>
        <th><?php echo $translation["step2_system_colstatus"];?></th>
    </tr>
    </thead>

    <tbody>
    <?php foreach ($system as $result) { ?>
        <?php
        if ($result["result"]) {
            $class = "success";
        } elseif ($result["error"]) {
            $class = "error";
        } else {
            $class = "warning";
        }
        ?>
    <tr class="<?php echo $class; ?>">
        <td><?php echo $result["name"] ?></td>
        <td><?php echo $result["required"] ?></td>
        <td><?php echo empty($result["version"]) ? "0" : $result["version"] ?></td>
        <td><?php echo $result["result"] ? '<i class="icon-ok-sign"></i>' : '<i class="icon-minus-sign"></i>' ?></td>
    </tr>
        <?php if (!empty($result["notice"])) { ?>
        <tr class="notice-text">
            <td colspan="4">
                <p><i class="icon-info-sign"></i> <?php echo $result["notice"] ?></p>
            </td>
        </tr>
            <?php } ?>
        <?php } ?>
    </tbody>
</table>
</div>
<?php } ?>

<?php if(!empty($customs)) { ?>
<div class="page-header page-restore">
    <h2>Plugins / Erweiterungen</h2>
</div>
<div class="page">
    <table class="table table-striped">
        <thead>
        <tr>
            <th>Name</th>
            <th>Aktiv</th>
            <th>Quelle</th>
            <th>Kompatibel</th>
            <th>Store-Link</th>
        </tr>
        </thead>
        <tbody>
            <?php foreach($customs as $plugin) { ?>
<?php
    if (isset($plugin['version']) && empty($plugin['compatibility'])) {
        $class = 'success';
    } elseif (isset($plugin['updateVersion'])) {
        $class = 'warning';
    } else {
        $class = 'error';
    }
?>
        <tr class="<?php echo $class; ?>">
            <td><?php echo $plugin['label'];?></td>
            <td><?php echo $plugin['active'] ? 'Ja' : 'Nein';?></td>
            <td><?php echo ucfirst($plugin['source']);?></td>
            <td>
                <?php if (!empty($plugin['updateVersion'])) {?>
                <?php if (!empty($plugin['id']) && empty($plugin['compatibility'])) {?>
                    Ja /
                    <?php } ?>
                Update im Store verfügbar
                <?php } elseif (!empty($plugin['compatibility'])) {?>
                Nein (<?php echo implode(', ', $plugin['compatibility']); ?>)
                <?php } elseif($plugin['source'] == 'Connector') { ?>
                Update bitte manuell überprüfen
                <?php } elseif(!isset($plugin['version'])) { ?>
                Nein / Kein Update im Store gefunden
                <?php } elseif($plugin['version'] == 'default') { ?>
                In der Standard-Installation enthalten
                <?php } else { ?>
                Ja
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
</div>
<?php } ?>
<?php
    $subject = "Update-Angebot von {$app->config('currentVersion')} auf {$app->config('updateVersion')}";
    $body = "";
?>
<div class="actions clearfix">
    <a href="<?php echo $app->urlFor('index'); ?>" class="secondary"><?php echo $translation["back"];?></a>
    <a id="link-next" href="<?php echo $app->urlFor('main'); ?>" class="right primary">
        Weiter
    </a>

</div>

</div>
<?php $this->display('footer.php'); ?>