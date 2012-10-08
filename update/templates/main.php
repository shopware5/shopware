<?php $this->display('header.php');?>
<script type="text/javascript">
    jQuery(document).ready(function() {
        $('.ajax-loading').live('click', function(event) {
            event.preventDefault();
            var me = $(this);
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
                        $('.alert').remove();
                    }
                    if(!result || !result.success) {
                        $('<div class="alert alert-error"></div>')
                                .html(result.message).appendTo('#messages');
                    } else if(result.message) {
                        $('<div class="alert alert-success"></div>')
                                .html(result.message).appendTo('#messages');
                    }
                }
            });
        };

        <?php if(!file_exists('backup/database.php')) { ?>
            $next = $('.page-backup');
        <?php } elseif(version_compare($app->config('updateVersion'), $app->config('currentVersion'), '<')) { ?>
            $next = $('.page-database');
        <?php } else { ?>
            $next = $('.page-main');
        <?php } ?>

        $next.next('.page').show();
        $next.find('i').removeClass('icon-chevron-down').addClass('icon-chevron-down');
    });
</script>
<div id="start">
    <div id="messages"></div>
<?php if(!file_exists('backup/database.php')) { ?>
        <div class="page-header page-backup">
            <h2>Datenbank-Backup erstellen</h2>
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
    <?php if(version_compare($app->config('updateVersion'), $app->config('currentVersion'), '<')) { ?>
        <div class="page-header page-database">
            <h2>Datenbank-Update duchführen</h2>
        </div>
        <div class="page">
            <span class="help-block">
                Aktuelle Version: <?php echo $app->config('currentVersion')?><br>
                Update Version: <?php echo $app->config('updateVersion')?>
            </span>
            <div class="actions clearfix">
                <a id="link-update" href="<?php echo $app->urlFor('action', array('action' => 'database')); ?>" class="right primary ajax-loading">
                    Update starten
                </a>
            </div>
        </div>
    <?php } ?>

        <div class="page-header page-main">
            <h2>Generelles Update starten</h2>
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

        <div class="actions clearfix" style="margin: 18px 0">
            <a href="<?php echo $app->urlFor('system'); ?>" class="secondary"><?php echo $translation["back"];?></a>
            <a id="link-next" href="<?php echo $app->urlFor('finish'); ?>" class="right primary"><?php echo $translation["forward"];?></a>
        </div>

</div>
<?php $this->display('footer.php');?>