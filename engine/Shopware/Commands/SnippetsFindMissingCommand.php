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

namespace Shopware\Commands;

use Shopware\Components\Model\ModelRepository;
use Shopware\Models\Shop\Locale;
use Stecman\Component\Symfony\Console\BashCompletion\Completion\CompletionAwareInterface;
use Stecman\Component\Symfony\Console\BashCompletion\CompletionContext;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class SnippetsFindMissingCommand extends ShopwareCommand implements CompletionAwareInterface
{
    /**
     * {@inheritdoc}
     */
    public function completeOptionValues($optionName, CompletionContext $context)
    {
        if ($optionName === 'target') {
            return $this->completeDirectoriesInDirectory();
        } elseif ($optionName === 'fallback') {
            /** @var ModelRepository $localeRepository */
            $localeRepository = $this->getContainer()->get('models')->getRepository(Locale::class);
            $queryBuilder = $localeRepository->createQueryBuilder('locale');

            if (strlen($context->getCurrentWord())) {
                $queryBuilder->andWhere($queryBuilder->expr()->like('locale.locale', ':search'))
                    ->setParameter('search', addcslashes($context->getCurrentWord(), '_%') . '%');
            }

            $result = $queryBuilder->select(['locale.locale'])
                ->getQuery()
                ->getArrayResult();

            return array_diff(array_column($result, 'locale'), [$context->getWordAtIndex(2)]);
        }

        return [];
    }

    /**
     * {@inheritdoc}
     */
    public function completeArgumentValues($argumentName, CompletionContext $context)
    {
        if ($argumentName === 'locale') {
            return $this->completeInstalledLocaleKeys($context->getCurrentWord());
        }

        return [];
    }

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
            ->addOption(
                'fallback',
                null,
                InputOption::VALUE_REQUIRED,
                'If a locale is provided, it will be used to fill in the values for the snippets. Ideal to export missing snippets directly for translation. Defaults to null, which exports empty snippets',
                null
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

            return null;
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
                    array_map(function ($item) {
                        return "'" . $item['hash'] . "'";
                    }, $localeSnippets)
                )
            )
            ->setParameter('snippets', array_map(function ($item) {
                return $item['hash'];
            }, $localeSnippets))
        ;

        if ($input->getOption('fallback')) {
            $targetLocale = $this->container->get('models')->getRepository('Shopware\Models\Shop\Locale')->findOneByLocale($input->getOption('fallback'));
            if (!$targetLocale) {
                $output->writeln('<error>Provided fallback locale not found</error>');

                return null;
            }

            $statement
                ->addSelect('fallback_values.value AS value')
                ->leftJoin(
                    's',
                    's_core_snippets',
                    'fallback_values',
                    '(s.name = fallback_values.name AND s.namespace = fallback_values.namespace AND fallback_values.localeID = :targetLocale)'
                )
                ->setParameter('targetLocale', $targetLocale->getId())
            ;
        }

        $snippets = $statement->execute()->fetchAll();

        $output->writeln('<info></info>');
        $output->writeln('<info>' . count($snippets) . ' missing snippets detected</info>');

        $outputAdapter = new \Enlight_Config_Adapter_File([
            'configDir' => $input->getOption('target') . '/',
        ]);

        $data = [];

        foreach ($snippets as $snippet) {
            if (!array_key_exists($snippet['namespace'], $data)) {
                $data[$snippet['namespace']] = new \Enlight_Components_Snippet_Namespace([
                    'name' => $snippet['namespace'],
                    'section' => [
                        $locale->getLocale(),
                    ],
                ]);
            }
            $content = $data[$snippet['namespace']];

            $content->set($snippet['name'], isset($snippet['value']) ? $snippet['value'] : '');
        }
        $output->writeln('<info>' . count($data) . ' namespaces written</info>');

        foreach ($data as $namespace) {
            $outputAdapter->write($namespace, true);
        }
    }
}
