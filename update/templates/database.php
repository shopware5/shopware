<?php $this->display('header.php');?>
<script type="text/javascript">
    jQuery(document).ready(function() {
        $('.ajax-loading').live('click', function(event) {
            event.preventDefault();
            $.loading($(this).text());
            $.ajax({
                url: $(this).attr('href'),
                dataType: 'json',
                data: [],
                success: function(result) {
                    if(!result.success) {
                        $('.alert').remove();
                        $('<div class="alert alert-error"></div>')
                          .text(result.message).prependTo('#start');
                    }
                    $.removeLoading();
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
    });
</script>
<div id="start">
<?php if ($error){ ?>
    <div class="alert alert-error">
        <?php echo $translation["database_error"];?>
   </div>
<?php } ?>

    <form id="form-database" action="<?php echo $app->urlFor('index', array()); ?>" method="post">

        <div class="page-header">
            <i class="icon-chevron-down"></i>
            <h2>Datenbank-Backup erstellen</h2>
        </div>
        <div class="page">
            <span class="help-block">
                .....
            </span>
            <div class="actions clearfix">
                <a href="<?php echo $app->urlFor('backupDatabase'); ?>" class="right primary ajax-loading">Backup erstellen</a>
            </div>
        </div>

        <div class="page-header">
            <i class="icon-chevron-down"></i>
            <h2>Datenbank-Update duchführen</h2>
        </div>
        <div class="page">
            <span class="help-block">
                .....
            </span>
            <div class="actions clearfix">
                <a href="<?php echo $app->urlFor('updateDatabase'); ?>" class="right primary ajax-loading">Update durchführen</a>
            </div>
        </div>

        <div class="actions clearfix">
            <a href="<?php echo $app->urlFor('system', array()); ?>" class="secondary"><?php echo $translation["back"];?></a>
            <!--<input type="submit" class="right primary" value="<?php echo $translation["forward"];?>" />-->
        </div>
    </form>
</div>
<?php $this->display('footer.php');?>