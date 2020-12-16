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

namespace PrestaShopBundle\Form\Admin\Sell\CustomerService\MerchandiseReturn;

use PrestaShopBundle\Form\Admin\Type\SwitchType;
use PrestaShopBundle\Form\Admin\Type\TranslatableType;
use PrestaShopBundle\Form\Admin\Type\TranslatorAwareType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\GreaterThanOrEqual;

/**
 * Form type for merchandise returns options
 */
class MerchandiseReturnOptionsType extends TranslatorAwareType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('enable_order_return', SwitchType::class, [
                'label' => $this->trans('Enable returns', 'Admin.Orderscustomers.Feature'),
                'help' => $this->trans('Would you like to allow merchandise returns in your shop?', 'Admin.Orderscustomers.Help'),
                'required' => false,
            ])
            ->add('order_return_period_in_days', IntegerType::class, [
                'label' => $this->trans('Time limit of validity', 'Admin.Orderscustomers.Feature'),
                'help' => $this->trans('How many days after the delivery date does the customer have to return a product?', 'Admin.Orderscustomers.Help'),
                'required' => false,
                'constraints' => new GreaterThanOrEqual([
                    'value' => 0,
                ]),
            ])
            ->add('order_return_prefix', TranslatableType::class, [
                'label' => $this->trans('Return prefix', 'Admin.Orderscustomers.Feature'),
                'help' => $this->trans('Prefix used for merchandise returns (e.g. RE00001).', 'Admin.Orderscustomers.Help'),
                'required' => false,
            ])
        ;
    }
}
