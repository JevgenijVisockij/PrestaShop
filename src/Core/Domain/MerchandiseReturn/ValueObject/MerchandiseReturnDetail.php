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

namespace PrestaShop\PrestaShop\Core\Domain\MerchandiseReturn\ValueObject;

/**
 * Provides merchandise return id
 */
class MerchandiseReturnDetail
{
    /**
     * @var MerchandiseReturnDetailId
     */
    private $merchandiseReturnDetailId;

    /**
     * @var CustomizationId|null
     */
    private $customizationId;

    public function __construct(int $merchandiseReturnDetailId)
    {
        $this->merchandiseReturnDetailId = new MerchandiseReturnDetailId($merchandiseReturnDetailId);
    }

    /**
     * @return MerchandiseReturnDetailId
     */
    public function getMerchandiseReturnDetailId(): MerchandiseReturnDetailId
    {
        return $this->merchandiseReturnDetailId;
    }

    /**
     * @return CustomizationId|null
     */
    public function getCustomizationId(): ?CustomizationId
    {
        return $this->customizationId;
    }

    /**
     * @param CustomizationId|null $customizationId
     *
     * @return MerchandiseReturnDetail
     */
    public function setCustomizationId(int $customizationId): MerchandiseReturnDetail
    {
        $this->customizationId = new CustomizationId($customizationId);

        return $this;
    }
}
