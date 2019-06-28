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

use Shopware\Models\Shop\Locale;
use Stecman\Component\Symfony\Console\BashCompletion\Completion\CompletionAwareInterface;
use Stecman\Component\Symfony\Console\BashCompletion\CompletionContext;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class SettingsLabelsFindMissingCommand extends ShopwareCommand implements CompletionAwareInterface
{
    /**
     * {@inheritdoc}
     */
    public function completeOptionValues($optionName, CompletionContext $context)
    {
        if ($optionName === 'target') {
            return $this->completeDirectoriesInDirectory($this->container->get('application')->DocPath());
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
            ->setName('sw:settings:label:find:missing')
            ->setDescription('Dump missing settings labels from the database into php arrays files')
            ->addArgument(
                'locale',
                InputArgument::REQUIRED,
                'Locale to be exported.'
            )
            ->addOption(
                'target',
                null,
                InputOption::VALUE_REQUIRED,
                'The folder where the exported files should be placed. Defaults to Shopware\'s root folder',
                null
            );
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $dir = $this->container->get('application')->DocPath($input->getOption('target'));
        if (!file_exists($dir) || !is_writable($dir)) {
            $old = umask(0);
            mkdir($dir, 0777, true);
            chmod($dir, 0777);
            umask($old);
        }
        if (!is_writable($dir)) {
            $output->writeln('<error>Output dir ' . $input->getOption('file') . ' is not writable, aborting</error>');

            return 1;
        }

        /** @var Locale|null $locale */
        $locale = $this->container->get('models')
            ->getRepository(\Shopware\Models\Shop\Locale::class)
            ->findOneByLocale($input->getArgument('locale'));
        if (!$locale) {
            $output->writeln('<error>Provided locale not found</error>');

            return null;
        }

        $this->exportFormLabels($output, $locale, $dir);

        $this->exportElementLabels($output, $locale, $dir);
    }

    /**
     * Exports form labels from the database into a php file containing an array
     *
     * @param \Shopware\Models\Shop\Locale $locale
     * @param string                       $dir
     *
     * @throws \Exception
     */
    protected function exportFormLabels(OutputInterface $output, $locale, $dir)
    {
        $formQueryBuilder = $this->container->get('models')->getDBALQueryBuilder();
        $statement = $formQueryBuilder
            ->select('form.name AS name', 'form.label AS label', 'form.description AS description')
            ->from('s_core_config_forms', 'form')
            ->leftJoin(
                'form',
                's_core_config_form_translations',
                'trans',
                ' form.id = trans.form_id AND trans.locale_id = :localeId'
            )
            ->where('trans.form_id IS NULL')
            ->andWhere('form.name IS NOT NULL')
            ->setParameter('localeId', $locale->getId())
            ->execute();

        $missingFormLabels = $statement->fetchAll();

        $output->writeln('<info></info>');
        $output->writeln('<info>' . count($missingFormLabels) . ' missing form labels detected</info>');
        if ($missingFormLabels) {
            $formLabelFilePath = $dir . 'formTranslations' . str_replace('_', '', $locale->getLocale()) . '.php';

            $output->writeln('<info>Writing to ' . $formLabelFilePath . '</info>');
            file_put_contents($formLabelFilePath, '<?php return ' . var_export($missingFormLabels, true) . ';');
        }
    }

    /**
     * Exports element labels from the database into a php file containing an array
     *
     * @param Locale $locale
     * @param string $dir
     *
     * @throws \Exception
     */
    protected function exportElementLabels(OutputInterface $output, $locale, $dir)
    {
        $elementsQueryBuilder = $this->container->get('models')->getDBALQueryBuilder();
        $statement = $elementsQueryBuilder
            ->select('form.name AS formName', 'elem.name AS elementName', 'elem.label AS label')
            ->from('s_core_config_forms', 'form')
            ->leftJoin('form', 's_core_config_elements', 'elem', 'form.id = elem.form_id')
            ->leftJoin(
                'elem',
                's_core_config_element_translations',
                'trans',
                ' elem.id = trans.element_id AND trans.locale_id = :localeId'
            )
            ->where('trans.element_id IS NULL')
            ->andWhere('elem.name IS NOT NULL')
            ->andWhere('form.name IS NOT NULL')
            ->setParameter('localeId', $locale->getId())
            ->execute();

        $missingElementLabels = $statement->fetchAll();

        $output->writeln('<info></info>');
        $output->writeln('<info>' . count($missingElementLabels) . ' missing element labels detected</info>');
        if ($missingElementLabels) {
            $elementLabelFilePath = $dir . 'elementTranslations' . str_replace('_', '', $locale->getLocale()) . '.php';

            $output->writeln('<info>Writing to ' . $elementLabelFilePath . '</info>');
            file_put_contents($elementLabelFilePath, '<?php return ' . var_export($missingElementLabels, true) . ';');
        }
    }
}
