<?php $this->display('header.php');?>
<script type="text/javascript">
    jQuery(document).ready(function() {
        $('.ajax-loading').live('click', function(event) {
            event.preventDefault();
            var me = $(this);
            if(me.hasClass('disabled')) {
                return;
            }
            me.addClass('disabled');
            $.loading(me.text());
            $.ajaxLoading($(this).attr('href'));
        });

        $.ajaxLoading = function(url, last) {
            $.ajax({
                url: url,
                type: 'POST',
                dataType: 'json',
                data: last,
                success: function(result) {
                    if(result && result.next) {
                        $.ajaxLoading(url, result);
                    } else {
                        $.removeLoading();
                    }
                    if(!last) {
                        $('#messages').empty();
                    }
                    if(!result || !result.success) {
                        $('<div class="alert alert-error"></div>')
                                .html(result.message).appendTo('#messages');
                    } else if(result.message) {
                        var progress = $('.loading-mask .progress');
                        if(!progress.length) {
                            progress = $('<div class="progress progress-striped active">')
                                     . append('<div class="bar">')
                                     . append('<div class="message">')
                                     . appendTo('.loading-mask');
                        }
                        if(result.warning) {
                            $('<div class="alert alert-warning"></div>')
                                    .html(result.warning).appendTo('#messages');
                        }
                        if(result.progress) {
                            progress.show();
                            progress.children('.bar').css('width', '' + (result.progress * 100) + '%');
                            progress.children('.message').text(result.message);
                        } else {
                            progress.hide();
                            $('<div class="alert alert-success"></div>')
                                    .html(result.message).appendTo('#messages');
                        }
                    }
                }
            });
        };
    });
</script>
<div id="start">

<?php if(!empty($testDirs)) { ?>
<div class="alert alert-error">
    <strong>Achtung:</strong> Für das Update werden für folgende Verzeichnisse Schreibrechte benötigt.<br><br>
    <?php foreach($testDirs as $testDir) { ?>
    <?php echo $testDir ?: '. (Shopware-Verzeichnis)'; ?><br>
    <?php } ?>
</div>
<?php } ?>

<div id="messages"></div>

<?php if(!file_exists($app->config('backupDir') . 'database.php')) { ?>
    <div class="page-header page-backup">
        <h2>1. Datenbank-Backup erstellen</h2>
    </div>
    <div class="page">
        <span class="help-block">
            .....
        </span>
        <div class="actions clearfix">
            <a id="link-backup" href="<?php echo $app->urlFor('action', array('action' =>'backupDatabase')); ?>" class="right primary ajax-loading">
                Backup erstellen
            </a>
        </div>
    </div>
<?php } ?>

<?php if(version_compare($app->config('updateVersion'), $app->config('currentVersion'), '>')) { ?>
    <div class="page-header page-database">
        <h2>2. Datenbank-Update duchführen</h2>
    </div>
    <div class="page">
        <span class="help-block">
            Aktuelle Version: <?php echo $app->config('currentVersion')?><br>
            Update Version: <?php echo $app->config('updateVersion')?>
        </span>
        <div class="actions clearfix">
            <a id="link-update" href="<?php echo $app->urlFor('action', array('action' => 'database')); ?>" class="right primary ajax-loading">
                Update durchführen
            </a>
        </div>
    </div>
<?php } ?>

<?php if(file_exists($app->config('sourceDir'))) { ?>
    <div class="page-header page-main">
        <h2>3. Generelles Update starten</h2>
    </div>
    <div class="page">
        <span class="help-block">
            Artikel-Bilder/Konfiguration übernehmen, Cache leeren, Kategoriebaum erstellen
        </span>
        <div class="actions clearfix">
            <a id="link-progress" href="<?php echo $app->urlFor('action', array('action' => 'progress')); ?>" class="right primary ajax-loading">
                Update durchführen
            </a>
        </div>
    </div>
<?php } else { ?>
    <div class="alert alert-success">
        Das Update wurde erfolgreich abgeschlossen. Bitte fahren Sie mit der Übernahme der Anpassungen fort.
    </div>
<?php }  ?>

    <div class="actions clearfix" style="margin: 18px 0">
        <a href="<?php echo $app->urlFor('system'); ?>" class="secondary"><?php echo $translation["back"];?></a>
        <a id="link-next" href="<?php echo $app->urlFor('custom'); ?>" class="right primary"><?php echo $translation["forward"];?></a>
    </div>
</div>
<?php $this->display('footer.php');?>