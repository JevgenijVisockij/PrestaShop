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

namespace PrestaShopBundle\Controller\Admin\Sell\CustomerService;

use Exception;
use PrestaShop\PrestaShop\Core\Domain\OrderReturn\Command\BulkDeleteProductFromOrderReturnCommand;
use PrestaShop\PrestaShop\Core\Domain\OrderReturn\Command\DeleteProductFromOrderReturnCommand;
use PrestaShop\PrestaShop\Core\Domain\OrderReturn\Exception\BulkDeleteOrderReturnProductException;
use PrestaShop\PrestaShop\Core\Domain\OrderReturn\Exception\DeleteOrderReturnProductException;
use PrestaShop\PrestaShop\Core\Domain\OrderReturn\Exception\MissingOrderReturnRequiredFieldsException;
use PrestaShop\PrestaShop\Core\Domain\OrderReturn\Exception\OrderReturnConstraintException;
use PrestaShop\PrestaShop\Core\Domain\OrderReturn\Exception\OrderReturnNotFoundException;
use PrestaShop\PrestaShop\Core\Domain\OrderReturn\Exception\OrderReturnOrderStateConstraintException;
use PrestaShop\PrestaShop\Core\Domain\OrderReturn\Exception\UpdateOrderReturnException;
use PrestaShop\PrestaShop\Core\Domain\OrderReturn\Query\GetOrderReturnForEditing;
use PrestaShop\PrestaShop\Core\Domain\OrderReturn\QueryResult\EditableOrderReturn;
use PrestaShop\PrestaShop\Core\Domain\OrderReturn\ValueObject\OrderReturnDetailId;
use PrestaShop\PrestaShop\Core\Exception\CoreException;
use PrestaShop\PrestaShop\Core\Form\FormHandlerInterface;
use PrestaShop\PrestaShop\Core\Search\Filters\MerchandiseReturnFilters;
use PrestaShop\PrestaShop\Core\Search\Filters\OrderReturnProductsFilters;
use PrestaShopBundle\Controller\Admin\FrameworkBundleAdminController;
use PrestaShopBundle\Security\Annotation\AdminSecurity;
use PrestaShopBundle\Security\Annotation\DemoRestricted;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class MerchandiseReturnController responsible for "Sell > Customer Service > Merchandise Returns" page
 */
class MerchandiseReturnController extends FrameworkBundleAdminController
{
    private const ORDER_RETURN_STATE_WAITING_FOR_PACKAGE_ID = 2;

    /**
     * Render merchandise returns grid and options.
     *
     * @AdminSecurity(
     *     "is_granted(['read'], request.get('_legacy_controller'))",
     *     redirectRoute="admin_merchandise_returns_index"
     * )
     *
     * @param Request $request
     * @param MerchandiseReturnFilters $filters
     *
     * @return Response
     *
     * @throws Exception
     */
    public function indexAction(Request $request, MerchandiseReturnFilters $filters): Response
    {
        $gridFactory = $this->get('prestashop.core.grid.factory.merchandise_return');

        $optionsFormHandler = $this->getOptionsFormHandler();
        $optionsForm = $optionsFormHandler->getForm();
        $optionsForm->handleRequest($request);

        if ($optionsForm->isSubmitted() && $optionsForm->isValid()) {
            $errors = $optionsFormHandler->save($optionsForm->getData());

            if (empty($errors)) {
                $this->addFlash('success', $this->trans('Update successful', 'Admin.Notifications.Success'));
            } else {
                $this->flashErrors($errors);
            }
        }

        return $this->render('@PrestaShop/Admin/Sell/CustomerService/MerchandiseReturn/index.html.twig', [
            'merchandiseReturnsGrid' => $this->presentGrid($gridFactory->getGrid($filters)),
            'merchandiseReturnsOptionsForm' => $optionsForm->createView(),
        ]);
    }

