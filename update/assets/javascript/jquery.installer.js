(function($){

    $(document).ready(function() {
        // Set js class on the html tag
        $('html').removeClass('no-js').addClass('js');

        var $pageHeader = $('.page-header');
        $pageHeader.live('click', function() {
            var $this = $(this);
            if ($(this).find('i').hasClass('icon-chevron-down')){
                $(this).find('i').removeClass('icon-chevron-down').addClass('icon-chevron-up');
            }else {
                $(this).find('i').removeClass('icon-chevron-up').addClass('icon-chevron-down');
            }
            $this.next('.page').toggle();
        });
        $('.page').hide();
        $pageHeader.prepend('<i>');
        $('.page-header i').addClass('icon-chevron-up');

        $('*[data-loading]').click(function(event) {
            event.preventDefault();

            var $this = $(this);

            if($this.attr('data-loading') == 'false') {
                return false;
            }

            var text = $this.attr('data-loading-text');
            $.loading(text);
            $this.parents("form").trigger("submit");
            //return true;
        });

        $('.check-all input[type=checkbox]').live('click', function() {
            var $this = $(this),
                name = $this.attr('name').replace(/([ #;&,.+*~':"!^$[\]()=>|\/@])/g,'\\$1'),
                $fields = $('input[name=' + name + ']');
            if($(this).attr('checked')) {
                $fields.attr('checked', 'checked');
            } else {
                $fields.removeAttr('checked');
            }
        });

        $('form.ajax-loading').submit(function() {
            $this = $(this);
            $.loading($this.find('[type=submit]').attr('value'));
            $.ajax({
                type: 'POST',
                url: $this.attr('action'),
                data: $this.serialize(),
                dataType: 'json',
                success: function(result) {
                    $('#messages').empty();
                    $.removeLoading();
                    if(!result || !result.success) {
                        $('<div class="alert alert-error"></div>')
                            .html(result.message).appendTo('#messages');
                    } else if(result.message) {
                        $('<div class="alert alert-success"></div>')
                            .html(result.message).appendTo('#messages');
                    }
                }
            });
            return false;
        });

       //$('.primary').bind('click', function(event) {
       //    var active = $('.navi-tabs li.active'),
       //        $this = $(this),
       //        form = $this.parents('form');
       //    if(!$.checkForm(form)) {
       //        event.preventDefault();
       //        return false;
       //    }
       //});

        $('.secondary').bind('click', function(event) {
            var active = $('.navi-tabs li.active'),
                prev = active.prev('li');

            prev.addClass('active');
        });

        $('input').bind('keyup', function() {

            /*if(!$.checkForm($(this).parents('form'))) {
               // return false;
            }*/

            var required = $(this).attr('required');
            if(required ) {
                var $this = $(this);

                if(!$this.val().length) {

                    $this.removeClass('inline-success').addClass('inline-error');
                } else {
                    $this.removeClass('inline-error').addClass('inline-success');
                }
            }

            var active = $('.navi-tabs li.active'),
                next = active.next('li');

            next.removeClass('disabled');
        });
    });

    $.removeLoading = function() {
        $('.loading-mask').remove();
        $('.overlay').remove();
    };

    $.loading = function(text) {
        $.removeLoading();
        var loadingDiv = $('<div>', {
            'class': 'loading-mask',
            'html': text
        }).hide();;
        var overlay = $('<div>', { 'class': 'overlay' }).hide();
        overlay.css('opacity', 0);

        loadingDiv.css({
            'width': 200,
            'margin-left': -100,
            'top': '50%',
            'left': '50%',
            'display': 'none',
            'opacity': 0,
            'position': 'fixed'
        });

        loadingDiv.close = function() {
            loadingDiv.fadeOut().hide();
            overlay.fadeOut().hide();
        };

        overlay.appendTo($('body')).show();
        overlay.animate({
            opacity: 0.2
        }, 350);
        loadingDiv.appendTo($('body')).show();;
        loadingDiv.animate({
            opacity: 1
        }, 350);
    };

    $.checkForm = function(form) {
        var inputs = form.find('input'),
            selects = form.find('select'),
            success = true;

        $.each(inputs, function(i, input) {
            var $input = $(input);

            if(!success) { return false; }

            if($input.hasClass('allowBlank')) {
                return success;
            }

            if($input.val().length === 0) {
                success = false;
            }
        });

        $.each(selects, function(i, select) {
            var $select = $(select);

            if(!success) { return false; }

            if($select.hasClass('allowBlank')) {
                return false;
            }

            if($select.val().length === 0) {
                success = false;
            }
        });

        return success;
    };
})(jQuery);