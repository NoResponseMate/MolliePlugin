<?php


declare(strict_types=1);

namespace Sylius\MolliePlugin\Order;

use Sylius\Component\Core\Model\OrderInterface;
use Sylius\Component\Core\Model\OrderItemInterface;
use Sylius\Component\Order\Factory\OrderItemUnitFactoryInterface;
use Sylius\Component\Resource\Factory\FactoryInterface;

final class OrderItemCloner implements OrderItemClonerInterface
{
    private FactoryInterface $orderItemFactory;

    private OrderItemUnitFactoryInterface $orderItemUnitFactory;

    public function __construct(
        FactoryInterface $orderItemFactory,
        OrderItemUnitFactoryInterface $orderItemUnitFactory
    ) {
        $this->orderItemFactory = $orderItemFactory;
        $this->orderItemUnitFactory = $orderItemUnitFactory;
    }

    public function clone(
        OrderItemInterface $orderItem,
        OrderInterface $order
    ): OrderItemInterface {
        /** @var OrderItemInterface $clonedOrderItem */
        $clonedOrderItem = $this->orderItemFactory->createNew();
        $clonedOrderItem->setOrder($order);
        $clonedOrderItem->setProductName($orderItem->getProductName());
        $clonedOrderItem->setUnitPrice($orderItem->getUnitPrice());
        $clonedOrderItem->setVariant($orderItem->getVariant());
        $clonedOrderItem->setVariantName($orderItem->getVariantName());
        $clonedOrderItem->setVersion($orderItem->getVersion());
        $clonedOrderItem->setImmutable(true);

        for ($orderItemQuantity = $orderItem->getQuantity(); 0 < $orderItemQuantity; --$orderItemQuantity) {
            $clonedUnit = $this->orderItemUnitFactory->createForItem($clonedOrderItem);
            $clonedOrderItem->addUnit($clonedUnit);
        }

        $clonedOrderItem->recalculateUnitsTotal();
        $clonedOrderItem->recalculateAdjustmentsTotal();

        return $clonedOrderItem;
    }
}
