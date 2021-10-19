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

use RuntimeException;
use Shopware\Components\Model\ModelManager;
use Shopware\Components\Model\ModelRepository;
use Shopware\Components\Theme\Generator;
use Shopware\Components\Theme\Installer;
use Shopware\Models\Shop\Template;
use Stecman\Component\Symfony\Console\BashCompletion\Completion\CompletionAwareInterface;
use Stecman\Component\Symfony\Console\BashCompletion\CompletionContext;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;

class ThemeCreateCommand extends ShopwareCommand implements CompletionAwareInterface
{
    /**
     * @var ModelRepository<Template>|null
     */
    private ?ModelRepository $repository = null;

    /**
     * {@inheritdoc}
     */
    public function completeOptionValues($optionName, CompletionContext $context)
    {
        return [];
    }

    /**
     * {@inheritdoc}
     */
    public function completeArgumentValues($argumentName, CompletionContext $context)
    {
        if ($argumentName === 'parent') {
            $queryBuilder = $this->getRepository()->createQueryBuilder('tpl');

            if ($context->getCurrentWord() !== '') {
                $queryBuilder->andWhere($queryBuilder->expr()->like('tpl.template', ':search'))
                    ->setParameter('search', addcslashes($context->getCurrentWord(), '_%') . '%');
            }

            $result = $queryBuilder->select(['tpl.template'])
                ->getQuery()
                ->getArrayResult();

            return array_column($result, 'template');
        }

        return [];
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('sw:theme:create')
            ->setDescription('Creates a theme.')
            ->addArgument(
                'parent',
                InputArgument::REQUIRED,
                'Name of the theme which should be extended.'
            )
            ->addArgument(
                'template',
                InputArgument::REQUIRED,
                'Name of the theme directory.'
            )
            ->addArgument(
                'name',
                InputArgument::REQUIRED,
                'Name of the theme readable in theme manager.'
            )
            ->addOption(
                'description',
                null,
                InputOption::VALUE_REQUIRED,
                'Description of the theme to be created.'
            )
            ->addOption(
                'author',
                null,
                InputOption::VALUE_REQUIRED,
                'Author of the theme to be created.'
            )
            ->addOption(
                'license',
                null,
                InputOption::VALUE_REQUIRED,
                'Licence of the theme to be created.'
            )
            ->setHelp(
                <<<'EOF'
                The <info>%command.name%</info> creates a theme.
EOF
            );
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $arguments = $input->getArguments();

        $themeInstaller = $this->container->get(Installer::class);
        $themeInstaller->synchronize();

        if ($this->getRepository()->findOneBy(['template' => $arguments['template']])) {
            $output->writeln('A theme with that name already exists');

            return 1;
        }

        if (!\is_string($arguments['parent'])) {
            throw new RuntimeException('Invalid argument "parent" given.');
        }

        $parent = $this->getRepository()->findOneBy(['template' => $arguments['parent']]);
        if (!$parent instanceof Template) {
            $output->writeln(
                sprintf(
                    'Shop template by template name "%s" not found',
                    $arguments['parent']
                )
            );

            return 1;
        }

        $arguments = array_merge($arguments, $this->dialog($input, $output));

        $themeGenerator = $this->container->get(Generator::class);
        $themeGenerator->generateTheme($arguments, $parent);

        $output->writeln(sprintf('Theme "%s" has been created successfully.', $arguments['name']));

        return 0;
    }

    /**
     * @return ModelRepository<Template>
     */
    private function getRepository(): ModelRepository
    {
        if ($this->repository === null) {
            $this->repository = $this->container->get(ModelManager::class)->getRepository(Template::class);
        }

        return $this->repository;
    }

    /**
     * Helper function to ask for optional data
     *
     * @return array<string, mixed>
     */
    private function dialog(InputInterface $input, OutputInterface $output): array
    {
        $options = [];

        $options['description'] = $this->askForOptionalData($input, $output, 'description');
        $options['author'] = $this->askForOptionalData($input, $output, 'author');
        $options['license'] = $this->askForOptionalData($input, $output, 'license');

        return $options;
    }

    /**
     * Helper function to ask the user a question
     */
    private function askForOptionalData(InputInterface $input, OutputInterface $output, string $optionKey)
    {
        $optionValue = $input->getOption($optionKey);

        if (empty($optionValue)) {
            $questionHelper = $this->getHelper('question');
            $question = new Question(sprintf('Please enter the %s: ', $optionKey));
            $optionValue = $questionHelper->ask($input, $output, $question);
        }

        return $optionValue;
    }
}
