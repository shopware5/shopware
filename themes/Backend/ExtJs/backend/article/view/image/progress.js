/**
 * Shopware 5
 * Copyright (c) shopware AG
 *
 * According to our dual licensing model, this program can be used either
 * under the terms of the GNU Affero General Public License, version 3,
 * or under a proprietary license.
 *
 * The texts of the GNU Affero General Public License with an additional
 * permission and of our proprietary license can be found at and
 * in the LICENSE file you have received along with this program.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU Affero General Public License for more details.
 *
 * "Shopware" is a registered trademark of shopware AG.
 * The licensing of the program under the AGPLv3 does not imply a
 * trademark license. Therefore any rights, title and interest in
 * our trademarks remain entirely with us.
 *
 * @category   Shopware
 * @package    Article
 * @subpackage Detail
 * @version    $Id$
 * @author shopware AG
 */

/**
 * Shopware UI - Article Image assign variants progress window.
 */
//{namespace name=backend/article/view/main}
//{block name="backend/article/view/image/progress"}
Ext.define('Shopware.apps.Article.view.image.Progress', {
    extend: 'Shopware.window.Progress',

    /**
     * The name to identify our component
     *
     * @type { string }
     */
    name: 'image-variants-progress-window',

    /**
     * The window title
     *
     * @type { string }
     */
    title: '{s name="image/variant_info/progress/title"}Assigning variant configurations{/s}',

    /**
     * Internal flag which defines if the batch-processing may start.
     *
     * @type { boolean }
     */
    allowStart: false,

    /**
     * Will contain the mapping window which opened this progress component.
     * It needs to be destroyed once the progress is done.
     *
     * @type { Shopware.apps.Article.view.image.Mapping }
     */
    mappingWindow: undefined,

    /**
     * Instance of the start progress button which allows the user
     * to start the batch process.
     * The start button is created in the { @link #createStartButton } function.
     *
     * @type { Ext.button.Button }
     */
    startProgressButton: undefined,

    /**
     * @Override
     *
     * @param { Ext.data.Batch } batch
     * @param { Ext.data.Operation } operation
     * @returns { Shopware.model.DataOperation }
     */
    createResponseRecord: function (batch, operation) {
        var rawData = batch.proxy.getReader().rawData,
            errorMessage = '';

        if (!rawData.success && rawData.noId) {
            errorMessage = 'No image id was applied';
        }

        return Ext.create('Shopware.model.DataOperation', {
            success: rawData.success,
            error: errorMessage,
            request: { url: operation.batch.proxy.api.update },
            operation: operation
        });
    },

    /**
     * @Override
     *
     * @param { Object } currentTask
     * @param { Object[] } remainingTasks
     */
    sequentialProcess: function (currentTask, remainingTasks) {
        if (!this.allowStart) {
            return;
        }

        this.callParent(arguments);
    },

    /**
     * @Override
     *
     * @returns { Ext.button.Button[] }
     */
    createToolbarItems: function () {
        var items = this.callParent(arguments);

        this.closeButton.setVisible(false);

        this.registerCustomButtonHandler(this.closeButton);
        this.registerCustomButtonHandler(this.cancelButton);

        items.push(this.createStartButton());

        return items;
    },

    /**
     * @Override
     */
    onCancelProgress: function () {
        this.cancelProcess = true;
        this.startProgressButton.destroy();

        if (!this.allowStart) {
            this.allowStart = true;
            this.sequentialProcess(undefined, this.getConfig('tasks'));
        }
    },

    /**
     * @returns { Ext.button.Button }
     */
    createStartButton: function () {
        var me = this;

        me.startProgressButton = Ext.create('Ext.button.Button', {
            cls: 'primary',
            text: '{s name="image/variant_info/progress/save_button/text"}Save configurations{/s}',
            handler: function () {
                me.allowStart = true;
                me.sequentialProcess(undefined, me.getConfig('tasks'));

                this.destroy();
            }
        });

        return me.startProgressButton;
    },

    /**
     * @param { Ext.button.Button } btn
     */
    registerCustomButtonHandler: function (btn) {
        btn.on('disable', function (btn) {
            btn.setVisible(false);
        });

        btn.on('enable', function (btn) {
            btn.setVisible(true);
        });
    }
});
//{/block}
