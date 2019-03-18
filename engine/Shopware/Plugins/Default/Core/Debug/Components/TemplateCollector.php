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

namespace Shopware\Plugin\Debug\Components;

use Shopware\Components\Logger;

class TemplateCollector implements CollectorInterface
{
    /**
     * @var \Enlight_Template_Manager
     */
    protected $template;

    /**
     * @var string
     */
    protected $rootDir;

    /**
     * @var Utils
     */
    private $utils;

    /**
     * @param string $rootDir
     */
    public function __construct(\Enlight_Template_Manager $template, Utils $utils, $rootDir)
    {
        $this->template = $template;
        $this->rootDir = $rootDir;
        $this->utils = $utils;
    }

    public function start()
    {
        $this->template->setDebugging(true);
        $this->template->debug_tpl = 'string:';
    }

    /**
     * Logs all rendered templates into the internal log object.
     * Each logged template contains the template name, the required compile time,
     * the required render time and the required cache time.
     */
    public function logResults(Logger $log)
    {
        $rows = [['name', 'compile_time', 'render_time', 'cache_time']];
        $total_time = 0;
        foreach (\Smarty_Internal_Debug::$template_data as $template_file) {
            $total_time += $template_file['render_time'];
            $total_time += $template_file['cache_time'];
            $template_file['name'] = str_replace($this->rootDir, '', $template_file['name']);
            $template_file['compile_time'] = $this->utils->formatTime($template_file['compile_time']);
            $template_file['render_time'] = $this->utils->formatTime($template_file['render_time']);
            $template_file['cache_time'] = $this->utils->formatTime($template_file['cache_time']);
            unset($template_file['start_time']);
            $rows[] = array_values($template_file);
        }
        $total_time = round($total_time, 5);
        $total_count = count($rows) - 1;
        $label = "Benchmark Template ($total_count @ $total_time sec)";
        $table = [$label, $rows];

        $log->table($table);
    }
}
