<?php

namespace Sylius\ShopApiPlugin\Factory;

use Sylius\Component\Core\Model\ShipmentInterface;
use Sylius\Component\Core\Model\ShippingMethodInterface;
use Sylius\Component\Registry\ServiceRegistry;
use Sylius\Component\Shipping\Calculator\CalculatorInterface;
use Sylius\ShopApiPlugin\View\ShippingMethodView;

final class ShippingMethodViewFactory implements ShippingMethodViewFactoryInterface
{
    /** @var ServiceRegistry */
    private $calculatorRegistry;

    /** @var PriceViewFactoryInterface */
    private $priceViewFactory;

    /** @var string */
    private $shippingMethodViewClass;

    public function __construct(
        ServiceRegistry $calculatorRegistry,
        PriceViewFactoryInterface $priceViewFactory,
        string $shippingMethodViewClass
    ) {
        $this->calculatorRegistry = $calculatorRegistry;
        $this->priceViewFactory = $priceViewFactory;
        $this->shippingMethodViewClass = $shippingMethodViewClass;
    }

    /**
     * {@inheritdoc}
     */
    public function create(ShipmentInterface $shipment, string $locale): ShippingMethodView
    {
        return $this->createWithShippingMethod($shipment, $shipment->getMethod(), $locale);
    }

    /**
     * {@inheritdoc}
     */
    public function createWithShippingMethod(
        ShipmentInterface $shipment,
        ShippingMethodInterface $shippingMethod,
        string $locale
    ): ShippingMethodView {
        /** @var CalculatorInterface $calculator */
        $calculator = $this->calculatorRegistry->get($shippingMethod->getCalculator());

        /** @var ShippingMethodView $shippingMethodView */
        $shippingMethodView = new $this->shippingMethodViewClass();

        $shippingMethodView->code = $shippingMethod->getCode();
        $shippingMethodView->name = $shippingMethod->getTranslation($locale)->getName();
        $shippingMethodView->description = $shippingMethod->getTranslation($locale)->getDescription();
        $shippingMethodView->price = $this->priceViewFactory->create(
            $calculator->calculate($shipment, $shippingMethod->getConfiguration())
        );

        return $shippingMethodView;
    }
}
