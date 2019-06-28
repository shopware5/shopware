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

namespace Shopware\Components\Snippet;

use Shopware;
use Shopware\Components\Model\ModelManager;
use Shopware\Components\Snippet\Writer\DatabaseWriter;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Finder\Finder;

class DatabaseHandler
{
    /**
     * @var string
     */
    protected $kernelRoot;

    /**
     * @var ModelManager The entity manager
     */
    protected $em;

    /**
     * @var \Enlight_Components_Db_Adapter_Pdo_Mysql
     */
    protected $db;

    /**
     * @var OutputInterface|null optional output used in CLI
     */
    protected $output;

    /**
     * @param string $kernelRoot
     */
    public function __construct(ModelManager $em, \Enlight_Components_Db_Adapter_Pdo_Mysql $db, $kernelRoot)
    {
        $this->em = $em;
        $this->db = $db;
        $this->kernelRoot = $kernelRoot;
    }

    public function setOutput(OutputInterface $output)
    {
        $this->output = $output;
    }

    /**
     * Loads all snippets from all files in $snippetsDir
     * (including subfolders) and writes them to the database.
     *
     * @param string|null $snippetsDir
     * @param bool        $force
     * @param string      $namespacePrefix allows to prefix the snippet namespace
     */
    public function loadToDatabase($snippetsDir = null, $force = false, $namespacePrefix = '')
    {
        $snippetsDir = $snippetsDir ?: $this->kernelRoot . '/snippets/';
        if (!file_exists($snippetsDir)) {
            if ($snippetsDir == ($this->kernelRoot . '/snippets/')) {
                $this->printWarning('<info>No snippets folder found in Shopware core, skipping</info>');
            }

            return;
        }

        $localeRepository = $this->em->getRepository('Shopware\Models\Shop\Locale');

        $inputAdapter = new \Enlight_Config_Adapter_File([
            'configDir' => $snippetsDir,
        ]);

        $databaseWriter = new DatabaseWriter($this->em->getConnection());
        $databaseWriter->setForce($force);

        $finder = new Finder();
        $finder->files()->in($snippetsDir);
        $defaultLocale = $localeRepository->findOneBy(['locale' => 'en_GB']);

        $snippetCount = $this->em->getConnection()->fetchArray('SELECT * FROM s_core_snippets LIMIT 1');
        $databaseWriter->setUpdate((bool) $snippetCount);

        foreach ($finder as $file) {
            $filePath = $file->getRelativePathname();
            if (strpos($filePath, '.ini') == strlen($filePath) - 4) {
                $namespace = substr($filePath, 0, -4);
                $namespace = str_replace('\\', '/', $namespace);
            } else {
                continue;
            }

            $this->printNotice('<info>Importing ' . $namespace . ' namespace</info>');

            $namespaceData = new \Enlight_Components_Snippet_Namespace([
                'adapter' => $inputAdapter,
                'name' => $namespace,
            ]);

            foreach ($namespaceData->read()->toArray() as $index => $values) {
                if ($index == 'default') {
                    $locale = $defaultLocale;
                } else {
                    $locale = $localeRepository->findOneBy(['locale' => $index]);
                }

                // Only write entry if locale was found
                if ($locale) {
                    $databaseWriter->write($values, $namespacePrefix . $namespace, $locale->getId(), 1);

                    $this->printNotice('<info>Imported ' . count($values) . ' snippets into ' . $locale->getLocale() . '</info>');
                }
            }

            $this->printNotice('<info></info>');
        }
    }

