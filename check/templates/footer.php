<?php
if (!defined("installer")){
    exit;
}
?>
        </div>
    </section>
</div>

<!-- Include jQuery -->
<script src="//code.jquery.com/jquery-1.8.0.min.js"></script>
<script>window.jQuery || document.write('<script src="<?php echo $basepath ?>/check/assets/javascript/jquery-1.8.0.min.js"><\/script>')</script>
<script type="text/javascript" src="<?php echo $basepath ?>/check/assets/javascript/jquery.installer.js"></script>
<script type="text/javascript">
    (function($) {
    	$(document).ready(function() {
    		$('table.table').hide();
            $('span.help-block').hide();
    		$('.page-header').live('click', function() {
    			var $this = $(this);
                if ($(this).find('i').hasClass('icon-chevron-down')){
                    $(this).find('i').removeClass('icon-chevron-down').addClass('icon-chevron-up');
                }else {
                    $(this).find('i').removeClass('icon-chevron-up').addClass('icon-chevron-down');
                }

    			$this.next().next('table.table').toggle();
                $this.next('span.help-block').toggle();
    		});
    	});
    })(jQuery);
</script>
</body>
</html>