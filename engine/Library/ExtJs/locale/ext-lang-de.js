/**
 * German translation
 * 2007-Apr-07 update by schmidetzki and humpdi
 * 2007-Oct-31 update by wm003
 * 2009-Jul-10 update by Patrick Matsumura and Rupert Quaderer
 * 2010-Mar-10 update by Volker Grabsch
 */
Ext.onReady(function() {
    var cm = Ext.ClassManager,
        exists = Ext.Function.bind(cm.get, cm);

    if (Ext.Updater) {
        Ext.Updater.defaults.indicatorText = '<div class="loading-indicator">Übertrage Daten ...</div>';
    }

    Ext.define('Ext.locale.de.view.View', {
        override: 'Ext.view.View',
        emptyText: ''
    });

    Ext.define('Ext.locale.de.grid.Panel', {
        override: 'Ext.grid.Panel',
        ddText: '{0} Zeile(n) ausgewählt'
    });

    Ext.define('Ext.locale.de.TabPanelItem', {
        override: 'Ext.TabPanelItem',
        closeText: 'Diesen Tab schließen'
    });

    Ext.define('Ext.locale.de.form.Basic', {
        override: 'Ext.form.Basic',
        waitTitle: 'Bitte warten...'
    });

    Ext.define('Ext.locale.de.form.field.Base', {
        override: 'Ext.form.field.Base',
        invalidText: 'Der Wert des Feldes ist nicht korrekt'
    });

    // changing the msg text below will affect the LoadMask
    Ext.define('Ext.locale.de.view.AbstractView', {
        override: 'Ext.view.AbstractView',
        msg: 'Übertrage Daten...'
    });

    if (Ext.Date) {
        Ext.Date.monthNames = ['Januar', 'Februar', 'März', 'April', 'Mai', 'Juni', 'Juli', 'August', 'September', 'Oktober', 'November', 'Dezember'];

        Ext.Date.getShortMonthName = function(month) {
            return Ext.Date.monthNames[month].substring(0, 3);
        };

        Ext.Date.monthNumbers = {
            Jan: 0,
            Feb: 1,
            'M\u00e4r': 2,
            Apr: 3,
            Mai: 4,
            Jun: 5,
            Jul: 6,
            Aug: 7,
            Sep: 8,
            Okt: 9,
            Nov: 10,
            Dez: 11
        };

        Ext.Date.getMonthNumber = function(name) {
            return Ext.Date.monthNumbers[name.substring(0, 1).toUpperCase() + name.substring(1, 3).toLowerCase()];
        };

        Ext.Date.dayNames = ['Sonntag', 'Montag', 'Dienstag', 'Mittwoch', 'Donnerstag', 'Freitag', 'Samstag'];

        Ext.Date.getShortDayName = function(day) {
            return Ext.Date.dayNames[day].substring(0, 3);
        };
    }
    if (Ext.MessageBox) {
        Ext.MessageBox.buttonText = {
            ok: 'OK',
            cancel: 'Abbrechen',
            yes: 'Ja',
            no: 'Nein'
        };

        // As of 4.0.4, setting the buttonText above does not take effect properly. This should be removable in 4.1.0
        // (see issue EXTJSIV-3909)
        Ext.MessageBox.msgButtons['ok'].text = Ext.MessageBox.buttonText.ok;
        Ext.MessageBox.msgButtons['cancel'].text = Ext.MessageBox.buttonText.cancel;
        Ext.MessageBox.msgButtons['yes'].text = Ext.MessageBox.buttonText.yes;
        Ext.MessageBox.msgButtons['no'].text = Ext.MessageBox.buttonText.no;
    }

    if (exists('Ext.util.Format')) {
        Ext.util.Format.__number = Ext.util.Format.number;
        Ext.util.Format.number = function(v, format) {
            return Ext.util.Format.__number(v, format || '0.000,00/i');
        };

        Ext.apply(Ext.util.Format, {
            thousandSeparator: '.',
            decimalSeparator: ',',
            currencySign: '\u20ac',
            // German Euro
            dateFormat: 'd.m.Y'
        });
    }

    Ext.define('Ext.locale.de.picker.Date', {
        override: 'Ext.picker.Date',
        todayText: 'Heute',
        minText: 'Dieses Datum liegt von dem erstmöglichen Datum',
        maxText: 'Dieses Datum liegt nach dem letztmöglichen Datum',
        disabledDaysText: '',
        disabledDatesText: '',
        monthNames: Ext.Date.monthNames,
        dayNames: Ext.Date.dayNames,
        nextText: 'Nächster Monat (Strg/Control + Rechts)',
        prevText: 'Vorheriger Monat (Strg/Control + Links)',
        monthYearText: 'Monat auswählen (Strg/Control + Hoch/Runter, um ein Jahr auszuwählen)',
        todayTip: 'Heute ({0}) (Leertaste)',
        format: 'd.m.Y',
        startDay: 1
    });

    Ext.define('Ext.locale.de.picker.Month', {
        override: 'Ext.picker.Month',
        okText: '&#160;OK&#160;',
        cancelText: 'Abbrechen'
    });

    Ext.define('Ext.locale.de.toolbar.Paging', {
        override: 'Ext.PagingToolbar',
        beforePageText: 'Seite',
        afterPageText: 'von {0}',
        firstText: 'Erste Seite',
        prevText: 'vorherige Seite',
        nextText: 'nächste Seite',
        lastText: 'letzte Seite',
        refreshText: 'Aktualisieren',
        displayMsg: 'Anzeige Eintrag {0} - {1} von {2}',
        emptyMsg: 'Keine Daten vorhanden'
    });

    Ext.define('Ext.locale.de.form.field.Text', {
        override: 'Ext.form.field.Text',
        minLengthText: 'Bitte gib mindestens {0} Zeichen ein',
        maxLengthText: 'Bitte gib maximal {0} Zeichen ein',
        blankText: 'Dieses Feld darf nicht leer sein',
        regexText: '',
        emptyText: null
    });

    Ext.define('Ext.locale.de.form.field.Number', {
        override: 'Ext.form.field.Number',
        minText: 'Der Mindestwert für dieses Feld ist {0}',
        maxText: 'Der Maximalwert für dieses Feld ist {0}',
        nanText: '{0} ist keine Zahl',
        decimalSeparator: ','
    });

    Ext.define('Ext.locale.de.form.field.Date', {
        override: 'Ext.form.field.Date',
        disabledDaysText: 'nicht erlaubt',
        disabledDatesText: 'nicht erlaubt',
        minText: 'Das Datum in diesem Feld muss nach dem {0} liegen',
        maxText: 'Das Datum in diesem Feld muss vor dem {0} liegen',
        invalidText: '{0} ist kein gültiges Datum - es muss im Format {1} eingegeben werden',
        format: 'd.m.Y',
        altFormats: 'j.n.Y|j.n.y|j.n.|j.|j/n/Y|j/n/y|j-n-y|j-n-Y|j/n|j-n|dm|dmy|dmY|j|Y-n-j'
    });

    Ext.define('Ext.locale.de.form.field.ComboBox', {
        override: 'Ext.form.field.ComboBox',
        valueNotFoundText: undefined
    }, function() {
        Ext.apply(Ext.form.field.ComboBox.prototype.defaultListConfig, {
            loadingText: 'Lade Daten ...'
        });
    });

    if (exists('Ext.form.field.VTypes')) {
        Ext.apply(Ext.form.field.VTypes, {
            emailText: 'Dieses Feld sollte eine E-Mail-Adresse enthalten. Format: "user@example.com"',
            urlText: 'Dieses Feld sollte eine URL enthalten. Format: "http:/' + '/www.example.com"',
            alphaText: 'Dieses Feld darf nur Buchstaben enthalten und _',
            alphanumText: 'Dieses Feld darf nur Buchstaben und Zahlen enthalten und _'
        });
    }

    Ext.define('Ext.locale.de.form.field.HtmlEditor', {
        override: 'Ext.form.field.HtmlEditor',
        createLinkText: 'Bitte gib die URL für den Link ein:'
    }, function() {
        Ext.apply(Ext.form.field.HtmlEditor.prototype, {
            buttonTips: {
                bold: {
                    title: 'Fett (Ctrl+B)',
                    text: 'Erstellt den ausgewählten Text in Fettschrift.',
                    cls: Ext.baseCSSPrefix + 'html-editor-tip'
                },
                italic: {
                    title: 'Kursiv (Ctrl+I)',
                    text: 'Erstellt den ausgewählten Text in Schrägschrift.',
                    cls: Ext.baseCSSPrefix + 'html-editor-tip'
                },
                underline: {
                    title: 'Unterstrichen (Ctrl+U)',
                    text: 'Unterstreicht den ausgewählten Text.',
                    cls: Ext.baseCSSPrefix + 'html-editor-tip'
                },
                increasefontsize: {
                    title: 'Text vergößern',
                    text: 'Erhöht die Schriftgröße.',
                    cls: Ext.baseCSSPrefix + 'html-editor-tip'
                },
                decreasefontsize: {
                    title: 'Text verkleinern',
                    text: 'Verringert die Schriftgröße.',
                    cls: Ext.baseCSSPrefix + 'html-editor-tip'
                },
                backcolor: {
                    title: 'Text farblich hervorheben',
                    text: 'Hintergrundfarbe des ausgewählten Textes ändern.',
                    cls: Ext.baseCSSPrefix + 'html-editor-tip'
                },
                forecolor: {
                    title: 'Schriftfarbe',
                    text: 'Farbe des ausgewählten Textes ändern.',
                    cls: Ext.baseCSSPrefix + 'html-editor-tip'
                },
                justifyleft: {
                    title: 'Linksbündig',
                    text: 'Setzt den Text linksbündig.',
                    cls: Ext.baseCSSPrefix + 'html-editor-tip'
                },
                justifycenter: {
                    title: 'Zentrieren',
                    text: 'Zentriert den Text in Editor.',
                    cls: Ext.baseCSSPrefix + 'html-editor-tip'
                },
                justifyright: {
                    title: 'Rechtsbündig',
                    text: 'Setzt den Text rechtsbündig.',
                    cls: Ext.baseCSSPrefix + 'html-editor-tip'
                },
                insertunorderedlist: {
                    title: 'Aufzählungsliste',
                    text: 'Beginnt eine Aufzählungsliste mit Spiegelstrichen.',
                    cls: Ext.baseCSSPrefix + 'html-editor-tip'
                },
                insertorderedlist: {
                    title: 'Numerierte Liste',
                    text: 'Beginnt eine numerierte Liste.',
                    cls: Ext.baseCSSPrefix + 'html-editor-tip'
                },
                createlink: {
                    title: 'Hyperlink',
                    text: 'Erstellt einen Hyperlink aus dem ausgewählten text.',
                    cls: Ext.baseCSSPrefix + 'html-editor-tip'
                },
                sourceedit: {
                    title: 'Source bearbeiten',
                    text: 'Zur Bearbeitung des Quelltextes wechseln.',
                    cls: Ext.baseCSSPrefix + 'html-editor-tip'
                }
            }
        });
    });

    Ext.define('Ext.locale.de.grid.header.Container', {
        override: 'Ext.grid.header.Container',
        sortAscText: 'Aufsteigend sortieren',
        sortDescText: 'Absteigend sortieren',
        lockText: 'Spalte sperren',
        unlockText: 'Spalte freigeben (entsperren)',
        columnsText: 'Spalten'
    });

    Ext.define('Ext.locale.de.grid.GroupingFeature', {
        override: 'Ext.grid.GroupingFeature',
        emptyGroupText: '(Keine)',
        groupByText: 'Dieses Feld gruppieren',
        showGroupsText: 'In Gruppen anzeigen'
    });

    Ext.define('Ext.locale.de.grid.PropertyColumnModel', {
        override: 'Ext.grid.PropertyColumnModel',
        nameText: 'Name',
        valueText: 'Wert',
        dateFormat: 'd.m.Y'
    });

    Ext.define('Ext.locale.de.grid.BooleanColumn', {
        override: 'Ext.grid.BooleanColumn',
        trueText: 'aktiv',
        falseText: 'inaktiv'
    });

    Ext.define('Ext.locale.de.grid.NumberColumn', {
        override: 'Ext.grid.NumberColumn',
        format: '0.000,00/i'
    });

    Ext.define('Ext.locale.de.grid.DateColumn', {
        override: 'Ext.grid.DateColumn',
        format: 'd.m.Y'
    });

    Ext.define('Ext.locale.de.form.field.Time', {
        override: 'Ext.form.field.Time',
        minText: 'Die Zeit muss gleich oder nach {0} liegen',
        maxText: 'Die Zeit muss gleich oder vor {0} liegen',
        invalidText: '{0} ist keine gültige Zeit',
        format: 'H:i'
    });

    Ext.define('Ext.locale.de.form.CheckboxGroup', {
        override: 'Ext.form.CheckboxGroup',
        blankText: 'Du mußt mehr als einen Eintrag aus der Gruppe auswählen'
    });

    Ext.define('Ext.locale.de.form.RadioGroup', {
        override: 'Ext.form.RadioGroup',
        blankText: 'Du mußt einen Eintrag aus der Gruppe auswählen'
    });

    /**
     * Shopware 5
     *
     * Code beyond this point is no longer part of ExtJs core
     * and must be ported manually when ExtJs is updated
     */
    Ext.define('Ext.app.de.Application', {
        override: 'Ext.app.Application',

        loadingMessage: '[0] wird geladen ...'
    });
    Ext.define('Shopware.apps.Base.view.element.de.BooleanSelect', {
        override: 'Shopware.apps.Base.view.element.BooleanSelect',

        store: [
            ['', 'Vererbt'],
            [true, 'Ja'],
            [false, 'Nein']
        ]
    });
    Ext.define('Shopware.MediaManager.de.MediaSelection', {
        override: 'Shopware.MediaManager.MediaSelection',

        buttonText: 'Datei(en) auswählen...'
    });
    Ext.define('Shopware.container.de.Viewport', {
        override: 'Shopware.container.Viewport',

        snippets: {
            title: 'Desktop erstellen',
            message: 'Bitte wähle einen Titel für den neuen Desktop'
        }
    });
    Ext.define('Shopware.DataView.de.GooglePreview', {
        override: 'Shopware.DataView.GooglePreview',

        refreshButtonText: 'Aktualisieren'
    });
    Ext.define('Shopware.form.field.de.ArticleSearch', {
        override: 'Shopware.form.field.ArticleSearch',

        confirmButtonText: 'Zugeordneten Artikel speichern',
        cancelButtonText: 'Reset-Artikel',

        snippets: {
            emptyText: 'Suche...',
            assignedArticles: 'Zugeordneten Artikel',
            articleName: 'Artikelname',
            orderNumber: 'Bestell-Nr.',
            dropDownTitle: 'Artikel'
        }
    });
    Ext.define('Shopware.listing.de.InfoPanel', {
        override: 'Shopware.listing.InfoPanel',
        title: 'Detaillierte Informationen',
        emptyText: 'Kein Eintrag selektiert'
    });
    Ext.define('Shopware.window.de.Detail', {
        override: 'Shopware.window.Detail',
        cancelButtonText: 'Abbrechen',
        saveButtonText: 'Speichern'
    });
    Ext.define('Shopware.window.de.Progress', {
        override: 'Shopware.window.Progress',
        title: 'Einträge löschen',
        cancelButtonText: 'Abbrechen',
        closeButtonText: 'Fenster schließen',
        successHeader: 'Erfolgreich',
        requestHeader: 'Request',
        errorHeader: 'Fehlermeldung',
        requestResultTitle: 'Request Ergebnis',
        processCanceledText: 'Prozess wurde an Position [0] von [1] unterbrochen'
    });
    Ext.define('Shopware.form.field.de.TinyMCE', {
        override: 'Shopware.form.field.TinyMCE',

        noSourceErrorText: 'Die Quelldateien der TinyMCE-Editor sind nicht in das Projekt aufgenommen'
    });
    Ext.define('Shopware.form.de.PluginPanel', {
        override: 'Shopware.form.PluginPanel',

        noFormIdConfiguredErrorText: 'Es wurde keine FormId an die Komponente übergeben',
        formNotLoadedErrorText: 'Das Formular konnte nicht erfolgreich geladen werden',

        snippets: {
            resetButton: 'Zurücksetzen',
            saveButton: 'Speichern',
            description: 'Beschreibung',
            onSaveFormTitle: 'Formular speichern',
            saveFormSuccess: 'Formular „[name]“ wurde gespeichert.',
            saveFormError: 'Formular „[name]“ konnte nicht gespeichert werden.'
        }
    });
    Ext.define('Shopware.global.de.ErrorReporter', {
        override: 'Shopware.global.ErrorReporter',

        snippets: {
            general: {
                title: 'Shopware Fehler Reporter',
                error_title: 'Fehlerinformationen',
                browser_title: 'Browser-Informationen',
                cancel: 'Abbrechen'
            },
            xhr: {
                module: 'Modul',
                request_path: 'Pfad der Anforderung',
                http_error: 'HTTP-Fehlermeldung',
                http_status: 'HTTP-Statuscode',
                error_desc: 'Fehlerbeschreibung',
                module_files: 'Moduldateien',
                class_name: 'Klassenname',
                path: 'Pfad',
                type: 'Typ',
                unknown_type: 'Unbekannter Typ',
                reload_module: 'Reload-Modul'
            },
            eval: {
                reload_admin: 'Modul erneut laden',
                error_type: 'Art des Fehlers',
                error_msg: 'Fehlermeldung'
            },
            browser: {
                os: 'Betriebssystem',
                browser_engine: 'Browser-engine',
                window_size: 'Fenstergröße',
                java_enabled: 'Java aktiviert',
                cookies_enabled: 'Cookies aktiviert',
                lang: 'Sprache',
                plugins: 'Browser-plugins',
                plugin_name: 'Plugin-Namen',
                plugin_path: 'Plugin Pfad'
            },
            response: {
                name: 'Server-Antwort',
                errorOverview: 'Fehlerübersicht'
            }
        }
    });
    Ext.define('Shopware.window.plugin.de.Hud', {
        override: 'Shopware.window.plugin.Hud',

        hudTitle: 'Elemente-Bibliothek',
        hudStoreErrorMessage: function(className) {
            return className + ' benötigt die Eigenschaft "HudStore" zum speichern des Hub-Panels.';
        }
    });
    Ext.apply(Ext.form.VTypes, {
        passwordText: 'Das Feld Passwort ist nicht gültig'
    });
    Ext.apply(Ext.form.field.VTypes, {
        missingValidationErrorText: 'Die vType Validierung braucht eine ValidationErrorMsg-Eigenschaft'
    });
    Ext.define('Ext.grid.de.RowEditor', {
        override: 'Ext.grid.RowEditor',
        saveBtnText: 'Aktualisieren',
        cancelBtnText: 'Abbrechen',
        errorsText: 'Fehler',
        dirtyText: 'Du musst die Änderungen übernehmen oder abbrechen'
    });
    Ext.define('Ext.util.de.FileUpload', {
        override: 'Ext.util.FileUpload',
        snippets: {
            uploadReady: 'Dateien hochgeladen',
            filesFrom: 'von',
            messageText: '[0] Dateien hochgeladen',
            messageTitle: 'Medienverwaltung',
            legacyMessage: 'Dein Browser unterstützt nicht die benötigten Funktionen für einen Drag&Drop-Upload. ',
            maxUploadSizeTitle: 'Die Datei ist zu groß',
            maxUploadSizeText: 'Die selektierte Datei überschreitet die maximal erlaubte Uploadgröße. Bitte wähle eine andere Datei aus.',
            extensionNotAllowedTitle: 'Dateiendung wird nicht unterstützt',
            extensionNotAllowedText: 'Die Dateiendung \"[0]\" wird nicht unterstützt. Bitte wähle eine andere Datei aus.',
            blackListTitle: 'Blacklist',
            blackListMessage: 'Die Datei [0] ist nicht erlaubt!'
        }
    });
    Ext.define('Shopware.de.Notification', {
        override: 'Shopware.Notification',
        closeText: 'Schließen'
    });
    Ext.define('Enlight.app.de.Window', {
        override: 'Enlight.app.Window',
        closePopupTitle: 'Modul schließen',
        closePopupMessage: 'Sollen alle Unterfenster vom "__MODULE__"-Modul geschlossen werden?'
    });
    Ext.define('Shopware.detail.de.Controller', {
        override: 'Shopware.detail.Controller',
        closeText: 'Schließen',
        saveSuccessTitle: 'Erfolgreich',
        saveSuccessMessage: 'Eintrag wurde erfolgreich gespeichert',
        violationErrorTitle: 'Validierung Fehler',
        invalidFormTitle: 'Formularvalidierungs Fehler',
        invalidFormMessage: 'Das Formular beinhaltet invalide Daten, bitte prüfe deine Eingabe.'
    });
    Ext.define('Shopware.form.field.de.Media', {
        override: 'Shopware.form.field.Media',
        selectButtonText: 'Medium selektieren',
        resetButtonText: 'Medium zurücksetzen'
    });
    Ext.define('Shopware.grid.de.Association', {
        override: 'Shopware.grid.Association',
        searchComboLabel: 'Suche nach'
    });
    Ext.define('Shopware.grid.de.Controller', {
        override: 'Shopware.grid.Controller',
        deleteConfirmTitle: 'Einträge löschen',
        deleteConfirmText: 'Bist du sicher, dass du die markierten Einträge löschen möchtest?',
        deleteInfoText: '<b>Die Einträge werden gelöscht.</b> <br>Um den Prozess abzubrechen, kannst du den <b><i>`Cancel process`</i></b> Button verwenden. Abhänging von der Datenmenge kann dieser Prozess einige Minuten in Anspruch nehmen.',
        deleteProgressBarText: 'Eintrag [0] von [1]'
    });
    Ext.define('Shopware.listing.de.FilterPanel', {
        override: 'Shopware.listing.FilterPanel',
        infoTextSnippet: 'Aktiviere die verschiedenen Felder über die angezeigten Checkboxen. Aktivierte Felder werden mit einer "Und" Bedingung verknüpft.',
        filterButtonText: 'Filter anwenden',
        resetButtonText: 'Filter zurücksetzen'
    });
    Ext.define('Shopware.grid.de.Panel', {
        override: 'Shopware.grid.Panel',
        pageSizeLabel: 'Einträge pro Seite',
        addButtonText: 'Hinzufügen',
        deleteButtonText: 'Markierte Einträge löschen',
        searchFieldText: 'Suche...'
    });
    Ext.define('Shopware.grid.plugin.de.Translation', {
        override: 'Shopware.grid.plugin.Translation',
        snippets: {
            tooltip: 'Übersetzen'
        }
    });
    Ext.define('Shopware.apps.Base.view.element.de.ProductBoxLayoutSelect', {
        override: 'Shopware.apps.Base.view.element.ProductBoxLayoutSelect',

        fieldLabel: 'Produkt Layout',
        helpText: 'Mit Hilfe des Produkt Layouts kannst du entscheiden, wie deine Produkte auf der Kategorie-Seite dargestellt werden sollen. Wähle eines der drei unterschiedlichen Layouts um die Ansicht perfekt auf dein Produktsortiment abzustimmen. Du kannst für jede Kategorie ein eigenes Layout wählen oder über die Vererbungsfunktion automatisch die Einstellungen der Eltern-Kategorie übernehmen.'
    });
    Ext.define('Shopware.apps.Base.store.de.ProductBoxLayout', {
        override: 'Shopware.apps.Base.store.ProductBoxLayout',

        snippets: {
            displayExtendLayout: {
                label: 'Vererbt',
                description: 'Das Layout der Produkt-Box wird von der Eltern-Kategorie übernommen.'
            },
            displayBasicLayout: {
                label: 'Detaillierte Informationen',
                description: 'Das Layout der Produkt-Box zeigt detaillierte Informationen an.'
            },
            displayMinimalLayout: {
                label: 'Nur wichtige Informationen',
                description: 'Das Layout der Produkt-Box zeigt nur die wichtigsten Informationen an.'
            },
            displayImageLayout: {
                label: 'Großes Bild',
                description: 'Das Layout der Produkt-Box zeigt ein besonders großes Produkt-Bild.'
            },
            displayListLayout: {
                label: 'Produktliste',
                description: 'Das Layout der Produkt-Box zeigt detaillierte Informationen an, jedoch nur ein Produkt pro Zeile.'
            }
        }
    });

    Ext.define('Shopware.notification.de.SubscriptionWarning', {
        override: 'Shopware.notification.SubscriptionWarning',

        snippets: {
            licence_upgrade_warning: 'Es stehen noch Lizenzupgrades für [0] Plugin(s) aus. <a target="_blank" href="https://account.shopware.com">Account aufrufen</a><br /><br /><b>Nötige Upgrades:</b><br />[1]',
            subscription_warning: 'Es sind [0] Plugin Subscription(s) abgelaufen. <a target="_blank" href="https://account.shopware.com">Account aufrufen</a><br /><br /><b>Abgelaufene Plugins:</b><br />[1]',
            expired_soon_subscription_warning: 'Es laufen [0] Plugin Subscription(s) aus. <a target="_blank" href="https://account.shopware.com">Account aufrufen</a><br /><br /><b>Bald abgelaufene Plugins:</b><br />[1]',
            expired_soon_subscription_days_warning: ' Tage',
            invalid_licence: 'Lizenz von [0] Plugin(s) sind ungültig. <a target="_blank" href="https://account.shopware.com">Account aufrufen</a><br /><br /><b>Ungültige Lizenzen:</b><br />[1]',
            shop_license_upgrade: 'Das Lizenzupgrade für den Shop wurde noch nicht ausgeführt. <a target="_blank" href="https://account.shopware.com">Account aufrufen</a>',
            no_license: 'Möglicherweise bist du Opfer einer Produktfälschung geworden. <a target="_blank" href="https://account.shopware.com">Account aufrufen</a><br /><br /><b>Für die folgenden Plugins liegt keine gültige Lizenz vor:</b><br />[1]',
            expiring_license: 'Ablaufende Lizenz(en)',
            expired_license: 'Abgelaufene Lizenz(en)',
            expiring_license_warning: 'Es laufen [0]x Plugin Lizenz(en) aus.<br /><br /><b>Bald abgelaufene Lizenz(en):</b><br />[1]',
            expired_license_warning: 'Mindestens eine Lizenz der von Dir eingesetzten Plugins ist abgelaufen. <br> Überprüfe die Lizenzen in Deinem <a target="_blank" href="https://account.shopware.com">Shopware Account</a> unter <strong style="font-weight: bold">„Lizenzen“</strong>.',
            unknown_license: 'Nicht lizenzierte Plugins',
            subscription: 'Subscription',
            subscription_hide_message: 'Möchtest du diese Nachricht für eine Woche ausblenden?',
            confirm_open_pluginmanager: 'Du hast nicht lizenzierte Plugins installiert. Bitte erwirb eine gültige Lizenz oder deinstalliere und entferne die Plugins über den Plugin Manager.',
            openPluginOverview: 'Plugin-Übersicht öffnen',
            importantInformation: 'Wichtiger Hinweis',
            noShopSecretWarning: 'Damit Du Informationen zu Updates erhältst und Plugins installieren kannst, musst Du Dich in Deinen Shopware Account einloggen.<br> Solltest Du noch keinen Shopware Account besitzen, kannst Du Dich ganz einfach registrieren.',
            login: 'Jetzt anmelden'
        }
    });
});
