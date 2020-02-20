(function ($, window, undefined) {
    'use strict';

    $.getCookiePreference = function(cookieName) {
        var cookie = $.getCookie('cookiePreferences'),
            activeState = false,
            groupKeys,
            cookieKeys,
            cookiePreferences;

        if (!cookie) {
            return activeState;
        }

        cookiePreferences = JSON.parse(cookie);
        groupKeys = Object.keys(cookiePreferences.groups);

        $.each(groupKeys, function (groupIndex, groupKey) {
            if (!cookiePreferences.groups.hasOwnProperty(groupKey)) {
                return;
            }

            cookieKeys = Object.keys(cookiePreferences.groups[groupKey].cookies);

            $.each(cookieKeys, function (cookieIndex, cookieKey) {
                if (!cookiePreferences.groups[groupKey].cookies.hasOwnProperty(cookieKey)) {
                    return;
                }

                if (cookieKey !== cookieName) {
                    return;
                }

                activeState = cookiePreferences.groups[groupKey].cookies[cookieKey].active;
            });
        });

        return activeState;
    };

    $.plugin('swCookieConsentManager', {

        defaults: {
            /**
             * Class which will be applied when opening the cookie consent manager.
             *
             * @property openClass
             * @type {String}
             */
            openClass: 'is--open',

            /**
             * Selector of the element that should be clicked to close the consent manager modal.
             *
             * @property closeModalSelector
             * @type {String}
             */
            closeModalSelector: '.cookie-consent--close',

            /**
             * Selector of the element that wraps around each group.
             *
             * @property cookieGroupSelector
             * @type {String}
             */
            cookieGroupSelector: '.cookie-consent--group',

            /**
             * Selector of the hidden input element that contains the group's name.
             *
             * @property cookieGroupNameSelector
             * @type {String}
             */
            cookieGroupNameSelector: '.cookie-consent--group-name',

            /**
             * Selector of the input element which contains the 'active' state of the cookie group.
             *
             * @property cookieGroupToggleInputSelector
             * @type {String}
             */
            cookieGroupToggleInputSelector: '.cookie-consent--group-state-input',

            /**
             * Selector of the element which contains the cookies assigned to a group.
             *
             * @property cookieContainerSelector
             * @type {String}
             */
            cookieContainerSelector: '.cookie-consent--cookie',

            /**
             * Selector of the hidden input element which contains the name of a cookie.
             *
             * @property cookieNameSelector
             * @type {String}
             */
            cookieNameSelector: '.cookie-consent--cookie-name',

            /**
             * Selector of the input element which contains the 'active' state of the cookie.
             *
             * @property cookieActiveInputSelector
             * @type {String}
             */
            cookieActiveInputSelector: '.cookie-consent--cookie-state-input',

            /**
             * Selector of the label element for the active input.
             *
             * @property cookieActiveInputLabelSelector
             * @type {String}
             */
            cookieActiveInputLabelSelector: '.cookie-consent--cookie-state',

            /**
             * Selector of the button which should save the configured preferences.
             *
             * @property saveButtonSelector
             * @type {String}
             */
            saveButtonSelector: '.cookie-consent--save-button',

            /**
             * Selector of the buttons to open the cookie consent manager.
             *
             * @property openConsentManagerButton
             * @type {string}
             */
            openConsentManagerButton: '*[data-openConsentManager=true]',

            /**
             * Selector of the element which can be clicked as well to toggle a cookies state.
             *
             * @property cookieLabelSelector
             * @type {string}
             */
            cookieLabelSelector: '.cookie--label',

            /**
             * The class which marks a group as "required".
             *
             * @property requiredClass
             * @type {string}
             */
            requiredClass: 'cookie-consent--required'
        },

        /**
         * Contains the selected cookie preferences.
         *
         * @property preferences
         * @type {Object}
         */
        preferences: null,

        /**
         * Contains if the cookie consent manager is already open.
         *
         * @property isOpened
         * @type {Boolean}
         */
        isOpened: false,

        /**
         * Contains the name for preference cookie.
         *
         * @property preferenceCookieName
         * @type {String}
         */
        preferenceCookieName: 'cookiePreferences',

        /**
         * Contains the cookie permission jQuery plugin.
         *
         * @property cookiePermissionPlugin
         * @type {Object}
         */
        cookiePermissionPlugin: null,

        init: function () {
            this.applyDataAttributes();

            this.registerEvents();
            this.cookiePermissionPlugin = $('*[data-cookie-permission="true"]').data('plugin_swCookiePermission');
        },

        registerEvents: function () {
            this.$el.find(this.opts.closeModalSelector).on('click', $.proxy(this.onCloseClicked, this));
            this.$el.find(this.opts.cookieGroupToggleInputSelector).on('change', $.proxy(this.onGroupToggleChanged, this));
            this.$el.find(this.opts.cookieActiveInputSelector).on('change', $.proxy(this.onCookieToggleChanged, this));
            this.$el.find(this.opts.saveButtonSelector).on('click', $.proxy(this.onSave, this));
            this.$el.find(this.opts.cookieLabelSelector).on('click', $.proxy(this.onClickCookieName, this));

            this._on(this.opts.openConsentManagerButton, 'click', $.proxy(this.openConsentManager, this));
        },

        assignCookieData: function () {
            if (!this.hasSetPreferences()) {
                return;
            }

            this.preferences = JSON.parse($.getCookie(this.preferenceCookieName));
            this.parsePreferences();
        },

        parsePreferences: function () {
            var me = this,
                groupNames = Object.keys(me.preferences['groups']),
                group,
                groupRequired,
                cookieNames,
                cookie;

            $.each(groupNames, function (groupIndex, groupName) {
                group = me.findGroupByName(groupName);
                groupRequired = group.find(me.opts.cookieActiveInputLabelSelector).hasClass(me.opts.requiredClass);
                me.toggleGroup(group, groupRequired || me.preferences['groups'][groupName].active);

                cookieNames = Object.keys(me.preferences['groups'][groupName].cookies);

                $.each(cookieNames, function (cookieIndex, cookieName) {
                    cookie = me.findCookieByName(cookieName);
                    me.toggleCookie(cookie, groupRequired || me.preferences['groups'][groupName].cookies[cookieName].active);

                    me.checkActiveStateForAllCookiesOfGroup(group, groupRequired || me.preferences['groups'][groupName].cookies[cookieName].active);
                });
            });
        },

        findGroupByName: function (groupName) {
            return $(this.opts.cookieGroupNameSelector + '[value=' + groupName + ']').parent();
        },

        findCookieByName: function (cookieName) {
            return $(this.opts.cookieNameSelector + '[value=' + cookieName + ']').parent();
        },

        hasSetPreferences: function () {
            return $.getCookie(this.preferenceCookieName) !== undefined;
        },

        openConsentManager: function () {
            this.open();

            if (window.cookieRemoval !== 2) {
                this.cookiePermissionPlugin.hideElement();
            }
        },

        buildCookiePreferences: function (allTrue) {
            var opts = this.opts,
                cookieGroups = this.$el.find(this.opts.cookieGroupSelector),
                preferences = { 'groups': {}},
                date = new Date(),
                uniqueNames = [];

            allTrue = allTrue || false;

            cookieGroups.each(function (index, cookieGroup) {
                var groupName = $(cookieGroup).find(opts.cookieGroupNameSelector).val(),
                    isActive = allTrue ? allTrue : $(cookieGroup).find(opts.cookieGroupToggleInputSelector).is(':checked'),
                    cookies = $(cookieGroup).find(opts.cookieContainerSelector);

                uniqueNames.push(groupName);

                if (!preferences['groups'].hasOwnProperty(groupName)) {
                    preferences['groups'][groupName] = {
                        name: groupName,
                        cookies: {}
                    };
                }

                preferences['groups'][groupName].active = isActive;

                cookies.each(function (cookieIndex, cookie) {
                    var cookieName = $(cookie).find(opts.cookieNameSelector).val(),
                        isCookieActive = allTrue ? allTrue : $(cookie).find(opts.cookieActiveInputSelector).is(':checked');

                    uniqueNames.push(cookieName);

                    if (!preferences['groups'][groupName].cookies.hasOwnProperty(cookieName)) {
                        preferences['groups'][groupName].cookies[cookieName] = {
                            name: cookieName
                        };
                    }

                    preferences['groups'][groupName].cookies[cookieName].active = isCookieActive;
                });
            });

            uniqueNames.sort();
            preferences.hash = window.btoa(JSON.stringify(uniqueNames));

            date.setTime(date.getTime() + (180 * 24 * 60 * 60 * 1000));
            document.cookie = this.preferenceCookieName + '=' + JSON.stringify(preferences) + ';path=' + this.getBasePath() +';expires=' + date.toGMTString() + ';';

            $.publish('plugin/swCookieConsentManager/onBuildCookiePreferences', [ this, preferences ]);
        },

        onClickCookieName: function (event) {
            var cookieNameEl = $(event.currentTarget),
                cookieCt = cookieNameEl.parent(this.opts.cookieContainerSelector),
                inputEl = cookieCt.find(this.opts.cookieActiveInputSelector);

            inputEl.click();
        },

        toggleAllCookiesFromGroup: function (cookies, active) {
            var me = this;

            cookies.each(function (cookieIndex, cookie) {
                me.toggleCookie($(cookie), active);
            });
        },

        checkActiveStateForAllCookiesOfGroup: function (group, cookieActiveStatus) {
            var opts = this.opts,
                cookies = group.find(this.opts.cookieContainerSelector),
                allOfSame = true,
                groupInput = group.find(this.opts.cookieGroupToggleInputSelector),
                cookieInput;

            cookies.each(function (cookieIndex, cookie) {
                cookie = $(cookie);
                cookieInput = cookie.find(opts.cookieActiveInputSelector);

                if (cookieInput.is(':checked') !== cookieActiveStatus) {
                    allOfSame = false;
                }
            });

            if (allOfSame) {
                groupInput.prop('indeterminate', false);
                this.toggleGroup(group, cookieActiveStatus);
            } else {
                groupInput.prop('indeterminate', true);
            }
        },

        toggleGroup: function (group, activeStatus) {
            group.find(this.opts.cookieGroupToggleInputSelector).prop('checked', activeStatus);
        },

        toggleCookie: function (cookie, activeStatus) {
            cookie.find(this.opts.cookieActiveInputSelector).prop('checked', activeStatus);
        },

        onSave: function () {
            this.buildCookiePreferences();
            this.close();
            this.removeDeclinedAndAcceptedCookie();
            $.overlay.close();

            $.publish('plugin/swCookieConsentManager/onSave', [ this ]);
        },

        onGroupToggleChanged: function (event) {
            var opts = this.opts,
                groupToggle =  $(event.currentTarget),
                group = groupToggle.parents(opts.cookieGroupSelector),
                cookies = group.find(opts.cookieContainerSelector);

            this.toggleAllCookiesFromGroup(cookies, groupToggle.is(':checked'));

            $.publish('plugin/swCookieConsentManager/onGroupToggleChanged', [ this, groupToggle ]);
        },

        onCookieToggleChanged: function (event) {
            var opts = this.opts,
                cookieToggle = $(event.currentTarget),
                cookie = cookieToggle.parents(opts.cookieContainerSelector),
                group = cookie.parents(opts.cookieGroupSelector);

            this.checkActiveStateForAllCookiesOfGroup(group, cookieToggle.is(':checked'));

            $.publish('plugin/swCookieConsentManager/onCookieToggleChanged', [ this, cookieToggle ]);
        },

        onCloseClicked: function () {
            $.overlay.close();
            this.close();
        },

        open: function () {
            if (this.isOpened) {
                return;
            }

            this.assignCookieData();

            this.$el.removeClass('block-transition');
            this.$el.show();
            this.$el.addClass(this.opts.openClass);

            this.isOpened = true;

            $.overlay.open({
                onClose: $.proxy(this.close, this)
            });
        },

        close: function () {
            if (!this.isOpened) {
                return;
            }

            this.$el.removeClass(this.opts.openClass);
            this.isOpened = false;
        },

        removeDeclinedAndAcceptedCookie: function () {
            $.removeCookie('cookieDeclined');
            $.removeCookie('allowCookie');

            window.localStorage.removeItem(this.cookiePermissionPlugin.storageKey);
        },

        getBasePath: function () {
            return window.csrfConfig.basePath || '/';
        }
    });
})(jQuery, window);

function openCookieConsentManager () {
    var plugin = $('*[data-cookie-consent-manager="true"]').data('plugin_swCookieConsentManager');
    plugin.openConsentManager();
}