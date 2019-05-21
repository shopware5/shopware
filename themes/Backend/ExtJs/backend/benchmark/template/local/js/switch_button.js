(function ($) {
    'use strict';

    function SwitchButton(el) {
        this.$el = $(el);
        this.input = this.$el.find('input');
        this.industry = (el.getAttribute('data-industry') === 'true');
        this.lastYear = (el.getAttribute('data-last-year') === 'true');
        this.time = this.$el.attr('data-time');
        this.valuesKey = this.$el.attr('data-values-key');

        this.init();
    }

    SwitchButton.prototype.init = function () {
        this.$el.on('change', $.proxy(this.onChangeSwitch, this));
    };

    SwitchButton.prototype.onChangeSwitch = function () {
        var uniqueChartString = this.valuesKey + this.time.charAt(0).toUpperCase() + this.time.slice(1) + 'Chart';

        // Hide / show shop graph
        if (!this.industry && !this.lastYear) {
            window[uniqueChartString].data.datasets[0]._meta[window[uniqueChartString].id].hidden = !this.input.is(':checked');
        }

        // Hide / show industry graph
        if (this.industry) {
            window[uniqueChartString].data.datasets[2]._meta[window[uniqueChartString].id].hidden = !this.input.is(':checked');
        }

        // Hide / show last years graphs
        if (this.lastYear) {
            window[uniqueChartString].data.datasets[1]._meta[window[uniqueChartString].id].hidden = !this.input.is(':checked');

            // Industry last year also exists
            if (window[uniqueChartString].data.datasets[3]) {
                window[uniqueChartString].data.datasets[3]._meta[window[uniqueChartString].id].hidden = !this.input.is(':checked');
            }
        }

        window[uniqueChartString].update();
    };

    $.fn.switchButton = function () {
        return this.each(function() {
            var $el = $(this),
                plugin;

            if ($el.data('plugin_switchButton')) {
                return;
            }

            plugin = new SwitchButton(this);
            $el.data('plugin_switchButton', plugin);
        });
    };

    $(function() {
        $('*[data-switch-button="true"]').switchButton();
    });
})(jQuery);
