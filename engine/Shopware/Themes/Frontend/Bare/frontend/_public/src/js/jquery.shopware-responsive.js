$(function() {
    StateManager.init([{
        type: 'smartphone',
        enter: '0px',
        exit: '767px'
    }, {
        type: 'tablet',
        enter: '768px',
        exit: '1023px'
    }, {
        type: 'tabletLandscape',
        enter: '1024px',
        exit: '1259px'
    }, {
        type: 'desktop',
        enter: '1260px',
        exit: '5160px'
    }]);

    StateManager.registerListener([{
        type: 'smartphone',
        enter: function() {
            var pageWrap = $('.page-wrap');
            $('.sidebar-main').insertBefore(pageWrap);
            $('.action--filter-options').insertBefore(pageWrap);
            $('*[data-offcanvas="true"]').offcanvasMenu();
            $('*[data-search-dropdown="true"]').searchFieldDropDown();
            $('*[data-slide-panel="true"]').slidePanel();
            $('.product--supplier').appendTo($('.product--info'));
            $('.category--teaser .hero--text').collapseText();
        },
        exit: function() {
            $('*[data-offcanvas="true"]').each(function() {
                $(this).data('plugin_offcanvasMenu').destroy();
            });
            $('*[data-search-dropdown="true"]').each(function() {
                $(this).data('plugin_searchFieldDropDown').destroy();
            });
            $('*[data-slide-panel="true"]').each(function() {
                $(this).data('plugin_slidePanel').destroy();
            });

            $('.product--supplier').appendTo($('.product--header'));
            $('.category--teaser .hero--text').data('plugin_collapseText').destroy();
            $('.sidebar-main').prependTo($('.content-main--inner'));
            $('.action--filter-options').insertAfter($('.action--per-page'));
        }
    }, {
        type: 'tablet',
        enter: function() {
            $('*[data-search-dropdown="true"]').searchFieldDropDown();
            $('nav.product--actions').insertBefore($('.additional-info--tabs'));
            $('.filter--trigger').collapsePanel();
        },
        exit: function() {
            $('*[data-search-dropdown="true"]').each(function() {
                $(this).data('plugin_searchFieldDropDown').destroy();
            });
            $('nav.product--actions').insertAfter($('.buybox--form'));
            $('.filter--trigger').data('plugin_collapsePanel').destroy();
        }
    }, {
        type: 'tabletLandscape',
        enter: function() {
            $('*[data-search-dropdown="true"]').searchFieldDropDown();
            $('nav.product--actions').insertBefore($('.additional-info--tabs'));
            $('.filter--trigger').collapsePanel();
        },
        exit: function() {
            $('*[data-search-dropdown="true"]').each(function() {
                $(this).data('plugin_searchFieldDropDown').destroy();
            });
            $('nav.product--actions').insertAfter($('.buybox--form'));
            $('.filter--trigger').data('plugin_collapsePanel').destroy();
        }
    }, {
        type: 'desktop',
        enter: function() {
            $('.filter--trigger').collapsePanel();
        },
        exit: function() {
            $('.filter--trigger').data('plugin_collapsePanel').destroy();
        }
    }]);

    if (window.widgets !== 'undefined' && window.widgets.length > 0) {
        $.each(window.widgets, function(index, widget) {
            StateManager.registerListener([{
                type: 'smartphone',
                enter: function() {
                    $(widget.selector)[widget.plugin](widget.smartphone);
                },
                exit: function() {
                    $(widget.selector).data('plugin_' + widget.plugin).destroy();
                }
            }, {
                type: 'tablet',
                enter: function() {
                    $(widget.selector)[widget.plugin](widget.tablet);
                },
                exit: function() {
                    $(widget.selector).data('plugin_' + widget.plugin).destroy();
                }
            }, {
                type: 'tabletLandscape',
                enter: function() {
                    $(widget.selector)[widget.plugin](widget.tabletLandscape);
                },
                exit: function() {
                    $(widget.selector).data('plugin_' + widget.plugin).destroy();
                }
            }, {
                type: 'desktop',
                enter: function() {
                    $(widget.selector)[widget.plugin](widget.desktop);
                },
                exit: function() {
                    $(widget.selector).data('plugin_' + widget.plugin).destroy();
                }
            }]);
        });
    }

    $('*[data-tab-content="true"]').tabContent();
    $('*[data-emotions="true"]').emotions();
    $('*[data-image-slider="true"]').imageSlider();
    $('*[data-collapse-panel="true"]').collapsePanel();
    $('*[data-auto-submit="true"]').autoSubmit();
    $('input[data-quantity-field="true"]').quantityField();

    // Deferred loading of the captcha
    $("div.captcha--placeholder[data-src]").each(function() {
        var $this = $(this),
            requestURL = $this.attr('data-src') || '';

        if (!requestURL || !requestURL.length) {
            return false;
        }

        $this.load(requestURL);
    });

    // Select box replacement
    $('.field--select .arrow').on('click', function(event) {
        event.preventDefault();

        var el =  $(this).parent('div').children('select')[0];

        // Workaround to open the select box drop down
        if(document.createEvent) {
            var e = document.createEvent('MouseEvents');

            e.initMouseEvent("mousedown", true, true, window, 0, 0, 0, 0, 0, false, false, false, false, 0, null);
            el.dispatchEvent(e);
        } else {
            el.fireEvent("onmousedown");
        }
    });

    // Auto submitting form
    $('select[data-auto-submit-form="true"]').on('change', function() {
        $(this).parents('form').submit();
    });

    // Change the active tab to the customer reviews, if the url param sAction === rating is set.
    if($('.is--ctl-detail').length) {
        var plugin = $('.additional-info--tabs').data('plugin_tabContent');

        $('.product--rating-link').on('click', function(e) {
            e.preventDefault();
            plugin.changeTab(1, true);
        });

        var param = decodeURI((RegExp('sAction' + '=' + '(.+?)(&|$)').exec(location.search) || [,null])[1]);
        if(param === 'rating') {
            plugin.changeTab(1, false);
        }
    }

    // Debug mode is enabled
    if($('.debug--panel').length) {
        var $debugPanel = $('.debug--panel'),
            $window = $(window), timer;

        $debugPanel.hide();
        var refreshDebugPanel = function() {
            var device = 'Device: ';
            $debugPanel.find('.debug--width').html($window.width());
            $debugPanel.find('.debug--height').html($window.height());

            $debugPanel.fadeIn('fast');

            if(StateManager.isSmartphone()) {
                device += 'Smartphone';
            }

            if(StateManager.isTablet()) {
                device += 'Tablet';
            }

            if(StateManager.isDesktop()) {
                device += 'Desktop';
            }
            $debugPanel.find('.debug--device').html(device);

            if(timer) {
                window.clearTimeout(timer);
            }
            timer = window.setTimeout(function() {
                $debugPanel.fadeOut('fast');
            }, 1000);
        };

        $window.on('resize', function() {
            window.setTimeout(function() {
                refreshDebugPanel();
            }, 10);
        });
    }
});
