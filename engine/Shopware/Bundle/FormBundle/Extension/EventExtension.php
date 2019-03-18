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

namespace Shopware\Bundle\FormBundle\Extension;

use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;

class EventExtension extends AbstractTypeExtension
{
    /**
     * @var \Enlight_Event_EventManager
     */
    private $eventManager;

    public function __construct(\Enlight_Event_EventManager $eventManager)
    {
        $this->eventManager = $eventManager;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $this->eventManager->notify('Shopware_Form_BuildForm', [
            'reference' => $builder->getForm()->getConfig()->getType()->getBlockPrefix(),
            'builder' => $builder,
        ]);

        $builder->addEventListener(FormEvents::PRE_SET_DATA, [$this, 'triggerEvent']);
    }

    /**
     * Returns the name of the type being extended.
     *
     * @return string The name of the type being extended
     */
    public function getExtendedType()
    {
        return \Symfony\Component\Form\Extension\Core\Type\FormType::class;
    }

    /**
     * Trigger general form builder event with reference to the \Symfony\Component\Form\FormInterface type
     */
    public function triggerEvent(FormEvent $event)
    {
        /*
         * This is a legacy event which returns the already build form (\Symfony\Component\Form\FormInterface)
         * in the "builder" parameter. It is called on the Symfony internal event `FormEvents::PRE_SET_DATA.
         *
         * If you want to access the instance with \Symfony\Component\Form\FormBuilderInterface,
         * use the event "Shopware_Form_BuildForm" instead.
         */
        $this->eventManager->notify('Shopware_Form_Builder', [
            'reference' => $event->getForm()->getConfig()->getType()->getBlockPrefix(),
            'builder' => $event->getForm(),
        ]);
    }
}
