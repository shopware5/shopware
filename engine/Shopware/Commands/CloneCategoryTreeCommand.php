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

use Shopware\Models\Category\Category;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class CloneCategoryTreeCommand extends ShopwareCommand
{
    /**
     * @var OutputInterface
     */
    protected $output;

    /**
     * @var InputInterface
     */
    protected $input;

    /**
     * @var ProgressBar
     */
    protected $progressBar;

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('sw:clone:category:tree')
            ->setDescription('Duplicates the category tree.')
            ->addArgument(
                'category',
                InputArgument::REQUIRED,
                'Name or id of the category to duplicate.'
            )
            ->addArgument(
                'target',
                InputArgument::OPTIONAL,
                'Name or id of the target path category.'
            )
            ->addOption(
                'noArticleAssociations',
                null,
                InputOption::VALUE_NONE,
                'If set, the article associations are not copied'
            );
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->input = $input;
        $this->output = $output;

        /** @var Category $originalCategory */
        $originalCategory = $this->getCategoryFromInput($input->getArgument('category'));

        if (empty($originalCategory)) {
            return;
        } elseif ($originalCategory->getId() == 1) {
            $output->writeln('<error>Cannot duplicate root category</error>');

            return;
        }

        $parent = $input->getArgument('target');
        if (empty($parent)) {
            $parent = $originalCategory->getParent();
        } else {
            $parent = $this->getCategoryFromInput($parent);
            if (empty($parent)) {
                return;
            }
        }

        $copyArticleAssociations = !$input->getOption('noArticleAssociations');

        $count = $this->container->get('models')
            ->getRepository('Shopware\Models\Category\Category')
            ->getChildrenCountList($originalCategory->getId());

        $this->progressBar = new ProgressBar($output, $count);
        $this->progressBar->start();

        try {
            $this->duplicateCategory($originalCategory->getId(), $parent->getId(), $copyArticleAssociations);
        } catch (\RuntimeException $e) {
            $output->writeln('<error>' . $e->getMessage() . '</error>');

            return;
        }

        $this->progressBar->finish();

        $output->writeln('<info>Category tree duplicated successfully</info>');
    }

    /**
     * Based in the use input (id or name of the category), fetches the actual category
     * May print a helping table in case of multiple matches
     *
     * @param int|string $categoryInput
     *
     * @throws \Exception
     *
     * @return Category|null
     */
    private function getCategoryFromInput($categoryInput)
    {
        if (is_numeric($categoryInput)) {
            $categoryInput = intval($categoryInput);
            $mode = 'find';
        } else {
            $mode = 'findByName';
        }

        $category = $this->container->get('models')
            ->getRepository('Shopware\Models\Category\Category')
            ->$mode(
                $categoryInput
            );

        if (is_array($category)) {
            if (count($category) > 1) {
                $this->printCategoriesTable($category);

                return null;
            } elseif (count($category) == 1) {
                $category = array_shift($category);
            }
        }
        if (empty($category)) {
            $this->output->writeln(
                '<error>The given id or name "' . $categoryInput . '" does not match an existing category</error>'
            );

            return null;
        }

        return $category;
    }

    /**
     * Recursively duplicates categories
     *
     * @param int  $categoryId
     * @param int  $newParentId
     * @param bool $copyArticleAssociations
     * @param int  $newRootCategoryId
     *
     * @throws \RuntimeException
     */
    private function duplicateCategory(
        $categoryId,
        $newParentId,
        $copyArticleAssociations,
        $newRootCategoryId = null
    ) {
        $categoryDuplicator = $this->container->get('CategoryDuplicator');

        $newCategoryId = $categoryDuplicator->duplicateCategory($categoryId, $newParentId, $copyArticleAssociations);
        $this->progressBar->advance();

        $childrenStmt = $this->container->get('db')->prepare('SELECT id FROM s_categories WHERE parent = :parent');
        $childrenStmt->execute([':parent' => $categoryId]);
        $children = $childrenStmt->fetchAll(\PDO::FETCH_COLUMN);

        $newRootCategoryId = $newRootCategoryId ?: $newCategoryId;

        foreach ($children as $child) {
            if ($child != $newRootCategoryId) {
                $this->duplicateCategory($child, $newCategoryId, $copyArticleAssociations, $newRootCategoryId);
            }
        }
    }

    /**
     * Prints a list of category details
     * Used in case the user specifies a category name with multiple matches
     *
     * @param Category[] $categories
     */
    private function printCategoriesTable($categories)
    {
        $this->output->writeln(
            '<error>The given criteria matches multiple categories. Please try again using the category id</error>'
        );

        $tableData = [];

        foreach ($categories as $category) {
            $tableData[] = [
                $category->getId(),
                $category->getName(),
                $this->getCategoryPath($category),
            ];
        }

        $table = new Table($this->output);
        $table
            ->setHeaders(['Id', 'Name', 'Path'])
            ->setRows($tableData);
        $table->render();
    }

    /**
     * Creates a human readable category path
     *
     * @param Category $category
     *
     * @return string
     */
    private function getCategoryPath(Category $category)
    {
        $parent = $category->getParent();

        if (!$parent) {
            return $category->getName();
        }

        return $this->getCategoryPath($parent) . ' > ' . $category->getName();
    }
}
