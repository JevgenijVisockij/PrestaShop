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
use PrestaShopBundle\Form\Admin\Type\SwitchType;
use PrestaShopBundle\Form\Admin\Type\TranslatableType;
use PrestaShopBundle\Form\Admin\Type\TranslatorAwareType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Translation\TranslatorInterface;

/**
 * Class InvoiceOptionsType generates "Invoice options" form
 * in "Sell > Orders > Invoices" page.
 */
class InvoiceOptionsType extends TranslatorAwareType
{
    public const FIELD_ENABLE_INVOICES = 'enable_invoices';
    public const FIELD_ENABLE_TAX_BREAKDOWN = 'enable_tax_breakdown';
    public const FIELD_ENABLE_PRODUCT_IMAGES = 'enable_product_images';
    public const FIELD_INVOICE_PREFIX = 'invoice_prefix';
    public const FIELD_ADD_CURRENT_YEAR = 'add_current_year';
    public const FIELD_RESET_NUMBER_ANNUALLY = 'reset_number_annually';
    public const FIELD_YEAR_POSITION = 'year_position';
    public const FIELD_INVOICE_NUMBER = 'invoice_number';
    public const FIELD_LEGAL_FREE_TEXT = 'legal_free_text';
    public const FIELD_FOOTER_TEXT = 'footer_text';
    public const FIELD_INVOICE_MODEL = 'invoice_model';
    public const FIELD_USE_DISK_CACHE = 'use_disk_cache';
    /**
     * @var FormChoiceProviderInterface
     */
    private $invoiceModelChoiceProvider;

    /**
     * @var int the next available invoice number
     */
    private $nextInvoiceNumber;

    /**
     * @param TranslatorInterface $translator
     * @param array $locales
     * @param FormChoiceProviderInterface $invoiceModelChoiceProvider
     * @param int $nextInvoiceNumber the next available invoice number
     */
    public function __construct(
        TranslatorInterface $translator,
        array $locales,
        FormChoiceProviderInterface $invoiceModelChoiceProvider,
        $nextInvoiceNumber
    ) {
        parent::__construct($translator, $locales);
        $this->invoiceModelChoiceProvider = $invoiceModelChoiceProvider;
        $this->nextInvoiceNumber = $nextInvoiceNumber;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add(static::FIELD_ENABLE_INVOICES, SwitchType::class, [
                'label' => $this->trans('Enable invoices', 'Admin.Orderscustomers.Feature'),
                'help' => $this->trans('If enabled, your customers will receive an invoice for the purchase.', 'Admin.Orderscustomers.Help'),
            ])
            ->add(static::FIELD_ENABLE_TAX_BREAKDOWN, SwitchType::class, [
                'label' => $this->trans('Enable tax breakdown', 'Admin.Orderscustomers.Feature'),
                'help' => $this->trans('If required, show the total amount per rate of the corresponding tax.', 'Admin.Orderscustomers.Help'),
            ])
            ->add(static::FIELD_ENABLE_PRODUCT_IMAGES, SwitchType::class, [
                'label' => $this->trans('Enable product image', 'Admin.Orderscustomers.Feature'),
                'help' => $this->trans('Add an image in front of the product name on the invoice.', 'Admin.Orderscustomers.Help'),
            ])
            ->add(static::FIELD_INVOICE_PREFIX, TranslatableType::class, [
                'options' => [
                ],
                'required' => false,
                'type' => TextType::class,
                'label' => $this->trans('Invoice prefix', 'Admin.Orderscustomers.Feature'),
                'help' => $this->trans('Freely definable prefix for invoice number (e.g. #IN00001).', 'Admin.Orderscustomers.Help'),
            ])
            ->add(static::FIELD_ADD_CURRENT_YEAR, SwitchType::class, [
                'label' => $this->trans('Add current year to invoice number', 'Admin.Orderscustomers.Feature'),
            ])
            ->add(static::FIELD_RESET_NUMBER_ANNUALLY, SwitchType::class, [
                'label' => $this->trans('Reset sequential invoice number at the beginning of the year', 'Admin.Orderscustomers.Feature'),
            ])
            ->add(static::FIELD_YEAR_POSITION, ChoiceType::class, [
                'choices' => [
                    $this->trans('After the sequential number', 'Admin.Orderscustomers.Feature') => 0,
                    $this->trans('Before the sequential number', 'Admin.Orderscustomers.Feature') => 1,
                ],
                'expanded' => true,
                'label' => $this->trans('Position of the year date', 'Admin.Orderscustomers.Feature'),
            ])
            ->add(static::FIELD_INVOICE_NUMBER, NumberType::class, [
                'required' => false,
                'label' => $this->trans('Invoice number', 'Admin.Orderscustomers.Feature'),
                'help' => $this->trans(
                    'The next invoice number will begin with this number, and then increase with each additional invoice. Enter a positive integer greater than the last invoice number or set to 0 if you want to keep the current number (which is %number%).',
                    'Admin.OrdersCustomers.Help',
                    ['%number%' => $this->nextInvoiceNumber]
                ),
            ])
            ->add(static::FIELD_LEGAL_FREE_TEXT, TranslatableType::class, [
                'required' => false,
                'type' => TextareaType::class,
                'label' => $this->trans('Legal free text', 'Admin.Orderscustomers.Feature'),
                'help' => $this->trans('Use this field to show additional information on the invoice, below the payment methods summary (like specific legal information).', 'Admin.Orderscustomers.Help'),
            ])
            ->add(static::FIELD_FOOTER_TEXT, TranslatableType::class, [
                'required' => false,
                'type' => TextType::class,
                'label' => $this->trans('Footer text', 'Admin.Orderscustomers.Feature'),
                'help' => $this->trans('This text will appear at the bottom of the invoice, below your company details.', 'Admin.Orderscustomers.Help'),
            ])
            ->add(static::FIELD_INVOICE_MODEL, ChoiceType::class, [
                'placeholder' => false,
                'choices' => $this->invoiceModelChoiceProvider->getChoices(),
                'translation_domain' => false,
                'label' => $this->trans('Invoice model', 'Admin.Orderscustomers.Feature'),
                'help' => $this->trans('Choose an invoice model.', 'Admin.Orderscustomers.Help'),
            ])
            ->add(static::FIELD_USE_DISK_CACHE, SwitchType::class, [
                'label' => $this->trans('Use the disk as cache for PDF invoices', 'Admin.Orderscustomers.Feature'),
                'help' => $this->trans('Note that it saves memory but slows down the PDF generation.', 'Admin.Orderscustomers.Help'),
            ]);
    }

    /**
     * {@inheritdoc}
     */
    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        $view->vars['next_invoice_number'] = $this->nextInvoiceNumber;
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
}
