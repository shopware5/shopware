<?php $this->display('header.php');?>
<script type="text/javascript">
    jQuery(document).ready(function() {
        $('.ajax-loading').live('click', function(event) {
            event.preventDefault();
            var me = $(this);
            $.loading(me.text());
            $.ajax({
                url: $(this).attr('href'),
                dataType: 'json',
                data: [],
                success: function(result) {
                    if(!result || !result.success) {
                        $('.alert').remove();
                        $('<div class="alert alert-error"></div>')
                          .html(result.message).prependTo('#start');
                        $.removeLoading();
                        return;
                    } else if(result.message) {
                        $('.alert').remove();
                        $('<div class="alert alert-success"></div>')
                         .html(result.message).prependTo('#start');
                        $.removeLoading();
                    }
                    switch (me.attr('id')){
                        case 'link-backup':
                            $('.page-restore').show().next('.page').show();
                            $('.page-backup').hide().next('.page').hide();
                            break;
                        case 'link-restore':
                            $('.page-backup').show().next('.page').show();
                            $('.page-restore').hide().next('.page').hide();
                            break;
                        case 'link-update':
                            if($('.page-image')) {
                                $('.page-image').show().next('.page').show();
                            } else {
                                $('.link-next').show();
                            }
                            $('.page-update').hide().next('.page').hide();
                            break;
                        case 'link-image':
                            $('.page-image').hide().next('.page').hide();
                            $('.link-next').show();
                            break;
                    }
                }
            });
        });

        $('.page-header').live('click', function() {
            var $this = $(this);
            if ($(this).find('i').hasClass('icon-chevron-down')){
                $(this).find('i').removeClass('icon-chevron-down').addClass('icon-chevron-up');
            }else {
                $(this).find('i').removeClass('icon-chevron-up').addClass('icon-chevron-down');
            }
            $this.next('.page').toggle();
        });

        $('.page').hide();
        $('.page-header').prepend('<i>');
        $('.page-header i').addClass('icon-chevron-up');

        <?php if(!file_exists('backup/database.php')) { ?>
            //$('.page-backup').next('.page').show();
            //$('.page-backup').find('i').removeClass('icon-chevron-down').addClass('icon-chevron-down');
            $('.page-restore').hide().next('.page').hide();
        <?php } else { ?>
            $('.page-backup').hide().next('.page').hide();
            $('.page-update').next('.page').show();
            $('.page-update').find('i').removeClass('icon-chevron-down').addClass('icon-chevron-down');
        <?php } ?>

        <?php if(!file_exists('../images/articles/')) { ?>
            $('.page-image').hide().next('.page').hide();
        <?php } ?>

        <?php if(version_compare($app->config('updateVersion'), $app->config('currentVersion'), '<=')) { ?>
            $('.page-update').hide().next('.page').hide();
            $('.page-backup').hide().next('.page').hide();
        <?php } else { ?>
            $('.link-next').hide();
        <?php } ?>
    });
</script>
<div id="start">
<?php if ($error){ ?>
    <div class="alert alert-error">
        <?php echo $translation["database_error"];?>
   </div>
<?php } ?>

    <form id="form-database" action="<?php echo $app->urlFor('index', array()); ?>" method="post">

        <div class="page-header page-backup">
            <h2>Datenbank-Backup erstellen</h2>
        </div>
        <div class="page">
            <span class="help-block">
                .....
            </span>
            <div class="actions clearfix">
                <a id="link-backup" href="<?php echo $app->urlFor('backupDatabase'); ?>" class="right primary ajax-loading">Backup erstellen</a>
            </div>
        </div>

        <div class="page-header page-restore">
            <h2>Datenbank wiederherstellen</h2>
        </div>
        <div class="page">
            <span class="help-block">
                .....
            </span>
            <div class="actions clearfix">
                <a id="link-restore" href="<?php echo $app->urlFor('restoreDatabase'); ?>" class="right primary ajax-loading">Backup wiederherstellen</a>
                <a href="<?php echo $app->urlFor('downloadDatabase'); ?>" class="right secondary">Backup herunterladen</a>
            </div>
        </div>

        <div class="page-header page-update">
            <h2>Datenbank-Update duchführen</h2>
        </div>
        <div class="page">
            <span class="help-block">
                .....
            </span>
            <div class="actions clearfix">
                <a id="link-update" href="<?php echo $app->urlFor('updateDatabase'); ?>" class="right primary ajax-loading">Update durchführen</a>
            </div>
        </div>
<?php if(file_exists('../images/articles/')) { ?>
        <div class="page-header page-image">
            <h2>Artikel-Bilder übernehmen</h2>
        </div>
        <div class="page">
            <span class="help-block">
                .....
            </span>
            <div class="actions clearfix">
                <a id="link-image" href="<?php echo $app->urlFor('updateImage'); ?>" class="right primary ajax-loading">Bilder übernehmen</a>
            </div>
        </div>
<?php } ?>
        <div class="actions clearfix" style="margin: 18px 0">
            <a href="<?php echo $app->urlFor('system'); ?>" class="secondary"><?php echo $translation["back"];?></a>
            <a id="link-next" href="<?php echo $app->urlFor('finish'); ?>" class="right primary"><?php echo $translation["forward"];?></a>
        </div>
    </form>
</div>
<?php $this->display('footer.php');?>