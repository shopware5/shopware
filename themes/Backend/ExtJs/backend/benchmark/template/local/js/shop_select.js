(function ($) {
    'use strict';

    function ShopSelect(el) {
        this.$el = $(el);

        this.initElements();
        this.initEvents();
    }

    ShopSelect.prototype.initElements = function () {
        this.$shopOption = this.$el.find('.shop-option');
        this.$activeShopIdEl = this.$el.find('.active-shop-id');
    };

    ShopSelect.prototype.initEvents = function () {
        this.$el.on('click', $.proxy(this.onOpenShopSelect, this));
        this.$shopOption.on('click', $.proxy(this.onClickShopOption, this));
    };

    ShopSelect.prototype.onOpenShopSelect = function () {
        this.$el.toggleClass('open');
    };

    /**
     * @param { Event } event
     */
    ShopSelect.prototype.onClickShopOption = function (event) {
        var $currentTarget = $(event.currentTarget),
            selectedShopId = $currentTarget.attr('data-shop-id'),
            newLocation = $currentTarget.attr('data-shop-switch-url');

        // Already active shop has been selected, do nothing
        if (selectedShopId === this.$activeShopIdEl.val()) {
            return;
        }

        window.href.location = newLocation;
    };

    $.fn.shopSelect = function() {
        return this.each(function() {
            var $el = $(this);

            if ($el.data('plugin_shopSelect')) {
                return;
            }

            var plugin = new ShopSelect(this);
            $el.data('plugin_shopSelect', plugin);
        });
    };

    $(function() {
        $('*[data-shop-select="true"]').shopSelect();
    });
})(jQuery);