    /**
     * Edit existing order return
     *
     * @AdminSecurity(
     *     "is_granted(['update'], request.get('_legacy_controller'))",
     *     redirectRoute="admin_merchandise_returns_index"
     * )
     *
     * @param int $orderReturnId
     * @param Request $request
     * @param OrderReturnProductsFilters $filters
     *
     * @return Response
     */
    public function editAction(int $orderReturnId, OrderReturnProductsFilters $filters, Request $request): Response
    {
        $formBuilder = $this->get('prestashop.core.form.identifiable_object.builder.order_return_form_builder');
        $formHandler = $this->get('prestashop.core.form.identifiable_object.handler.order_return_form_handler');
        $gridFactory = $this->get('prestashop.core.grid.factory.order_return_products');

        try {
            /** @var EditableOrderReturn $editableOrderReturn */
            $editableOrderReturn = $this->getQueryBus()->handle(
                new GetOrderReturnForEditing(
                    $orderReturnId
                )
            );

            $form = $formBuilder->getFormFor($orderReturnId);
            $form->handleRequest($request);

            $result = $formHandler->handleFor($orderReturnId, $form);

            if ($result->getIdentifiableObjectId()) {
                $this->addFlash('success', $this->trans('Update successful', 'Admin.Notifications.Success'));

                return $this->redirectToRoute('admin_merchandise_returns_index');
            }
        } catch (Exception $e) {
            $this->addFlash('error', $this->getErrorMessageForException($e, $this->getErrorMessages()));

            return $this->redirectToRoute('admin_merchandise_returns_index');
        }

        $allowPrintingOrderReturnPdf = false;

        if ($editableOrderReturn->getOrderReturnStateId() === self::ORDER_RETURN_STATE_WAITING_FOR_PACKAGE_ID) {
            $allowPrintingOrderReturnPdf = true;
        }

        return $this->render('@PrestaShop/Admin/Sell/CustomerService/OrderReturn/edit.html.twig', [
            'help_link' => $this->generateSidebarLink($request->attributes->get('_legacy_controller')),
            'enableSidebar' => true,
            'layoutTitle' => sprintf($this->trans('Return Merchandise Authorization (RMA)', 'Admin.Orderscustomers.Feature')),
            'orderReturnForm' => $form->createView(),
            'editableOrderReturn' => $editableOrderReturn,
            'orderReturnsProductsGrid' => $this->presentGrid($gridFactory->getGrid($filters)),
            'allowPrintingOrderReturnPdf' => $allowPrintingOrderReturnPdf,
        ]);
    }

    /**
     * @AdminSecurity("is_granted('delete', request.get('_legacy_controller'))", redirectRoute="admin_merchandise_returns_index")
     *
     * @param Request $request
     * @param int $orderReturnId
     *
     * @return void
     *
     * @throws CoreException
     */
    public function generateOrderReturnPdfAction(Request $request, int $orderReturnId): void
    {
        $this->get('prestashop.adapter.pdf.order_return_pdf_generator')->generatePDF([$orderReturnId]);

        // When using legacy generator,
        // we want to be sure that displaying PDF is the last thing this controller will do
        die();
    }

    /**
     * @AdminSecurity("is_granted('delete', request.get('_legacy_controller'))", redirectRoute="admin_merchandise_returns_index")
     *
     * @param Request $request
     * @param int $orderReturnId
     * @param int $orderReturnDetailId
     *
     * @return RedirectResponse
     */
    public function deleteProductAction(Request $request, int $orderReturnId, int $orderReturnDetailId): RedirectResponse
    {
        try {
            $this->getCommandBus()->handle(
                new DeleteProductFromOrderReturnCommand($orderReturnId, $orderReturnDetailId)
            );

            $this->addFlash(
                'success',
                $this->trans('Successful deletion.', 'Admin.Notifications.Success')
            );
        } catch (Exception $e) {
            $this->addFlash('error', $this->getErrorMessageForException($e, $this->getErrorMessages($e)));
        }

        return $this->redirectToRoute(
            'admin_order_returns_edit',
            [
                'orderReturnId' => $orderReturnId,
            ]
        );
    }

