Ext.define('Shopware.apps.Jira', {
    /**
     * The name of the module. Used for internal purpose
     * @string
     */
    name: 'Shopware.apps.Jira',

    /**
     * Extends from our special controller, which handles the
     * sub-application behavior and the event bus
     * @string
     */
    extend: 'Enlight.app.SubApplication',

    /**
     * Sets the loading path for the sub-application.
     *
     * Note that you'll need a "loadAction" in your
     * controller (server-side)
     * @string
     */
    loadPath: '/Widgets/Jira/load?viewport&file=',

    /**
     * Enables our bulk loading mechanism.
     * @booelan
     */
    bulkLoad: false,

    controllers: [ 'Main' ]
});