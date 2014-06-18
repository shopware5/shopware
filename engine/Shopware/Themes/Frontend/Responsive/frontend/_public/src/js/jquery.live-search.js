$.plugin('liveSearch', {

    /** Your default options */
    defaults: {
        minLength: 3
    },

    /** Plugin constructor */
    init: function () {
        var me = this;

        StateManager.registerListener([{
            type: 'tabletLandscape',
            exit: function() {
                if(me.$el.hasClass('entry-is--active')) {
                    me.$el.removeClass('entry-is--active');
                }
            }
        }]);

        /** Register event listener */
        me._on(me.$el, 'keyup', $.proxy(me.onKeyUp, me));

        me._on(me.$el, 'blur', $.proxy(me.onBlur, me));

        me._on(me.$el, 'click', $.proxy(me.onClickSearchBar, me));

        me._on($('.main-search--results'), 'mousedown', $.proxy(me.onClickSearchResults, me));

    },

    /** Event listener method */
    onKeyUp: function (event)  {
        var me = this,
            term = me.$el.val(),
            termLength = term.length;

        if(me._timeout) {
            window.clearTimeout(me._timeout);
        }

        me._timeout = window.setTimeout(function() {

            // check for minimum characters
            if(me.defaults.minLength && termLength < me.defaults.minLength) {
                $('.main-search--results').hide();
                return;
            }

            $('.loading-indicator').fadeIn();

            // Ajax Search
            $.ajax({
                url: $.controller.ajax_search,
                data: {
                    sSearch: term
                }
            }).done(function(results) {

                $('.loading-indicator').fadeOut();

                if(!results) {
                    $('.main-search--results').hide();
                    return;
                }

                $('.main-search--results').html(results).show();
            });

        }, 250);

    },

    onBlur: function (event) {
        var $target = $(event.target);
        
        if(!$('.main-search--results').is(':visible')) {
            return;
        }

        $('.main-search--results').hide();
    },

    onClickSearchBar: function (event) {
        var me = this,
            term = me.$el.val(),
            termLength = term.length;

        if(termLength) {
            $('.main-search--results').show();
        }
    },

    onClickSearchResults: function (event) {
        // Prevent closing search results
        var $target = $(event.target);
        
        if($target.is('a')) {
            console.log('target is a link');
            location.href = $target[0]['baseURI'];
            return;
        }
        
        event.preventDefault();
    },

    /** Destroys the plugin */
    destroy: function () {
        this._destroy();
    }
});