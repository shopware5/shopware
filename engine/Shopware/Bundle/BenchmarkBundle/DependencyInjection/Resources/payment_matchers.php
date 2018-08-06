<?php
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
 */

return [
    'paypal' => [
        '/pay[\s-_]?pal/i',
    ],
    'invoice' => [
        '/invoice/i',
        '/rechnung/i',
    ],
    'debit' => [
        '/debit/i',
        '/last[\s-_]?schrift/i',
        '/sepa/i',
    ],
    'credit_card' => [
        '/credit/i',
        '/credit[\s-_]?card/i',
        '/visa/i',
        '/master[\s-_]?card/i',
        '/american[\s-_]?express/i',
    ],
    'sofort' => [
        '/sofort/i',
    ],
    'credit' => [
        '/post[\s-_]?finance/i',
    ],
    'stripe' => [
        '/stripe/i',
    ],
    'prepayment' => [
        '/pre[\s-_]?payment/i',
        '/vor[\s-_]?kasse/i',
    ],
    'amazon_pay' => [
        '/amazon/i',
    ],
    'google_wallet' => [
        '/google/i',
    ],
    'apple_pay' => [
        '/apple/i',
    ],
    'click_and_collect' => [
        '/abholen|abholung/i',
        '/self[\s-]?pick[\s-]?up/i',
        '/click[\s-]?(and|\&)[\s-]?collect/i',
    ],
    'klarna' => [
        '/klarna/i',
    ],
    'ideal' => [
        '/ideal/i',
    ],
    'pay_direkt' => [
        '/pay[\s-_]?direct/i',
        '/pay[\s-_]?direkt/i',
    ],
    'giro_pay' => [
        '/giro[\s-_]?pay/i',
    ],
    'bitcoin' => [
        '/bit[\s-_]?coin/i',
    ],
    'sage' => [
        '/sage/i',
    ],
    'cash' => [
        '/nach[\s-_]?nahme/i',
        '/cash[\s-_]?on[\s-_]?delivery/i',
        '/cash[[\s-_]?delivery/i',
        '/cash/i',
    ],
];
