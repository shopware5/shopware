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

namespace Shopware\Components\Plugin;

use Assert\Assertion;
use Shopware\Components\Model\ModelManager;
use Shopware\Models\Payment\Payment;
use Shopware\Models\Plugin\Plugin;

class PaymentInstaller
{
    /**
     * @var ModelManager
     */
    private $em;

    /**
     * @param ModelManager $em
     */
    public function __construct(ModelManager $em)
    {
        $this->em = $em;
    }

    /**
     * Inserts or updates the payment row
     * @param string $pluginName
     * @param array $options
     * @return Payment
     */
    public function createOrUpdate($pluginName, array $options)
    {
        Assertion::notEmptyKey($options, 'name', 'Payment name must not be empty');

        $repo = $this->em->getRepository(Payment::class);
        $payment = $repo->findOneBy(['name' => $options['name']]);

        $repo = $this->em->getRepository(Plugin::class);
        $plugin = $repo->findOneBy(['name' => $pluginName]);

        if (!$payment) {
            $payment = new Payment();
            $payment->setName($options['name']);
            $this->em->persist($payment);
        }
        $payment->fromArray($options);

        $payment->setPlugin($plugin);
        $this->em->flush($payment);

        return $payment;
    }
}
