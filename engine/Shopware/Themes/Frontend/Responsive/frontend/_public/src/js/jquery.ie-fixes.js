// Window.prototype.getComputedStyle
// see: https://github.com/jonathantneal/polyfill
(function () {
    function getComputedStylePixel(element, property, fontSize) {
        element.document; // Internet Explorer sometimes struggles to read currentStyle until the element's document is accessed.

        var
            value = element.currentStyle[property].match(/([\d\.]+)(%|cm|em|in|mm|pc|pt|)/) || [0, 0, ''],
            size = value[1],
            suffix = value[2],
            rootSize;

        fontSize = !fontSize ? fontSize : /%|em/.test(suffix) && element.parentElement ? getComputedStylePixel(element.parentElement, 'fontSize', null) : 16;
        rootSize = property == 'fontSize' ? fontSize : /width/i.test(property) ? element.clientWidth : element.clientHeight;

        return suffix == '%' ? size / 100 * rootSize :
            suffix == 'cm' ? size * 0.3937 * 96 :
                suffix == 'em' ? size * fontSize :
                    suffix == 'in' ? size * 96 :
                        suffix == 'mm' ? size * 0.3937 * 96 / 10 :
                            suffix == 'pc' ? size * 12 * 96 / 72 :
                                suffix == 'pt' ? size * 96 / 72 :
                                    size;
    }

    function setShortStyleProperty(style, property) {
        var
            borderSuffix = property == 'border' ? 'Width' : '',
            t = property + 'Top' + borderSuffix,
            r = property + 'Right' + borderSuffix,
            b = property + 'Bottom' + borderSuffix,
            l = property + 'Left' + borderSuffix;

        style[property] = (style[t] == style[r] && style[t] == style[b] && style[t] == style[l] ? [ style[t] ] :
            style[t] == style[b] && style[l] == style[r] ? [ style[t], style[r] ] :
                style[l] == style[r] ? [ style[t], style[r], style[b] ] :
                    [ style[t], style[r], style[b], style[l] ]).join(' ');
    }

    // <CSSStyleDeclaration>
    function CSSStyleDeclaration(element) {
        var
            style = this,
            currentStyle = element.currentStyle,
            fontSize = getComputedStylePixel(element, 'fontSize'),
            unCamelCase = function (match) {
                return '-' + match.toLowerCase();
            },
            property;

        for (property in currentStyle) {
            Array.prototype.push.call(style, property == 'styleFloat' ? 'float' : property.replace(/[A-Z]/, unCamelCase));

            if (property == 'width') {
                style[property] = element.offsetWidth + 'px';
            } else if (property == 'height') {
                style[property] = element.offsetHeight + 'px';
            } else if (property == 'styleFloat') {
                style.float = currentStyle[property];
            } else if (/margin.|padding.|border.+W/.test(property) && style[property] != 'auto') {
                style[property] = Math.round(getComputedStylePixel(element, property, fontSize)) + 'px';
            } else if (/^outline/.test(property)) {
                // errors on checking outline
                try {
                    style[property] = currentStyle[property];
                } catch (error) {
                    style.outlineColor = currentStyle.color;
                    style.outlineStyle = style.outlineStyle || 'none';
                    style.outlineWidth = style.outlineWidth || '0px';
                    style.outline = [style.outlineColor, style.outlineWidth, style.outlineStyle].join(' ');
                }
            } else {
                style[property] = currentStyle[property];
            }
        }

        setShortStyleProperty(style, 'margin');
        setShortStyleProperty(style, 'padding');
        setShortStyleProperty(style, 'border');

        style.fontSize = Math.round(fontSize) + 'px';
    }

    CSSStyleDeclaration.prototype = {
        constructor: CSSStyleDeclaration,
        // <CSSStyleDeclaration>.getPropertyPriority
        getPropertyPriority: function () {
            throw new Error('NotSupportedError: DOM Exception 9');
        },
        // <CSSStyleDeclaration>.getPropertyValue
        getPropertyValue: function (property) {
            return this[property.replace(/-\w/g, function (match) {
                return match[1].toUpperCase();
            })];
        },
        // <CSSStyleDeclaration>.item
        item: function (index) {
            return this[index];
        },
        // <CSSStyleDeclaration>.removeProperty
        removeProperty: function () {
            throw new Error('NoModificationAllowedError: DOM Exception 7');
        },
        // <CSSStyleDeclaration>.setProperty
        setProperty: function () {
            throw new Error('NoModificationAllowedError: DOM Exception 7');
        },
        // <CSSStyleDeclaration>.getPropertyCSSValue
        getPropertyCSSValue: function () {
            throw new Error('NotSupportedError: DOM Exception 9');
        }
    };

    // <window>.getComputedStyle
    window.getComputedStyle = Window.prototype.getComputedStyle = function (element) {
        return new CSSStyleDeclaration(element);
    };
})();

