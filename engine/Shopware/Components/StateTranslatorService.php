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

namespace Shopware\Components;

class StateTranslatorService implements StateTranslatorServiceInterface
{
    const STATE_PAYMENT = 'payment';
    const STATE_ORDER = 'order';

    /**
     * @var \Shopware_Components_Snippet_Manager
     */
    private $snippetManager;

    /**
     * @var array
     */
    private $availableTypes;

    public function __construct(
        \Shopware_Components_Snippet_Manager $snippetManager,
        $types = [
            self::STATE_PAYMENT => 'backend/static/payment_status',
            self::STATE_ORDER => 'backend/static/order_status',
        ]
    ) {
        $this->snippetManager = $snippetManager;
        $this->availableTypes = $types;
    }

    /**
     * {@inheritdoc}
     *
     * @throws \RuntimeException
     */
    public function translateState($type, array $state)
    {
        if (!$this->availableTypes[$type]) {
            throw new \RuntimeException(sprintf('Invalid type \'%s\' given.', $type));
        }

        $namespace = $this->availableTypes[$type];
        $state['description'] = $this->snippetManager->getNamespace($namespace)->get($state['name'], $state['name'], true);

        return $state;
    }
}
