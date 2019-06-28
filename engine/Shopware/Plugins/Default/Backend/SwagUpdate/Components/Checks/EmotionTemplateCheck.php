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

namespace ShopwarePlugins\SwagUpdate\Components\Checks;

use Doctrine\DBAL\Connection;
use Enlight_Components_Snippet_Namespace as SnippetNamespace;
use ShopwarePlugins\SwagUpdate\Components\CheckInterface;
use ShopwarePlugins\SwagUpdate\Components\Validation;

class EmotionTemplateCheck implements CheckInterface
{
    const CHECK_TYPE = 'emotiontemplate';

    /**
     * @var SnippetNamespace
     */
    private $namespace;

    /**
     * @var Connection
     */
    private $connection;

    public function __construct(Connection $connection, SnippetNamespace $namespace)
    {
        $this->namespace = $namespace;
        $this->connection = $connection;
    }

    /**
     * {@inheritdoc}
     */
    public function canHandle($requirement)
    {
        return $requirement['type'] == self::CHECK_TYPE;
    }

    /**
     * @param array $requirement
     *
     * @return array
     */
    public function check($requirement)
    {
        $templates = $this->getActiveEmotionTemplates();

        if (empty($templates)) {
            return [
                'type' => self::CHECK_TYPE,
                'errorLevel' => Validation::REQUIREMENT_VALID,
                'message' => $this->namespace->get('controller/check_emotiontemplate_success'),
            ];
        }

        $names = array_column($templates, 'templateName');

        return [
            'type' => self::CHECK_TYPE,
            'errorLevel' => $requirement['level'],
            'message' => sprintf(
                $this->namespace->get('check_emotiontemplate_failure'),
                implode(',', $names)
            ),
        ];
    }

    /**
     * @return array[]
     */
    private function getActiveEmotionTemplates()
    {
        $query = $this->connection->createQueryBuilder();
        $query->select(['shop.name as shopName', 'template.name as templateName'])
            ->from('s_core_templates', 'template')
            ->innerJoin('template', 's_core_shops', 'shop', 'shop.template_id = template.id')
            ->andWhere('template.version < 3');

        return $query->execute()->fetchAll(\PDO::FETCH_ASSOC);
    }
}