    /**
     * Deletes order return products on bulk action
     *
     * @DemoRestricted(redirectRoute="admin_merchandise_returns_index")
     *
     * @param int $orderReturnId
     * @param Request $request
     *
     * @return RedirectResponse
     */
    public function bulkDeleteProductAction(int $orderReturnId, Request $request): RedirectResponse
    {
        $orderReturnDetails = $this->getBulkOrderReturnDetailsFromRequest($request);

        try {
            $this->getCommandBus()->handle(
                new BulkDeleteProductFromOrderReturnCommand(
                    $orderReturnId,
                    $orderReturnDetails
                )
            );
            $this->addFlash(
                'success',
                $this->trans('Successful deletion.', 'Admin.Notifications.Success')
            );
        } catch (Exception $e) {
            $this->addFlash('error', $this->getErrorMessageForException($e, $this->getErrorMessages($e)));
        }

        return $this->redirectToRoute(
            'admin_order_returns_edit',
            [
                'orderReturnId' => $orderReturnId,
            ]
        );
    }

    /**
     * Provides order return ids from request of bulk action
     *
     * @param Request $request
     *
     * @return OrderReturnDetailId[]
     */
    private function getBulkOrderReturnDetailsFromRequest(Request $request): array
    {
        $orderReturnDetailIds = $request->request->get('order_return_products_order_return_bulk');
        if (!is_array($orderReturnDetailIds)) {
            return [];
        }

        return $orderReturnDetailIds;
    }

    /**
     * @return FormHandlerInterface
     */
    private function getOptionsFormHandler(): FormHandlerInterface
    {
        return $this->get('prestashop.admin.merchandise_return_options.form_handler');
    }

    /**
     * Provides error messages for exceptions
     *
     * @return array
     */
    private function getErrorMessages(Exception $e = null): array
    {
        return [
            OrderReturnConstraintException::class => [
                OrderReturnConstraintException::INVALID_ID => $this->trans(
                    'The object cannot be loaded (the identifier is missing or invalid)',
                    'Admin.Notifications.Error'
                ),
            ],
            BulkDeleteOrderReturnProductException::class => sprintf(
                '%s: %s',
                $this->trans(
                    'An error occurred while deleting this selection.',
                    'Admin.Notifications.Error'
                ),
                $e instanceof BulkDeleteOrderReturnProductException ? implode(', ', $e->getOrderReturnDetailIds()) : ''
            ),
            DeleteOrderReturnProductException::class => [
                DeleteOrderReturnProductException::ORDER_RETURN_PRODUCT_NOT_FOUND => $this->trans(
                    'An error occurred while deleting this product, order product not found.',
                    'Admin.Notifications.Error'
                ),
                DeleteOrderReturnProductException::LAST_ORDER_RETURN_PRODUCT => $this->trans(
                    'An error occurred while deleting this product, can\'t delete last product in order return.',
                    'Admin.Notifications.Error'
                ),
                DeleteOrderReturnProductException::UNEXPECTED_ERROR => $this->trans(
                    'An error occurred while deleting this product.',
                    'Admin.Notifications.Error'
                ),
            ],
            MissingOrderReturnRequiredFieldsException::class => $this->trans(
                'Missing required fields for merchandise return.',
                'Admin.Notifications.Error'
            ),
            OrderReturnNotFoundException::class => $this->trans(
                'Merchandise return not found.',
                'Admin.Notifications.Error'
            ),
            OrderReturnOrderStateConstraintException::class => [
                OrderReturnOrderStateConstraintException::INVALID_ID => $this->trans(
                    'The object cannot be loaded (the identifier is missing or invalid)',
                    'Admin.Notifications.Error'
                ),
            ],
            UpdateOrderReturnException::class => $this->trans(
                'An error occurred while trying to update merchandise return.',
                'Admin.Notifications.Error'
            ),
        ];
    }
}
