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

namespace Shopware\Components\Emotion;

use Enlight\Event\SubscriberInterface;

class EmotionComponentViewSubscriber implements SubscriberInterface
{
    /**
     * @var string
     */
    private $path;

    /**
     * @param string $path
     */
    public function __construct($path)
    {
        $this->path = $path;
    }

    public static function getSubscribedEvents()
    {
        return [
            'Enlight_Controller_Action_PostDispatchSecure_Widgets_Emotion' => 'registerWidgetTemplates',
            'Enlight_Controller_Action_PostDispatchSecure_Backend_Emotion' => 'registerBackendTemplates',
        ];
    }

    public function registerBackendTemplates(\Enlight_Event_EventArgs $args)
    {
        /** @var \Enlight_View_Default $view */
        $view = $args->get('subject')->View();

        $templateDir = $this->path . '/Resources/views/emotion_components/';

        if (!file_exists($templateDir)) {
            return;
        }

        $view->addTemplateDir($templateDir);
        $backendPath = $templateDir . 'backend/';
        if (!file_exists($backendPath)) {
            return;
        }

        $directoryIterator = new \DirectoryIterator($backendPath);
        $regex = new \RegexIterator($directoryIterator, '/^.+\.js$/i', \RecursiveRegexIterator::GET_MATCH);
        foreach ($regex as $file) {
            $path = 'backend/' . $file[0];
            $view->extendsBlock(
                'backend/Emotion/app',
                PHP_EOL . '{include file="' . $path . '"}',
                'append'
            );
        }
    }

    public function registerWidgetTemplates(\Enlight_Event_EventArgs $args)
    {
        /** @var \Enlight_View_Default $view */
        $view = $args->get('subject')->View();

        if (file_exists($this->path . '/Resources/views/emotion_components/')) {
            $view->addTemplateDir($this->path . '/Resources/views/emotion_components/');
        }
    }
}
