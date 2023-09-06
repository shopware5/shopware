<?php
/**
 * Shopware 5
 * Copyright (c) shopware AG
 *
 * According to our licensing model, this program can be used
 * under the terms of the GNU Affero General Public License, version 3.
 *
 * The texts of the GNU Affero General Public License with an additional
 * permission can be found at and in the LICENSE file you have received
 * along with this program.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 * See the GNU Affero General Public License for more details.
 *
 * "Shopware" is a registered trademark of shopware AG.
 * The licensing of the program under the AGPLv3 does not imply a
 * trademark license. Therefore, any rights, title and interest in
 * our trademarks remain entirely with the shopware AG.
 */

namespace Shopware\Components;

use RuntimeException;
use Shopware_Components_Snippet_Manager;

class StateTranslatorService implements StateTranslatorServiceInterface
{
    public const STATE_PAYMENT = 'payment';
    public const STATE_ORDER = 'order';

    /**
     * @var Shopware_Components_Snippet_Manager
     */
    private $snippetManager;

    /**
     * @var array
     */
    private $availableTypes;

    public function __construct(
        Shopware_Components_Snippet_Manager $snippetManager,
        $types = [
            self::STATE_PAYMENT => 'backend/static/payment_status',
            self::STATE_ORDER => 'backend/static/order_status',
        ]
    ) {
        $this->snippetManager = $snippetManager;
        $this->availableTypes = array_change_key_case($types, CASE_LOWER);
    }

    /**
     * {@inheritdoc}
     *
     * @throws RuntimeException
     */
    public function translateState($type, array $state)
    {
        $type = strtolower($type);

        if (empty($this->availableTypes[$type])) {
            throw new RuntimeException(sprintf('Invalid type \'%s\' given.', $type));
        }

        $namespace = $this->availableTypes[$type];
        $state['description'] = $this->snippetManager->getNamespace($namespace)->get($state['name'], $state['name'], true);

        return $state;
    }
}
