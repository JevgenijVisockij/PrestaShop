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

namespace PrestaShopBundle\Form\Admin\Sell\Order\Delivery;

use DateTime;
use PrestaShopBundle\Form\Admin\Type\CommonAbstractType;
use PrestaShopBundle\Form\Admin\Type\DatePickerType;
use PrestaShopBundle\Form\Admin\Type\TranslatorAwareType;
use Symfony\Component\Form\FormBuilderInterface;

/**
 * This form class generates the "Pdf" form in Delivery slips page.
 */
class SlipPdfType extends TranslatorAwareType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $now = (new DateTime())->format('Y-m-d');
        $builder
            ->add(
                'date_from',
                DatePickerType::class,
                [
                    'required' => false,
                    'attr' => ['placeholder' => 'YYYY-MM-DD'],
                    'data' => $now,
                    'empty_data' => $now,
                    'label' => $this->trans('From', 'Admin.Global'),
                    'help' => $this->trans('Format: 2011-12-31 (inclusive).', 'Admin.Orderscustomers.Help')
                ]
            )
            ->add(
                'date_to',
                DatePickerType::class,
                [
                    'required' => false,
                    'attr' => ['placeholder' => 'YYYY-MM-DD'],
                    'data' => $now,
                    'empty_data' => $now,
                    'label' => $this->trans('To', 'Admin.Global'),
                    'help' => $this->trans('Format: 2011-12-31 (inclusive).', 'Admin.Orderscustomers.Help')
                ]
            );
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'order_delivery_slip_options';
    }
}
