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

    Ext.define("Ext.locale.en_GB.view.View", {
        override: "Ext.view.View",
        emptyText: ""
    });

    Ext.define("Ext.locale.en_GB.grid.Panel", {
        override: "Ext.grid.Panel",
        ddText: "{0} selected row{1}"
    });

    // changing the msg text below will affect the LoadMask
    Ext.define("Ext.locale.en_GB.view.AbstractView", {
        override: "Ext.view.AbstractView",
        msg: "Loading..."
    });

    if (Ext.Date) {
        Ext.Date.monthNames = ["January", "February", "March", "April", "May", "June", "July", "August", "September", "October", "November", "December"];

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

        Ext.Date.dayNames = ["Sunday", "Monday", "Tuesday", "Wednesday", "Thursday", "Friday", "Saturday"];

        Ext.Date.getShortDayName = function(day) {
            return Ext.Date.dayNames[day].substring(0, 3);
        };

        Ext.Date.parseCodes.S.s = "(?:st|nd|rd|th)";
    }

    if (Ext.MessageBox) {
        Ext.MessageBox.buttonText = {
            ok: "OK",
            cancel: "Cancel",
            yes: "Yes",
            no: "No"
        };
    }

    if (exists('Ext.util.Format')) {
        Ext.apply(Ext.util.Format, {
            thousandSeparator: ',',
            decimalSeparator: '.',
            currencySign: '�',
            // UK Pound
            dateFormat: 'd/m/Y'
        });
    }

    Ext.define("Ext.locale.en_GB.picker.Date", {
        override: "Ext.picker.Date",
        todayText: "Today",
        minText: "This date is before the minimum date",
        maxText: "This date is after the maximum date",
        disabledDaysText: "",
        disabledDatesText: "",
        monthNames: Ext.Date.monthNames,
        dayNames: Ext.Date.dayNames,
        nextText: 'Next Month (Control+Right)',
        prevText: 'Previous Month (Control+Left)',
        monthYearText: 'Choose a month (Control+Up/Down to move years)',
        todayTip: "{0} (Spacebar)",
        format: "d.m.Y",
        startDay: 0
    });

    Ext.define("Ext.locale.en_GB.picker.Month", {
        override: "Ext.picker.Month",
        okText: "&#160;OK&#160;",
        cancelText: "Cancel"
    });

    Ext.define("Ext.locale.en_GB.toolbar.Paging", {
        override: "Ext.PagingToolbar",
        beforePageText: "Page",
        afterPageText: "of {0}",
        firstText: "First Page",
        prevText: "Previous Page",
        nextText: "Next Page",
        lastText: "Last Page",
        refreshText: "Refresh",
        displayMsg: "Displaying {0} - {1} of {2}",
        emptyMsg: 'No data to display'
    });

    Ext.define("Ext.locale.en_GB.form.Basic", {
        override: "Ext.form.Basic",
        waitTitle: "Please Wait..."
    });

    Ext.define("Ext.locale.en_GB.form.field.Base", {
        override: "Ext.form.field.Base",
        invalidText: "The value in this field is invalid"
    });

    Ext.define("Ext.locale.en_GB.form.field.Text", {
        override: "Ext.form.field.Text",
        minLengthText: "The minimum length for this field is {0}",
        maxLengthText: "The maximum length for this field is {0}",
        blankText: "This field is required",
        regexText: "",
        emptyText: null
    });

    Ext.define("Ext.locale.en_GB.form.field.Number", {
        override: "Ext.form.field.Number",
        decimalSeparator: ".",
        decimalPrecision: 2,
        minText: "The minimum value for this field is {0}",
        maxText: "The maximum value for this field is {0}",
        nanText: "{0} is not a valid number"
    });

    Ext.define("Ext.locale.en_GB.form.field.Date", {
        override: "Ext.form.field.Date",
        disabledDaysText: "Disabled",
        disabledDatesText: "Disabled",
        minText: "The date in this field must be after {0}",
        maxText: "The date in this field must be before {0}",
        invalidText: "{0} is not a valid date - it must be in the format {1}",
        format: "d.m.Y",
        altFormats: "d/m/Y|d/m/y|d-m-y|d-m-Y|d/m|d-m|dm|dmy|dmY|d|Y-m-d"
    });

    Ext.define("Ext.locale.en_GB.form.field.ComboBox", {
        override: "Ext.form.field.ComboBox",
        valueNotFoundText: undefined
    }, function() {
        Ext.apply(Ext.form.field.ComboBox.prototype.defaultListConfig, {
            loadingText: "Loading..."
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

    Ext.define("Ext.locale.en_GB.form.field.HtmlEditor", {
        override: "Ext.form.field.HtmlEditor",
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

    Ext.define("Ext.locale.en_GB.grid.header.Container", {
        override: "Ext.grid.header.Container",
        sortAscText: "Sort Ascending",
        sortDescText: "Sort Descending",
        columnsText: "Columns"
    });

    Ext.define("Ext.locale.en_GB.grid.GroupingFeature", {
        override: "Ext.grid.GroupingFeature",
        emptyGroupText: '(None)',
        groupByText: 'Group By This Field',
        showGroupsText: 'Show in Groups'
    });

    Ext.define("Ext.locale.en_GB.grid.PropertyColumnModel", {
        override: "Ext.grid.PropertyColumnModel",
        nameText: "Name",
        valueText: "Value",
        dateFormat: "j/m/Y",
        trueText: "true",
        falseText: "false"
    });

    Ext.define("Ext.locale.en_GB.form.field.Time", {
        override: "Ext.form.field.Time",
        minText: "The time in this field must be equal to or after {0}",
        maxText: "The time in this field must be equal to or before {0}",
        invalidText: "{0} is not a valid time",
        format: "g:i A",
        altFormats: "g:ia|g:iA|g:i a|g:i A|h:i|g:i|H:i|ga|ha|gA|h a|g a|g A|gi|hi|gia|hia|g|H"
    });

    Ext.define("Ext.locale.en_GB.form.CheckboxGroup", {
        override: "Ext.form.CheckboxGroup",
        blankText: "You must select at least one item in this group"
    });

    Ext.define("Ext.locale.en_GB.form.RadioGroup", {
        override: "Ext.form.RadioGroup",
        blankText: "You must select one item in this group"
    });

    /**
     * Shopware 4.0
     *
     * Code beyond this point is no longer part of ExtJs core
     * and must be ported manually when ExtJs is updated
     */
    Ext.define('Ext.app.en_GB.Application', {
        override:'Ext.app.Application',

        loadingMessage: 'Loading [0] ...'
    });
    Ext.define('Shopware.apps.Base.view.element.en_GB.BooleanSelect', {
        override:'Shopware.apps.Base.view.element.BooleanSelect',

        store: [
            ["", 'Inherited'],
            [true, 'Yes'],
            [false, 'No']
        ]
    });
    Ext.define('Shopware.MediaManager.en_GB.MediaSelection', {
        override:'Shopware.MediaManager.MediaSelection',

        buttonText: 'Select own files'
    });
    Ext.define('Shopware.container.en_GB.Viewport', {
        override:'Shopware.container.Viewport',

        snippets: {
            title: 'Create desktop',
            message: 'Please choose a title for the new desktop'
        }
    });
    Ext.define('Shopware.DataView.en_GB.GooglePreview', {
        override:'Shopware.DataView.GooglePreview',

        refreshButtonText: 'Refresh'
    });
    Ext.define('Shopware.form.field.en_GB.ArticleSearch', {
        override:'Shopware.form.field.ArticleSearch',

        confirmButtonText: 'Save assigned articles',
        cancelButtonText: 'Reset articles'
    });
    Ext.define('Shopware.form.field.en_GB.ArticleSearch', {
        override:'Shopware.form.field.ArticleSearch',

        snippets: {
            emptyText: 'Search...',
            assignedArticles: 'Assigned articles',
            articleName: 'Article name',
            orderNumber: 'Order number'
        }
    });
    Ext.define('Shopware.form.field.en_GB.TinyMCE', {
        override:'Shopware.form.field.TinyMCE',

        noSourceErrorText: "The TinyMCE editor source files aren't included in the project"
    });
    Ext.define('Shopware.form.plugin.en_GB.Translation', {
        override:'Shopware.form.plugin.Translation',

        noSubApplicationSupportErrorText: "Your ExtJS application does not support sub applications"
    });
    Ext.define('Shopware.form.en_GB.PluginPanel', {
        override:'Shopware.form.PluginPanel',

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
        override:'Shopware.global.ErrorReporter',

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
            }
        }
    });
    Ext.define('Shopware.grid.plugin.en_GB.Translation', {
        override:'Shopware.grid.plugin.Translation',

        noSubApplicationSupportErrorText: "Your ExtJS application does not support sub applications"
    });
    Ext.define('Shopware.window.plugin.en_GB.Hud', {
        override:'Shopware.window.plugin.Hud',

        hudTitle: "Elements library",
        hudStoreErrorMessage: function(className) {
            return className + ' needs the property "hudStore" which represents the store used by the hub panel to create the draggable items.';
        }
    });
    Ext.apply(Ext.form.VTypes, {
        passwordText : 'The inserted passwords are not equal'
    });
    Ext.apply(Ext.form.field.VTypes, {
        missingValidationErrorText: 'The remote vType validation needs a validationErrorMsg property'
    });
});
