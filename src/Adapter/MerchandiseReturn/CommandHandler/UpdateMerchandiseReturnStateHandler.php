<?php
/**
 * 2007-2020 PrestaShop SA and Contributors
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
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
 * needs please refer to https://www.prestashop.com for more information.
 *
 * @author    PrestaShop SA <contact@prestashop.com>
 * @copyright 2007-2020 PrestaShop SA and Contributors
 * @license   https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 * International Registered Trademark & Property of PrestaShop SA
 */

declare(strict_types=1);

namespace PrestaShop\PrestaShop\Adapter\MerchandiseReturn\CommandHandler;

use PrestaShop\PrestaShop\Adapter\Entity\OrderReturn;
use PrestaShop\PrestaShop\Adapter\MerchandiseReturn\AbstractMerchandiseReturnHandler;
use PrestaShop\PrestaShop\Core\Domain\MerchandiseReturn\Command\UpdateMerchandiseReturnStateCommand;
use PrestaShop\PrestaShop\Core\Domain\MerchandiseReturn\CommandHandler\UpdateMerchandiseReturnStateHandlerInterface;
use PrestaShop\PrestaShop\Core\Domain\MerchandiseReturn\Exception\MerchandiseReturnException;

class UpdateMerchandiseReturnStateHandler extends AbstractMerchandiseReturnHandler implements UpdateMerchandiseReturnStateHandlerInterface
{
    /**
     * {@inheritdoc}
     */
    public function handle(UpdateMerchandiseReturnStateCommand $command): void
    {
        $merchandiseReturnId = $command->getMerchandiseReturnId();

        $orderReturn = $this->getOrderReturn($merchandiseReturnId);

        $orderReturn = $this->updateOrderReturnWithCommandData($orderReturn, $command);

        $this->assertRequiredFieldsAreNotMissing($orderReturn);

        if (false === $orderReturn->validateFields(false)) {
            throw new MerchandiseReturnException('Order return contains invalid field values');
        }

        if (false === $orderReturn->update()) {
            throw new MerchandiseReturnException('Failed to update order return');
        }
    }

    /**
     * @param OrderReturn $orderReturn
     * @param UpdateMerchandiseReturnStateCommand $command
     *
     * @return OrderReturn
     */
    private function updateOrderReturnWithCommandData(OrderReturn $orderReturn, UpdateMerchandiseReturnStateCommand $command): OrderReturn
    {
        if (null !== $command->getMerchandiseReturnStateId()->getValue()) {
            $orderReturn->state = $command->getMerchandiseReturnStateId()->getValue();
        }

        return $orderReturn;
    }
}
