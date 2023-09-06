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

namespace Shopware\Components\Auth\Constraint;

use Shopware\Bundle\AccountBundle\Form\Account\PersonalFormType;
use Shopware\Components\Validator\NoUrlValidatorInterface;
use Shopware_Components_Snippet_Manager;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class NoUrlValidator extends ConstraintValidator
{
    public const SNIPPET_EMPTY_FIELD = [
        'namespace' => 'frontend/account/internalMessages',
        'name' => 'EmptyFieldError',
        'default' => 'This field cannot be empty',
    ];

    private Shopware_Components_Snippet_Manager $snippets;

    private NoUrlValidatorInterface $noUrlValidator;

    public function __construct(
        Shopware_Components_Snippet_Manager $snippets,
        NoUrlValidatorInterface $noUrlValidator
    ) {
        $this->snippets = $snippets;
        $this->noUrlValidator = $noUrlValidator;
    }

    /**
     * @param string $text
     */
    public function validate($text, Constraint $constraint): void
    {
        if (!$constraint instanceof NoUrl) {
            return;
        }

        if (empty($text)) {
            $this->addError($this->getSnippet(self::SNIPPET_EMPTY_FIELD));
        }

        if (!$this->noUrlValidator->isValid($text)) {
            $this->addError($this->getSnippet(PersonalFormType::SNIPPET_URL));
        }
    }

    private function addError(string $message): void
    {
        $this->context
            ->buildViolation($message)
            ->addViolation();
    }

    /**
     * @param array<string, string> $snippet with namespace, name and default value
     */
    private function getSnippet(array $snippet): string
    {
        return (string) $this->snippets->getNamespace($snippet['namespace'])->get($snippet['name'], $snippet['default'], true);
    }
}
