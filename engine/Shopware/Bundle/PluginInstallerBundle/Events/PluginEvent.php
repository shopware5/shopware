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

namespace Shopware\Bundle\PluginInstallerBundle\Events;

use Shopware\Components\Plugin;
use Shopware\Components\Plugin\Context\InstallContext;

abstract class PluginEvent extends \Enlight_Event_EventArgs
{
    const PRE_INSTALL = PrePluginInstallEvent::class;
    const POST_INSTALL = PostPluginInstallEvent::class;
    const PRE_UNINSTALL = PrePluginUninstallEvent::class;
    const POST_UNINSTALL = PostPluginUninstallEvent::class;
    const PRE_UPDATE = PrePluginUpdateEvent::class;
    const POST_UPDATE = PostPluginUpdateEvent::class;
    const PRE_ACTIVATE = PrePluginActivateEvent::class;
    const POST_ACTIVATE = PostPluginActivateEvent::class;
    const PRE_DEACTIVATE = PrePluginDeactivateEvent::class;
    const POST_DEACTIVATE = PostPluginDeactivateEvent::class;

    public function __construct(InstallContext $context, Plugin $plugin)
    {
        parent::__construct(['context' => $context, 'plugin' => $plugin]);
    }

    /**
     * @return InstallContext
     */
    public function getContext()
    {
        return $this->get('context');
    }

    /**
     * @return Plugin
     */
    public function getPlugin()
    {
        return $this->get('plugin');
    }
}
