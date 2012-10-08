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
                }
            });
        });
    });
</script>
<div id="start">
        <div class="page-header page-restore">
            <h2>Datenbank-Backup wiederherstellen</h2>
        </div>
        <div class="page">
            <span class="help-block">
                .....
            </span>
            <div class="actions clearfix">
                <a id="link-restore" href="<?php echo $app->urlFor('action', array('action' =>'restoreDatabase')); ?>" class="right primary ajax-loading">
                    Backup wiederherstellen
                </a>
                <a href="<?php echo $app->urlFor('action', array('action' => 'downloadDatabase')); ?>" class="right secondary">Backup herunterladen</a>
            </div>
        </div>
        <div class="actions clearfix" style="margin: 18px 0">
            <a href="<?php echo $app->urlFor('system'); ?>" class="secondary"><?php echo $translation["back"];?></a>
            <a id="link-next" href="<?php echo $app->urlFor('finish'); ?>" class="right primary"><?php echo $translation["forward"];?></a>
        </div>

</div>
<?php $this->display('footer.php');?>