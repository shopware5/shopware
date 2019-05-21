(function ($) {
    'use strict';

    var defaultConfig = {
        speed: 600,
        bullets: true,
        auto: false,
        time: 5000
    };

    function Slider(el) {
        this.$el = $(el);
        this.$imageContainer = this.$el.find('.images-container');
        this.$bulletContainer = this.$el.find('.bullets-container');
        this.index = 1;

        this.init();
    }

    Slider.prototype.init = function () {
        this.imgNumber = this.$imageContainer.find('li').length;
        this.sliderContainerWidth = this.$el.width();

        var imageContainerWidth = this.sliderContainerWidth * this.imgNumber;

        this.$imageContainer.css({
            'width': imageContainerWidth,
            'margin-left': 0
        });

        this.$imageContainer.find('li').css('width', this.sliderContainerWidth);

        if (defaultConfig.bullets) {
            this.$bulletContainer.html('');
            for (var i = 1; i <= this.imgNumber; i++) {
                this.$bulletContainer.append('<span class="bullet"></span>');
            }

            this.$bulletContainer.find('.bullet').eq(0).addClass('active');
        }

        this.$bulletContainer.find('.bullet').on('click', $.proxy(this.onUsePager, this));
        this.$el.find('.a-right').on('click', $.proxy(this.onSlideRight, this));
        this.$el.find('.a-left').on('click', $.proxy(this.onSlideLeft, this));
    };

    Slider.prototype.onSlideRight = function () {
        var marginLeft = parseInt(this.$imageContainer.css('margin-left')),
            newMargin = marginLeft - this.sliderContainerWidth,
            maxMargin = (this.imgNumber - 1) * this.sliderContainerWidth,
            currentBullet;

        if (newMargin < -maxMargin) {
            return;
        }

        this.$imageContainer.animate({
            marginLeft: newMargin
        }, defaultConfig.speed);

        currentBulvar = Math.abs(newMargin) / this.sliderContainerWidth;
        this.$bulletContainer.find('.bullet').removeClass('active');
        this.$bulletContainer.find('.bullet').eq(currentBullet).addClass('active');
    };

    Slider.prototype.onSlideLeft = function () {
        var marginLeft = parseInt(this.$imageContainer.css('margin-left')),
            newMargin = marginLeft + this.sliderContainerWidth,
            currentBullet;

        if (newMargin > 0) {
            return;
        }

        this.$imageContainer.animate({
            marginLeft: newMargin
        }, defaultConfig.speed);

        currentBulvar = Math.abs(newMargin) / this.sliderContainerWidth;
        this.$bulletContainer.find('.bullet').removeClass('active');
        this.$bulletContainer.find('.bullet').eq(currentBullet).addClass('active');
    };

    Slider.prototype.onUsePager = function (event) {
        var $el = $(event.currentTarget),
            bulletIndex;

        if ($el.hasClass('active')) {
            return;
        }

        bulletIndex = this.$bulletContainer.find('span').index($el);

        this.$bulletContainer.find(".bullet").removeClass("active").eq(bulletIndex).addClass("active");
        this.$imageContainer.animate({
            marginLeft: -this.sliderContainerWidth * bulletIndex
        }, defaultConfig.speed);
    };

    $.fn.slider = function () {
        return this.each(function() {
            var $el = $(this);

            if ($el.data('plugin_slider')) {
                return;
            }

            var plugin = new Slider(this);
            $el.data('plugin_slider', plugin);
        });
    };

    $(function () {
        $('*[data-slider="true"]').slider();
    });

})(jQuery);
