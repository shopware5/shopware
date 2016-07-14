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
 * @package    NewsletterManager
 * @subpackage Store
 * @version    $Id$
 * @author shopware AG
 */

//{namespace name="backend/newsletter_manager/main"}

/**
 * Shopware Store - mail dispatch store
 * Stores available dispatches for mails. Its mapped to mailings.plaintext later:
 * plaintext=false will be "HTML + Plaintext", plaintext=true will be "Plaintext"
 */
//{block name="backend/newsletter_manager/store/mail_dispatch"}
Ext.define('Shopware.apps.NewsletterManager.store.MailDispatch', {
    extend: 'Ext.data.Store',
    // Do not load data, when not explicitly requested
    model : 'Shopware.apps.NewsletterManager.model.MailDispatch',
    autoLoad: true,

    data : [
        { id:'1', name:'{s name=htmlPlaintext}HTML + Plaintext{/s}' },
        { id:'2', name:'{s name=plaintext}Plaintext{/s}' }
    ]


});
//{/block}
