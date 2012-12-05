<?php $this->display('header.php');?>
<div id="start">
    <div class="page-header">
        <h2>Update abgeschlossen</h2>
    </div>
    <div class="alert alert-success">
        Das Update wurde erfolgreich abgeschlossen.

        Aus Sicherheitsgründen sollten Sie den Updater (/update) nun via FTP vom Server löschen.
        <br /><br />
        <a href="../" target="_blank"><?php echo $translation["step7_frontend"];?></a><br /><br />
        <a href="../backend" target="_blank"><?php echo $translation["step7_backend"];?></a>
    </div>
</div>
<?php $this->display('footer.php');?>