/*! http://mths.be/placeholder v2.0.8 by @mathias */
;(function(window, document, $) {

    // Opera Mini v7 doesn’t support placeholder although its DOM seems to indicate so
    var isOperaMini = Object.prototype.toString.call(window.operamini) == '[object OperaMini]';
    var isInputSupported = 'placeholder' in document.createElement('input') && !isOperaMini;
    var isTextareaSupported = 'placeholder' in document.createElement('textarea') && !isOperaMini;
    var prototype = $.fn;
    var valHooks = $.valHooks;
    var propHooks = $.propHooks;
    var hooks;
    var placeholder;

    if (isInputSupported && isTextareaSupported) {

        placeholder = prototype.placeholder = function() {
            return this;
        };

        placeholder.input = placeholder.textarea = true;

    } else {

        placeholder = prototype.placeholder = function() {
            var $this = this;
            $this
                .filter((isInputSupported ? 'textarea' : ':input') + '[placeholder]')
                .not('.placeholder')
                .bind({
                    'focus.placeholder': clearPlaceholder,
                    'blur.placeholder': setPlaceholder
                })
                .data('placeholder-enabled', true)
                .trigger('blur.placeholder');
            return $this;
        };

        placeholder.input = isInputSupported;
        placeholder.textarea = isTextareaSupported;

        hooks = {
            'get': function(element) {
                var $element = $(element);

                var $passwordInput = $element.data('placeholder-password');
                if ($passwordInput) {
                    return $passwordInput[0].value;
                }

                return $element.data('placeholder-enabled') && $element.hasClass('placeholder') ? '' : element.value;
            },
            'set': function(element, value) {
                var $element = $(element);

                var $passwordInput = $element.data('placeholder-password');
                if ($passwordInput) {
                    return $passwordInput[0].value = value;
                }

                if (!$element.data('placeholder-enabled')) {
                    return element.value = value;
                }
                if (value == '') {
                    element.value = value;
                    // Issue #56: Setting the placeholder causes problems if the element continues to have focus.
                    if (element != safeActiveElement()) {
                        // We can't use `triggerHandler` here because of dummy text/password inputs :(
                        setPlaceholder.call(element);
                    }
                } else if ($element.hasClass('placeholder')) {
                    clearPlaceholder.call(element, true, value) || (element.value = value);
                } else {
                    element.value = value;
                }
                // `set` can not return `undefined`; see http://jsapi.info/jquery/1.7.1/val#L2363
                return $element;
            }
        };

        if (!isInputSupported) {
            valHooks.input = hooks;
            propHooks.value = hooks;
        }
        if (!isTextareaSupported) {
            valHooks.textarea = hooks;
            propHooks.value = hooks;
        }

        $(function() {
            // Look for forms
            $(document).delegate('form', 'submit.placeholder', function() {
                // Clear the placeholder values so they don't get submitted
                var $inputs = $('.placeholder', this).each(clearPlaceholder);
                setTimeout(function() {
                    $inputs.each(setPlaceholder);
                }, 10);
            });
        });

        // Clear placeholder values upon page reload
        $(window).bind('beforeunload.placeholder', function() {
            $('.placeholder').each(function() {
                this.value = '';
            });
        });

    }

    function args(elem) {
        // Return an object of element attributes
        var newAttrs = {};
        var rinlinejQuery = /^jQuery\d+$/;
        $.each(elem.attributes, function(i, attr) {
            if (attr.specified && !rinlinejQuery.test(attr.name)) {
                newAttrs[attr.name] = attr.value;
            }
        });
        return newAttrs;
    }

    function clearPlaceholder(event, value) {
        var input = this;
        var $input = $(input);
        if (input.value == $input.attr('placeholder') && $input.hasClass('placeholder')) {
            if ($input.data('placeholder-password')) {
                $input = $input.hide().next().show().attr('id', $input.removeAttr('id').data('placeholder-id'));
                // If `clearPlaceholder` was called from `$.valHooks.input.set`
                if (event === true) {
                    return $input[0].value = value;
                }
                $input.focus();
            } else {
                input.value = '';
                $input.removeClass('placeholder');
                input == safeActiveElement() && input.select();
            }
        }
    }

    function setPlaceholder() {
        var $replacement;
        var input = this;
        var $input = $(input);
        var id = this.id;
        if (input.value == '') {
            if (input.type == 'password') {
                if (!$input.data('placeholder-textinput')) {
                    try {
                        $replacement = $input.clone().attr({ 'type': 'text' });
                    } catch(e) {
                        $replacement = $('<input>').attr($.extend(args(this), { 'type': 'text' }));
                    }
                    $replacement
                        .removeAttr('name')
                        .data({
                            'placeholder-password': $input,
                            'placeholder-id': id
                        })
                        .bind('focus.placeholder', clearPlaceholder);
                    $input
                        .data({
                            'placeholder-textinput': $replacement,
                            'placeholder-id': id
                        })
                        .before($replacement);
                }
                $input = $input.removeAttr('id').hide().prev().attr('id', id).show();
                // Note: `$input[0] != input` now!
            }
            $input.addClass('placeholder');
            $input[0].value = $input.attr('placeholder');
        } else {
            $input.removeClass('placeholder');
        }
    }

    function safeActiveElement() {
        // Avoid IE9 `document.activeElement` of death
        // https://github.com/mathiasbynens/jquery-placeholder/pull/99
        try {
            return document.activeElement;
        } catch (exception) {}
    }

}(this, document, jQuery));