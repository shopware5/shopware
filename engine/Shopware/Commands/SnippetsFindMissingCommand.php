<?php
/**
 * Shopware 4
 * Copyright © shopware AG
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

namespace Shopware\Commands;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Finder\Finder;

/**
 * @category  Shopware
 * @package   Shopware\Command
 * @copyright Copyright (c) 2013, shopware AG (http://www.shopware.de)
 */
class SnippetsFindMissingCommand extends ShopwareCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('sw:snippets:find:missing')
            ->setDescription('Find missing snippets in the database and dumps them into .ini files')
            ->addArgument(
                'locale',
                InputArgument::REQUIRED,
                'Locale to be exported.'
            )
            ->addOption(
                'target',
                null,
                InputOption::VALUE_REQUIRED,
                'The folder where the exported files should be placed. Defaults to snippetsExport',
                'snippetsExport'
            )
        ;
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $locale = $this->container->get('models')->getRepository('Shopware\Models\Shop\Locale')->findOneByLocale($input->getArgument('locale'));
        if (!$locale) {
            $output->writeln('<error>Provided locale not found</error>');
            return;
        }

        $filteredQueryBuilder = $this->container->get('models')->getDBALQueryBuilder();
        $localeQueryBuilder = $this->container->get('models')->getDBALQueryBuilder();

        $statement = $localeQueryBuilder
            ->select('DISTINCT CONCAT(s.namespace, s.name) as hash')
            ->from('s_core_snippets', 's')
            ->where('s.localeID = :locale')
            ->setParameter('locale', $locale->getId())
            ->execute()
        ;

        $localeSnippets = $statement->fetchAll();

        $statement = $filteredQueryBuilder
            ->select('DISTINCT CONCAT(s.namespace, s.name) as hash, s.namespace', 's.name')
            ->from('s_core_snippets', 's')
            ->where(
                $filteredQueryBuilder->expr()->notIn(
                    'CONCAT(s.namespace, s.name)',
                    array_map(function($item) {return "'".$item['hash']."'";}, $localeSnippets)
                )
            )
            ->setParameter('snippets', array_map(function($item) {return $item['hash'];}, $localeSnippets))
            ->execute()
        ;
        $snippets = $statement->fetchAll();

        $output->writeln('<info></info>');
        $output->writeln('<info>'.count($snippets).' missing snippets detected</info>');

        $outputAdapter = new \Enlight_Config_Adapter_File(array(
            'configDir' => $input->getOption('target') . '/',
        ));

        $data = array();

        foreach ($snippets as $snippet) {
            if (!array_key_exists($snippet['namespace'], $data)) {
                $data[$snippet['namespace']] = new \Enlight_Components_Snippet_Namespace(array(
                    'name' => $snippet['namespace'],
                    'section' => array(
                        $locale->getLocale()
                    )
                ));
            }
            $content = $data[$snippet['namespace']];

            $content->set($snippet['name'], '');
        }
        $output->writeln('<info>'.count($data).' namespaces written</info>');

        foreach ($data as $namespace) {
            $outputAdapter->write($namespace, true);
        }
    }
}
