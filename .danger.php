<?php declare(strict_types=1);

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

use Danger\Config;
use Danger\Context;
use Danger\Platform\Github\Github;
use Danger\Platform\Gitlab\Gitlab;
use Danger\Rule\CommitRegex;
use Danger\Rule\Condition;
use Danger\Rule\DisallowRepeatedCommits;
use Danger\Struct\File;

return (new Config())
    ->useThreadOn(Config::REPORT_LEVEL_WARNING)
    ->useRule(new DisallowRepeatedCommits())
    ->useRule(function (Context $context): void {
        $files = $context->platform->pullRequest->getFiles();

        if ($files->matches('UPGRADE-*.md')->count() === 0) {
            $context->warning('The Pull Request doesn\'t contain any changes to the Upgrade file');
        }
    })
    ->useRule(new CommitRegex(
                '/(?m)(?mi)^(build|chore|ci|docs|feat|fix|perf|refactor|revert|style|test){1}(\([\w\-\.]+\))?(!)?: ([\w ])+([\s\S]*)/m',
                'The commit title `###MESSAGE###` does not match our requirements. Please follow: www.conventionalcommits.org'
            )
    )
    ->useRule(function (Context $context): void {
        $files = $context->platform->pullRequest->getFiles();

        $invalidFiles = [];

        foreach ($files as $file) {
            if ($file->status !== File::STATUS_REMOVED && preg_match('/^([-\.\w\/]+)$/', $file->name) === 0) {
                $invalidFiles[] = $file->name;
            }
        }

        if (count($invalidFiles) > 0) {
            $context->failure(
                'The following filenames contain invalid special characters, please use only alphanumeric characters, dots, dashes and underscores: <br/>'
                . print_r($invalidFiles, true)
            );
        }
    })
    ->after(function (Context $context): void {
        if ($context->platform instanceof Github && $context->hasFailures()) {
            $context->platform->addLabels('Incomplete');
        }
    })
;
