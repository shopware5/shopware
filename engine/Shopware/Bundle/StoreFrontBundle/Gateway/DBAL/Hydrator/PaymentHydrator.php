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

namespace Shopware\Bundle\StoreFrontBundle\Gateway\DBAL\Hydrator;

use Shopware\Bundle\StoreFrontBundle\Struct;

class PaymentHydrator extends Hydrator
{
    /**
     * @var AttributeHydrator
     */
    private $attributeHydrator;

    public function __construct(AttributeHydrator $attributeHydrator)
    {
        $this->attributeHydrator = $attributeHydrator;
    }

    /**
     * @return Struct\Payment
     */
    public function hydrate(array $data)
    {
        $payment = new Struct\Payment();

        $translation = $this->getTranslation($data, '__payment', [], $data['__payment_id']);
        $data = array_merge($data, $translation);

        $payment->setId($data['__payment_id']);
        $payment->setName($data['__payment_name']);
        $payment->setDescription($data['__payment_description']);
        $payment->setTemplate($data['__payment_template']);
        $payment->setClass($data['__payment_class']);
        $payment->setTable($data['__payment_table']);
        $payment->setHide($data['__payment_hide']);
        $payment->setAdditionalDescription(isset($data['__payment_additionalDescription']) ? $data['__payment_additionalDescription'] : $data['__payment_additionaldescription']);
        $payment->setDebitPercent($data['__payment_debit_percent']);
        $payment->setSurcharge($data['__payment_surcharge']);
        $payment->setSurchargeString($data['__payment_surchargestring']);
        $payment->setPosition($data['__payment_position']);
        $payment->setActive($data['__payment_active']);
        $payment->setEsdActive($data['__payment_esdactive']);
        $payment->setEmbediframe($data['__payment_embediframe']);
        $payment->setHideProspect($data['__payment_hideprospect']);
        $payment->setAction($data['__payment_action']);
        $payment->setPluginID($data['__payment_pluginID']);
        $payment->setSource($data['__payment_source']);
        $payment->setMobileInactive($data['__payment_mobile_inactive']);

        if (isset($data['__paymentAttribute_id'])) {
            $this->attributeHydrator->addAttribute($payment, $data, 'paymentAttribute');
        }

        return $payment;
    }
}