    /**
     * Dumps all snippets from database into the provided $snippetsDir
     *
     * @param string|null $snippetsDir
     * @param string      $localeName
     *
     * @throws \Exception
     */
    public function dumpFromDatabase($snippetsDir, $localeName)
    {
        $snippetsDir = $this->kernelRoot . '/' . $snippetsDir . '/';
        if (!file_exists($snippetsDir)) {
            return;
        }

        $snippetRepository = $this->em->getRepository('Shopware\Models\Snippet\Snippet');

        $locale = $this->em->getRepository('Shopware\Models\Shop\Locale')->findOneByLocale($localeName);
        if (!$locale) {
            throw new \Exception(\sprintf('Locale "%s" not found.', $localeName));
        }

        $outputAdapter = new \Enlight_Config_Adapter_File([
            'configDir' => $snippetsDir . '/',
        ]);
        $inputAdapter = new \Enlight_Config_Adapter_DbTable([
            'db' => $this->db,
            'table' => 's_core_snippets',
            'namespaceColumn' => 'namespace',
            'sectionColumn' => ['localeID', 'shopID'],
        ]);

        $namespaces = array_map(
            function ($result) {
                return $result['namespace'];
            },
            $snippetRepository->getDistinctNamespacesQuery($locale->getId())->getArrayResult()
        );

        if (count($namespaces) == 0) {
            $this->printWarning('<error>No snippets found for the given locale(s)</error>');

            return;
        }

        $data = [];

        foreach ($namespaces as $namespace) {
            if (!array_key_exists($namespace, $data)) {
                $data[$namespace] = true;
                $content = new \Enlight_Components_Snippet_Namespace([
                    'adapter' => $inputAdapter,
                    'name' => $namespace,
                    'section' => [
                        $locale->getId(),
                    ],
                ]);

                $content->setSection($locale->getLocale());
                $outputAdapter->write($content, true);
            }
        }
    }

    /**
     * Loads all snippets from all files in $snippetsDir
     * (including subfolders) and removes them from the database.
     *
     * @param string|null $snippetsDir
     * @param bool        $removeDirty
     */
    public function removeFromDatabase($snippetsDir = null, $removeDirty = false)
    {
        $snippetsDir = $snippetsDir ?: $this->kernelRoot . '/snippets/';
        if (!file_exists($snippetsDir)) {
            return;
        }

        $localeRepository = $this->em->getRepository(\Shopware\Models\Shop\Locale::class);

        $inputAdapter = new \Enlight_Config_Adapter_File([
            'configDir' => $snippetsDir,
        ]);

        $outputAdapter = new \Enlight_Config_Adapter_DbTable([
            'db' => $this->db,
            'table' => 's_core_snippets',
            'namespaceColumn' => 'namespace',
            'sectionColumn' => ['shopID', 'localeID'],
        ]);

        $finder = new Finder();
        $finder->files()->in($snippetsDir);
        $defaultLocale = $localeRepository->findOneBy(['locale' => 'en_GB']);

        foreach ($finder as $file) {
            $filePath = $file->getRelativePathname();
            if (strpos($filePath, '.ini') == strlen($filePath) - 4) {
                $namespace = substr($filePath, 0, -4);
                $namespace = str_replace('\\', '/', $namespace);
            } else {
                continue;
            }

            $this->printNotice('<info>Processing ' . $namespace . ' namespace</info>');

            $namespaceData = new \Enlight_Components_Snippet_Namespace([
                'adapter' => $inputAdapter,
                'name' => $namespace,
            ]);

            foreach ($namespaceData->read()->toArray() as $index => $values) {
                if ($index == 'default') {
                    $locale = $defaultLocale;
                } else {
                    $locale = $localeRepository->findOneBy(['locale' => $index]);
                }

                $namespaceData->setSection([1, $locale->getId()])->read();
                $namespaceData->setData($values);
                $outputAdapter->delete($namespaceData, array_keys($values), $removeDirty);

                $this->printNotice('<info>Deleted ' . count($values) . ' snippets from ' . $locale->getLocale() . '</info>');
            }

            $this->printNotice('<info></info>');
        }
    }

    /**
     * Prints given $message if output interface is set and it is verbose
     *
     * @param string $message
     */
    private function printNotice($message)
    {
        if ($this->output && $this->output->getVerbosity() >= OutputInterface::VERBOSITY_VERBOSE) {
            $this->output->writeln($message);
        }
    }

    /**
     * Prints given $message if output interface is set
     *
     * @param string $message
     */
    private function printWarning($message)
    {
        if ($this->output) {
            $this->output->writeln($message);
        }
    }
}
