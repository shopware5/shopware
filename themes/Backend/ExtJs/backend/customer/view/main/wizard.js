
Ext.define('Shopware.apps.Customer.view.main.Wizard', {
    extend: 'Ext.window.Window',
    layout: 'fit',
    autoShow: false,
    modal: true,
    cls: 'plugin-manager-loading-mask',
    bodyPadding: 20,
    header: false,
    width: 1400,
    height: 540,

    initComponent: function() {
        var me = this;

        me.dockedItems = me.createDockedItems();

        me.items = me.createItems();

        me.callParent(arguments);
    },

    nextPage: function() {
        var me = this;
        var layout = me.cardContainer.getLayout();

        if (layout.getNext()) {
            layout.next();
        } else {
            me.finish();
        }
    },

    previousPage: function() {
        var me = this;
        var layout = me.cardContainer.getLayout();

        if (layout.getPrev()) {
            layout.prev();
        }
    },

    finish: function() {
        var me = this;
        me.fireEvent('finish');
        me.destroy();
    },

    createItems: function() {
        var me = this;

        me.cardContainer = Ext.create('Ext.container.Container', {
            region: 'center',
            layout: 'card',
            items: [
                me.createFirstPage(),
                me.createSecondPage(),
                me.createFinishPage()
            ]
        });
        return [me.cardContainer];
    },

    createFirstPage: function() {
        return Ext.create('Ext.container.Container', {
            html: '' +
            '<h1 style="padding-bottom: 15px; font-size: 22px;">' +
                'Kundenübersicht' +
            '</h1>' +
            '<div style="float:left; width: 25%; margin-right: 10px;">' +
                '<p>Die Kundenübersicht bietet einen Schnellzugriff auf alle registrierten Kunden.</p>' +
                '<p>Dort befinden sich alle relevanten Kundendaten sowie ein Link zur Kontaktaufnahme mit dem Kunden per eMail.</p>' +
                '<p>Neben der Freitext-Suche befindet sich auf der linken Seite eine einfache Filtermöglichkeit, mit der Du schnell nach bestimmten Kunden suchen kannst.</p>' +
            '</div>' +
            '<div style="float: left;">' +
                '<img src="{link file="backend/_resources/images/customer_stream/quick_view.png"}" />' +
            '</div>'
        });
    },
    createSecondPage: function() {
        return Ext.create('Ext.container.Container', {
            html: '' +
            '<h1 style="padding-bottom: 15px; font-size: 22px;">' +
                'Customer Stream Übersicht' +
            '</h1>' +
            '<div style="float:left; width: 25%; margin-right: 10px;">' +
                '<p>Mit den Customer Streams kannst Du Kunden nach bestimmten Kriterien gruppieren.</p>' +
                '<p>Für diese Gruppierungen kannst Du anschließend Auswertungen erstellen, Marketing-Kampagnen umsetzen, individuelle Shop-Inhalte erzeugen und vieles mehr.</p>' +
                '<p>Damit das Arbeiten mit den Customer Streams für Dich angenehm und schnell ist und Du schnellstmöglich Ergebnisse zur Verwendung und Filterung in den Streams erhältst, werden die Kundendaten täglich neu analysiert.</p>' +
            '</div>' +
            '<div style="float: left;">' +
                '<img src="{link file="backend/_resources/images/customer_stream/stream_view.png"}" />' +
            '</div>'
        });
    },

    createFinishPage: function() {
        return Ext.create('Ext.container.Container', {
            html: '' +
            '<h1 style="padding-bottom: 15px; font-size: 22px;">' +
                'Verwendbarkeit' +
            '</h1>' +
            '<div style="float:left; width: 25%; margin-right: 10px;">' +
                '<p>Die Customer Streams bieten eine starke Wiederverwendbarkeit in Deinem Shop.</p>' +
                '<p>So kannst Du zum Beispiel eigene Einkaufswelten pro Customer Stream definieren, um auf die individuellen Wünsche Deiner Kunden einzugehen.</p>' +
                '<p>Des Weiteren ist es Dir so möglich, Gutscheine für bestimmte Kunden einzuschränken oder Newsletter an die verschiedenen Streams zu versenden.</p>' +
            '</div>' +
            '<div style="float: left;">' +
            '<img src="{link file="backend/_resources/images/customer_stream/ekw_usage.png"}" />' +
            '</div>'
        });
    },

    createDockedItems: function() {
        var me = this;

        me.nextButton = Ext.create('Ext.button.Button', {
            text: 'Next',
            cls: 'primary',
            handler: Ext.bind(me.nextPage, me)
        });

        me.previousButton = Ext.create('Ext.button.Button', {
            text: 'Back',
            cls: 'secondary',
            handler: Ext.bind(me.previousPage, me)
        });

        me.finishButton = Ext.create('Ext.button.Button', {
            text: 'Understood!',
            cls: 'primary',
            hidden: true,
            handler: Ext.bind(me.finish, me)
        });

        return [{
            xtype: 'toolbar',
            dock: 'bottom',
            ui: 'shopware-ui',
            items: ['->', me.previousButton, me.nextButton, me.finishButton]
        }];
    }
});