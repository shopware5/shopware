(function ($) {
    'use strict';

    function SwitchButton(el) {
        this.$el = $(el);
        this.input = this.$el.find('input');
        this.industry = (el.getAttribute('data-industry') === 'true');
        this.time = this.$el.attr('data-time');
        this.valuesKey = this.$el.attr('data-values-key');

        this.init();
    }

    SwitchButton.prototype.init = function () {
        this.$el.on('change', $.proxy(this.onChangeSwitch, this));
    };

    SwitchButton.prototype.onChangeSwitch = function () {
        var index = this.industry ? 1 : 0,
            uniqueChartString = this.valuesKey + this.time.charAt(0).toUpperCase() + this.time.slice(1) + 'Chart';

        window[uniqueChartString].data.datasets[index]._meta[window[uniqueChartString].id].hidden = !this.input.is(':checked');
        window[uniqueChartString].update();
    };

    $.fn.switchButton = function () {
        return this.each(function() {
            var $el = $(this);

            if ($el.data('plugin_switchButton')) {
                return;
            }

            var plugin = new SwitchButton(this);
            $el.data('plugin_switchButton', plugin);
        });
    };

    $(function() {
        $('*[data-switch-button="true"]').switchButton();
    });
})(jQuery);