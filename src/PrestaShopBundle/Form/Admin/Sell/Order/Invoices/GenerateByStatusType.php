<?php
/**
 * Copyright since 2007 PrestaShop SA and Contributors
 * PrestaShop is an International Registered Trademark & Property of PrestaShop SA
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.md.
 * It is also available through the world-wide-web at this URL:
 * https://opensource.org/licenses/OSL-3.0
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade PrestaShop to newer
 * versions in the future. If you wish to customize PrestaShop for your
 * needs please refer to https://devdocs.prestashop.com/ for more information.
 *
 * @author    PrestaShop SA and Contributors <contact@prestashop.com>
 * @copyright Since 2007 PrestaShop SA and Contributors
 * @license   https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace PrestaShopBundle\Form\Admin\Sell\Order\Invoices;

use PrestaShop\PrestaShop\Core\Form\FormChoiceProviderInterface;
use PrestaShopBundle\Form\Admin\Type\TranslatorAwareType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Translation\TranslatorInterface;

/**
 * Class generates "By order status" form
 * in "Sell > Orders > Invoices" page.
 *
 * Backwards compatibility break introduced in 1.7.8.0 due to extension of TranslationAwareType
 */
class GenerateByStatusType extends TranslatorAwareType
{
    public const FIELD_ORDER_STATES = 'order_states';

    /**
     * @var array
     */
    private $orderCountsByState;
    /**
     * @var FormChoiceProviderInterface
     */
    private $orderStateChoiceProvider;

    /**
     * @param TranslatorInterface $translator
     * @param array $locales
     * @param FormChoiceProviderInterface $orderStateChoiceProvider
     * @param array $orderCountsByState
     */
    public function __construct(
        TranslatorInterface $translator,
        array $locales,
        FormChoiceProviderInterface $orderStateChoiceProvider,
        array $orderCountsByState
    ) {
        parent::__construct($translator, $locales);
        $this->orderCountsByState = $orderCountsByState;
        $this->orderStateChoiceProvider = $orderStateChoiceProvider;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add(static::FIELD_ORDER_STATES, ChoiceType::class, [
                'required' => false,
                'expanded' => true,
                'multiple' => true,
                'choices' => $this->orderStateChoiceProvider->getChoices(),
                'label' => $this->trans('Order statuses', 'Admin.Orderscustomers.Feature'),
                'help' => $this->trans('You can also export orders which have not been charged yet.', 'Admin.Orderscustomers.Help'),
            ]);
    }

    /**
     * {@inheritdoc}
     */
    public function finishView(FormView $view, FormInterface $form, array $options)
    {
        /** @var FormView $child */
        foreach ($view->children['order_states'] as $child) {
            $child->vars['orders_count'] = 0;

            if (array_key_exists($child->vars['value'], $this->orderCountsByState)) {
                $child->vars['orders_count'] = $this->orderCountsByState[$child->vars['value']];
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'translation_domain' => 'Admin.Orderscustomers.Feature',
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'orders_invoices_by_status_block';
    }
}
