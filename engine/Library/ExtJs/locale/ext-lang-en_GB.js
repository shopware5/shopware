/**
 * List compiled by mystix on the extjs.com forums.
 * Thank you Mystix!
 *
 * English (UK) Translations
 * updated to 2.2 by Condor (8 Aug 2008)
 */
Ext.onReady(function() {
    var cm = Ext.ClassManager,
        exists = Ext.Function.bind(cm.get, cm);

    if (Ext.Updater) {
        Ext.Updater.defaults.indicatorText = '<div class="loading-indicator">Loading...</div>';
    }

    Ext.define('Ext.locale.en_GB.view.View', {
        override: 'Ext.view.View',
        emptyText: ''
    });

    Ext.define('Ext.locale.en_GB.grid.Panel', {
        override: 'Ext.grid.Panel',
        ddText: '{0} selected row{1}'
    });

    // changing the msg text below will affect the LoadMask
    Ext.define('Ext.locale.en_GB.view.AbstractView', {
        override: 'Ext.view.AbstractView',
        msg: 'Loading...'
    });

    if (Ext.Date) {
        Ext.Date.monthNames = ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'];

        Ext.Date.getShortMonthName = function(month) {
            return Ext.Date.monthNames[month].substring(0, 3);
        };

        Ext.Date.monthNumbers = {
            Jan: 0,
            Feb: 1,
            Mar: 2,
            Apr: 3,
            May: 4,
            Jun: 5,
            Jul: 6,
            Aug: 7,
            Sep: 8,
            Oct: 9,
            Nov: 10,
            Dec: 11
        };

        Ext.Date.getMonthNumber = function(name) {
            return Ext.Date.monthNumbers[name.substring(0, 1).toUpperCase() + name.substring(1, 3).toLowerCase()];
        };

        Ext.Date.dayNames = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];

        Ext.Date.getShortDayName = function(day) {
            return Ext.Date.dayNames[day].substring(0, 3);
        };

        Ext.Date.parseCodes.S.s = '(?:st|nd|rd|th)';
    }

    if (Ext.MessageBox) {
        Ext.MessageBox.buttonText = {
            ok: 'OK',
            cancel: 'Cancel',
            yes: 'Yes',
            no: 'No'
        };
    }

    if (exists('Ext.util.Format')) {
        Ext.apply(Ext.util.Format, {
            thousandSeparator: ',',
            decimalSeparator: '.',
            currencySign: '£',
            // UK Pound
            dateFormat: 'd/m/Y'
        });
    }

    Ext.define('Ext.locale.en_GB.picker.Date', {
        override: 'Ext.picker.Date',
        todayText: 'Today',
        minText: 'This date is before the minimum date',
        maxText: 'This date is after the maximum date',
        disabledDaysText: '',
        disabledDatesText: '',
        monthNames: Ext.Date.monthNames,
        dayNames: Ext.Date.dayNames,
        nextText: 'Next Month (Control+Right)',
        prevText: 'Previous Month (Control+Left)',
        monthYearText: 'Choose a month (Control+Up/Down to move years)',
        todayTip: '{0} (Spacebar)',
        format: 'd/m/Y',
        startDay: 0
    });

    Ext.define('Ext.locale.en_GB.picker.Month', {
        override: 'Ext.picker.Month',
        okText: '&#160;OK&#160;',
        cancelText: 'Cancel'
    });

    Ext.define('Ext.locale.en_GB.toolbar.Paging', {
        override: 'Ext.PagingToolbar',
        beforePageText: 'Page',
        afterPageText: 'of {0}',
        firstText: 'First Page',
        prevText: 'Previous Page',
        nextText: 'Next Page',
        lastText: 'Last Page',
        refreshText: 'Refresh',
        displayMsg: 'Displaying {0} - {1} of {2}',
        emptyMsg: 'No data to display'
    });

    Ext.define('Ext.locale.en_GB.form.Basic', {
        override: 'Ext.form.Basic',
        waitTitle: 'Please Wait...'
    });

    Ext.define('Ext.locale.en_GB.form.field.Base', {
        override: 'Ext.form.field.Base',
        invalidText: 'The value in this field is invalid'
    });

    Ext.define('Ext.locale.en_GB.form.field.Text', {
        override: 'Ext.form.field.Text',
        minLengthText: 'The minimum length for this field is {0}',
        maxLengthText: 'The maximum length for this field is {0}',
        blankText: 'This field is required',
        regexText: '',
        emptyText: null
    });

    Ext.define('Ext.locale.en_GB.form.field.Number', {
        override: 'Ext.form.field.Number',
        decimalSeparator: '.',
        decimalPrecision: 2,
        minText: 'The minimum value for this field is {0}',
        maxText: 'The maximum value for this field is {0}',
        nanText: '{0} is not a valid number'
    });

    Ext.define('Ext.locale.en_GB.form.field.Date', {
        override: 'Ext.form.field.Date',
        disabledDaysText: 'Disabled',
        disabledDatesText: 'Disabled',
        minText: 'The date in this field must be after {0}',
        maxText: 'The date in this field must be before {0}',
        invalidText: '{0} is not a valid date - it must be in the format {1}',
        format: 'd/m/y',
        altFormats: 'd/m/Y|d/m/y|d-m-y|d-m-Y|d/m|d-m|dm|dmy|dmY|d|Y-m-d'
    });

    Ext.define('Ext.locale.en_GB.form.field.ComboBox', {
        override: 'Ext.form.field.ComboBox',
        valueNotFoundText: undefined
    }, function() {
        Ext.apply(Ext.form.field.ComboBox.prototype.defaultListConfig, {
            loadingText: 'Loading...'
        });
    });

    if (exists('Ext.form.field.VTypes')) {
        Ext.apply(Ext.form.field.VTypes, {
            emailText: 'This field should be an e-mail address in the format "user@example.com"',
            urlText: 'This field should be a URL in the format "http:/' + '/www.example.com"',
            alphaText: 'This field should only contain letters and _',
            alphanumText: 'This field should only contain letters, numbers and _'
        });
    }

    Ext.define('Ext.locale.en_GB.form.field.HtmlEditor', {
        override: 'Ext.form.field.HtmlEditor',
        createLinkText: 'Please enter the URL for the link:'
    }, function() {
        Ext.apply(Ext.form.field.HtmlEditor.prototype, {
            buttonTips: {
                bold: {
                    title: 'Bold (Ctrl+B)',
                    text: 'Make the selected text bold.',
                    cls: Ext.baseCSSPrefix + 'html-editor-tip'
                },
                italic: {
                    title: 'Italic (Ctrl+I)',
                    text: 'Make the selected text italic.',
                    cls: Ext.baseCSSPrefix + 'html-editor-tip'
                },
                underline: {
                    title: 'Underline (Ctrl+U)',
                    text: 'Underline the selected text.',
                    cls: Ext.baseCSSPrefix + 'html-editor-tip'
                },
                increasefontsize: {
                    title: 'Grow Text',
                    text: 'Increase the font size.',
                    cls: Ext.baseCSSPrefix + 'html-editor-tip'
                },
                decreasefontsize: {
                    title: 'Shrink Text',
                    text: 'Decrease the font size.',
                    cls: Ext.baseCSSPrefix + 'html-editor-tip'
                },
                backcolor: {
                    title: 'Text Highlight Color',
                    text: 'Change the background color of the selected text.',
                    cls: Ext.baseCSSPrefix + 'html-editor-tip'
                },
                forecolor: {
                    title: 'Font Color',
                    text: 'Change the color of the selected text.',
                    cls: Ext.baseCSSPrefix + 'html-editor-tip'
                },
                justifyleft: {
                    title: 'Align Text Left',
                    text: 'Align text to the left.',
                    cls: Ext.baseCSSPrefix + 'html-editor-tip'
                },
                justifycenter: {
                    title: 'Center Text',
                    text: 'Center text in the editor.',
                    cls: Ext.baseCSSPrefix + 'html-editor-tip'
                },
                justifyright: {
                    title: 'Align Text Right',
                    text: 'Align text to the right.',
                    cls: Ext.baseCSSPrefix + 'html-editor-tip'
                },
                insertunorderedlist: {
                    title: 'Bullet List',
                    text: 'Start a bulleted list.',
                    cls: Ext.baseCSSPrefix + 'html-editor-tip'
                },
                insertorderedlist: {
                    title: 'Numbered List',
                    text: 'Start a numbered list.',
                    cls: Ext.baseCSSPrefix + 'html-editor-tip'
                },
                createlink: {
                    title: 'Hyperlink',
                    text: 'Make the selected text a hyperlink.',
                    cls: Ext.baseCSSPrefix + 'html-editor-tip'
                },
                sourceedit: {
                    title: 'Source Edit',
                    text: 'Switch to source editing mode.',
                    cls: Ext.baseCSSPrefix + 'html-editor-tip'
                }
            }
        });
    });

    Ext.define('Ext.locale.en_GB.grid.header.Container', {
        override: 'Ext.grid.header.Container',
        sortAscText: 'Sort Ascending',
        sortDescText: 'Sort Descending',
        columnsText: 'Columns'
    });

    Ext.define('Ext.locale.en_GB.grid.GroupingFeature', {
        override: 'Ext.grid.GroupingFeature',
        emptyGroupText: '(None)',
        groupByText: 'Group By This Field',
        showGroupsText: 'Show in Groups'
    });

    Ext.define('Ext.locale.en_GB.grid.PropertyColumnModel', {
        override: 'Ext.grid.PropertyColumnModel',
        nameText: 'Name',
        valueText: 'Value',
        dateFormat: 'j/m/Y',
        trueText: 'true',
        falseText: 'false'
    });

    Ext.define('Ext.locale.en_GB.form.field.Time', {
        override: 'Ext.form.field.Time',
        minText: 'The time in this field must be equal to or after {0}',
        maxText: 'The time in this field must be equal to or before {0}',
        invalidText: '{0} is not a valid time',
        format: 'g:i A',
        altFormats: 'g:ia|g:iA|g:i a|g:i A|h:i|g:i|ga|ha|gA|h a|g a|g A|gi|hi|gia|hia|g|H'
    });

    Ext.define('Ext.locale.en_GB.form.CheckboxGroup', {
        override: 'Ext.form.CheckboxGroup',
        blankText: 'You must select at least one item in this group'
    });

    Ext.define('Ext.locale.en_GB.form.RadioGroup', {
        override: 'Ext.form.RadioGroup',
        blankText: 'You must select one item in this group'
    });

    /**
     * Shopware 5
     *
     * Code beyond this point is no longer part of ExtJs core
     * and must be ported manually when ExtJs is updated
     */
    Ext.define('Ext.app.en_GB.Application', {
        override: 'Ext.app.Application',

        loadingMessage: 'Loading [0] ...'
    });
    Ext.define('Shopware.apps.Base.view.element.en_GB.BooleanSelect', {
        override: 'Shopware.apps.Base.view.element.BooleanSelect',

        store: [
            ['', 'Inherited'],
            [true, 'Yes'],
            [false, 'No']
        ]
    });
    Ext.define('Shopware.MediaManager.en_GB.MediaSelection', {
        override: 'Shopware.MediaManager.MediaSelection',

        buttonText: 'Select own files'
    });
    Ext.define('Shopware.container.en_GB.Viewport', {
        override: 'Shopware.container.Viewport',

        snippets: {
            title: 'Create desktop',
            message: 'Please choose a title for the new desktop'
        }
    });
    Ext.define('Shopware.DataView.en_GB.GooglePreview', {
        override: 'Shopware.DataView.GooglePreview',

        refreshButtonText: 'Refresh'
    });
    Ext.define('Shopware.form.field.en_GB.ArticleSearch', {
        override: 'Shopware.form.field.ArticleSearch',

        confirmButtonText: 'Save assigned articles',
        cancelButtonText: 'Reset articles',

        snippets: {
            emptyText: 'Search...',
            assignedArticles: 'Assigned articles',
            articleName: 'Article name',
            orderNumber: 'Order number',
            dropDownTitle: 'Articles'
        }
    });
    Ext.define('Shopware.listing.en_GB.InfoPanel', {
        override: 'Shopware.listing.InfoPanel',
        title: 'Detailed information',
        emptyText: 'No entry selected.'
    });
    Ext.define('Shopware.window.en_GB.Detail', {
        override: 'Shopware.window.Detail',
        cancelButtonText: 'Cancel',
        saveButtonText: 'Save'
    });
    Ext.define('Shopware.window.en_GB.Progress', {
        override: 'Shopware.window.Progress',
        title: 'Delete items',
        cancelButtonText: 'Cancel process',
        closeButtonText: 'Close window',
        successHeader: 'Success',
        requestHeader: 'Request',
        errorHeader: 'Error message',
        requestResultTitle: 'Request results',
        processCanceledText: 'Process canceled at position [0] of [1]'
    });
    Ext.define('Shopware.form.field.en_GB.TinyMCE', {
        override: 'Shopware.form.field.TinyMCE',

        noSourceErrorText: "The TinyMCE editor source files aren't included in the project"
    });
    Ext.define('Shopware.form.en_GB.PluginPanel', {
        override: 'Shopware.form.PluginPanel',

        noFormIdConfiguredErrorText: 'No formId is passed to the component configuration',
        formNotLoadedErrorText: "The form store couldn't be loaded successfully.",

        snippets: {
            resetButton: 'Reset',
            saveButton: 'Save',
            description: 'Description',
            onSaveFormTitle: 'Save form',
            saveFormSuccess: 'Form "[name]" has been saved.',
            saveFormError: 'Form "[name]" could not be saved.'
        }
    });
    Ext.define('Shopware.global.en_GB.ErrorReporter', {
        override: 'Shopware.global.ErrorReporter',

        snippets: {
            general: {
                title: 'Shopware Error Reporter',
                error_title: 'Error information',
                browser_title: 'Browser information',
                cancel: 'Cancel'
            },
            xhr: {
                module: 'Module',
                request_path: 'Request path',
                http_error: 'HTTP error message',
                http_status: 'HTTP status code',
                error_desc: 'Error description',
                module_files: 'Module files',
                class_name: 'Class name',
                path: 'Path',
                type: 'Type',
                unknown_type: 'Unknown type',
                reload_module: 'Reload module'
            },
            eval: {
                reload_admin: 'Reload administration',
                error_type: 'Error type',
                error_msg: 'Error message'
            },
            browser: {
                os: 'Operating system',
                browser_engine: 'Browser engine',
                window_size: 'Window size',
                java_enabled: 'Java enabled',
                cookies_enabled: 'Cookies enabled',
                lang: 'Language',
                plugins: 'Browser plugins',
                plugin_name: 'Plugin name',
                plugin_path: 'Plugin path'
            },
            response: {
                name: 'Server response',
                errorOverview: 'Error overview'
            }
        }
    });
    Ext.define('Shopware.window.plugin.en_GB.Hud', {
        override: 'Shopware.window.plugin.Hud',

        hudTitle: 'Elements library',
        hudStoreErrorMessage: function(className) {
            return className + ' needs the property "hudStore" which represents the store used by the hub panel to create the draggable items.';
        }
    });
    Ext.apply(Ext.form.VTypes, {
        passwordText: 'The inserted passwords are not equal'
    });
    Ext.apply(Ext.form.field.VTypes, {
        missingValidationErrorText: 'The remote vType validation needs a validationErrorMsg property'
    });
    Ext.define('Ext.grid.en_GB.RowEditor', {
        override: 'Ext.grid.RowEditor',
        saveBtnText: 'Update',
        cancelBtnText: 'Cancel',
        errorsText: 'Errors',
        dirtyText: 'You need to commit or cancel your changes'
    });
    Ext.define('Ext.util.en_GB.FileUpload', {
        override: 'Ext.util.FileUpload',
        snippets: {
            uploadReady: 'file(s) uploaded',
            filesFrom: 'from',
            messageText: '[0] file(s) uploaded',
            messageTitle: 'Media manager',
            legacyMessage: "Your browser doesn't support the necessary feature to support drag & drop uploads.",
            maxUploadSizeTitle: 'The file exceeds the file size limit',
            maxUploadSizeText: 'The selected file exceeds the configured maximum file size for uploads. Please select another file to upload.',
            extensionNotAllowedTitle: 'File extension not supported',
            extensionNotAllowedText: 'The file extension \"[0]\" is not supported. Please select another file to upload.',
            blackListTitle: 'Blacklist',
            blackListMessage: 'File extension [0] isn\'t allowed'
        }
    });
    Ext.define('Shopware.en_GB.Notification', {
        override: 'Shopware.Notification',
        closeText: 'Close'
    });
    Ext.define('Enlight.app.en_GB.Window', {
        override: 'Enlight.app.Window',

        closePopupTitle: 'Close module',
        closePopupMessage: 'This will close all windows of the "__MODULE__" module. Do you want to continue?'
    });
    Ext.define('Shopware.detail.en_GB.Controller', {
        override: 'Shopware.detail.Controller',
        closeText: 'Close',
        saveSuccessTitle: 'Success',
        saveSuccessMessage: 'Item saved successfully',
        violationErrorTitle: 'Violation errors',
        invalidFormTitle: 'Form validation error',
        invalidFormMessage: 'The form contains invalid data, please check the inserted values.'
    });
    Ext.define('Shopware.form.field.en_GB.Media', {
        override: 'Shopware.form.field.Media',
        selectButtonText: 'Select media',
        resetButtonText: 'Reset media'
    });
    Ext.define('Shopware.grid.en_GB.Association', {
        override: 'Shopware.grid.Association',
        searchComboLabel: 'Search for'
    });
    Ext.define('Shopware.grid.en_GB.Controller', {
        override: 'Shopware.grid.Controller',
        deleteConfirmTitle: 'Delete items',
        deleteConfirmText: 'Are you sure you want to delete the selected items?',
        deleteInfoText: '<b>The records will be deleted.</b> <br>To cancel the process, you can use the <b><i>`Cancel process`</i></b> Button. Depending on the selected volume of data may take several seconds to complete this process.',
        deleteProgressBarText: 'Item [0] of [1]'
    });
    Ext.define('Shopware.listing.en_GB.FilterPanel', {
        override: 'Shopware.listing.FilterPanel',
        infoTextSnippet: 'Activate the filter fields over the checkbox which displayed for each field. Activated fields will be joined with an AND condition.',
        filterButtonText: 'Filter result',
        resetButtonText: 'Reset filters'
    });
    Ext.define('Shopware.grid.en_GB.Panel', {
        override: 'Shopware.grid.Panel',
        pageSizeLabel: 'Items per page',
        addButtonText: 'Add item',
        deleteButtonText: 'Delete all selected',
        searchFieldText: 'Search...'
    });
    Ext.define('Shopware.grid.plugin.en_GB.Translation', {
        override: 'Shopware.grid.plugin.Translation',
        snippets: {
            tooltip: 'Translate'
        }
    });
    Ext.define('Shopware.apps.Base.view.element.en_GB.ProductBoxLayoutSelect', {
        override: 'Shopware.apps.Base.view.element.ProductBoxLayoutSelect',

        fieldLabel: 'Product layout',
        helpText: 'Product layout allows you to control how your products are presented on the category page. Choose between three different layouts to fine-tune your product display. You can select a layout for each category or automatically adopt the settings from the parent category.'
    });

    Ext.define('Shopware.apps.Base.store.en_GB.ProductBoxLayout', {
        override: 'Shopware.apps.Base.store.ProductBoxLayout',

        snippets: {
            displayExtendLayout: {
                label: 'Parent setting',
                description: 'The layout of the product box will be set by the value of the parent category.'
            },
            displayBasicLayout: {
                label: 'Detailed information',
                description: 'The layout of the product box will show very detailed information.'
            },
            displayMinimalLayout: {
                label: 'Only important information',
                description: 'The layout of the product box will only show the most important information.'
            },
            displayImageLayout: {
                label: 'Big image',
                description: 'The layout of the product box is based on a big image of the product.'
            },
            displayListLayout: {
                label: 'Product list',
                description: 'The layout of the product box will show very detailed information but only one product in a row.'
            }
        }
    });

    Ext.define('Shopware.notification.en_GB.SubscriptionWarning', {
        override: 'Shopware.notification.SubscriptionWarning',

        snippets: {
            licence_upgrade_warning: '[0] plugin(s) require a license upgrade. <a target="_blank" href="https://account.shopware.com">Open account</a><br /><br /><b>Required upgrades:</b><br />[1]',
            subscription_warning: 'Subscription(s) for [0] plugin(s) have expired. <a target="_blank" href="https://account.shopware.com">Open account</a><br /><br /><b>Expired plugins:</b><br />[1]',
            expired_soon_subscription_warning: 'Subscription(s) for [0] plugin(s) will expire soon. <a target="_blank" href="https://account.shopware.com">Open account</a><br /><br /><b>Soon expiring plugins:</b><br />[1]',
            expired_soon_subscription_days_warning: ' days',
            invalid_licence: 'License(s) of [0] plugin(s) are invalid. <a target="_blank" href="https://account.shopware.com">Open account</a><br /><b>Invalid licences:</b><br />[1]',
            shop_license_upgrade: 'The license upgrade for the shop hasn\'t been executed yet. <a target="_blank" href="https://account.shopware.com">Open account</a>',
            no_license: 'You may be a victim of counterfeiting. <a target="_blank" href="https://account.shopware.com">Open account</a><br /><br /><b>No valid license found for plugins:</b><br />[1]',
            expiring_license: 'Expiring license(s)',
            expired_license: 'Expired license(s)',
            expiring_license_warning: 'License(s) of [0]x plugin(s) are soon expiring.<br /><br /><b>Soon expired license(s):</b><br />[1]',
            expired_license_warning: 'At least one license of your used plugins has expired. <br>Please review this in your <a href="https://account.shopware.com" target="_blank">Shopware account</a> under <strong style="font-weight: bold">"Licenses"</strong> and update your license immediately.',
            unknown_license: 'Unlicensed plugins',
            confirm_plugin_deactivation: 'You have installed unlicensed plugins. Please buy a valid license or install and remove the plugins with help of the Plugin Manager now.',
            confirm_open_pluginmanager: 'You have installed unlicensed plugins. Do you want to open the Plugin Manager now to check your plugins?',
            subscription: 'Subscription',
            subscription_hide_message: 'Would you like to hide this message for a week?',
            openPluginOverview: 'Plugin overview',
            importantInformation: 'Important Information',
            noShopSecretWarning: 'In order to receive information about updates and install plugins, you need to log in to your Shopware account. If you don\'t have a Shopware account yet, you can easily register.',
            login: 'Login now'
        }
    });
});
