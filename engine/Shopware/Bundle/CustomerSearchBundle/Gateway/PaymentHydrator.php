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

namespace Shopware\Bundle\CustomerSearchBundle\Gateway;

use Shopware\Bundle\StoreFrontBundle\Gateway\DBAL\Hydrator\AttributeHydrator;
use Shopware\Bundle\StoreFrontBundle\Gateway\DBAL\Hydrator\Hydrator;

class PaymentHydrator extends Hydrator
{
    /**
     * @var AttributeHydrator
     */
    private $attributeHydrator;

    /**
     * @param AttributeHydrator $attributeHydrator
     */
    public function __construct(AttributeHydrator $attributeHydrator)
    {
        $this->attributeHydrator = $attributeHydrator;
    }

    public function hydrate(array $row)
    {
        $payment = new PaymentStruct();
        $payment->setId((int) $row['__payment_id']);
        $payment->setName($row['__payment_name']);
        $payment->setDescription($row['__payment_description']);
        $payment->setTemplate($row['__payment_template']);
        $payment->setClass($row['__payment_class']);
        $payment->setTable($row['__payment_table']);
        $payment->setHide((bool) $row['__payment_hide']);
        $payment->setAdditionalDescription($row['__payment_additionaldescription']);
        $payment->setDebitPercent((float) $row['__payment_debit_percent']);
        $payment->setSurcharge((float) $row['__payment_surcharge']);
        $payment->setSurchargeString($row['__payment_surchargestring']);
        $payment->setPosition((int) $row['__payment_position']);
        $payment->setActive((bool) $row['__payment_active']);
        $payment->setAllowEsd((bool) $row['__payment_esdactive']);
        $payment->setEmbediframe($row['__payment_embediframe']);
        $payment->setHideProspect((bool) $row['__payment_hideprospect']);
        $payment->setAction($row['__payment_action']);
        $payment->setPluginId((int) $row['__payment_pluginID']);
        $payment->setSource($row['__payment_source']);
        $payment->setAllowOnMobile($row['__payment_mobile_inactive'] == 0);

        if ($row['__paymentAttribute_id']) {
            $this->attributeHydrator->addAttribute($payment, $row, 'paymentAttribute');
        }

        return $payment;
    }
}
