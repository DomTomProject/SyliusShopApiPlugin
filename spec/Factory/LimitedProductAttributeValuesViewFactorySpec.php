<?php

namespace spec\Sylius\ShopApiPlugin\Factory;

use Sylius\Component\Product\Model\ProductAttributeValueInterface;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Sylius\ShopApiPlugin\Factory\LimitedProductAttributeValuesViewFactory;
use Sylius\ShopApiPlugin\Factory\ProductAttributeValuesViewFactoryInterface;
use Sylius\ShopApiPlugin\Factory\ProductAttributeValueViewFactoryInterface;
use Sylius\ShopApiPlugin\View\ProductAttributeValueView;

final class LimitedProductAttributeValuesViewFactorySpec extends ObjectBehavior
{
    function let(ProductAttributeValueViewFactoryInterface $productAttributeValueViewFactory)
    {
        $this->beConstructedWith($productAttributeValueViewFactory, ['CERTIFICATE_ATTRIBUTE']);
    }

    function it_is_product_attribute_values_view_facotry()
    {
        $this->shouldHaveType(ProductAttributeValuesViewFactoryInterface::class);
    }

    function it_creates_filitered_array_of_product_attribute_values(
        ProductAttributeValueInterface $skippedValue,
        ProductAttributeValueInterface $serializedValue,
        ProductAttributeValueViewFactoryInterface $productAttributeValueViewFactory
    ) {
        $productAttributeValueViewFactory->create($skippedValue)->shouldNotBeCalled();
        $productAttributeValueViewFactory->create($serializedValue)->willReturn(new ProductAttributeValueView());

        $serializedValue->getCode()->willReturn('CERTIFICATE_ATTRIBUTE');
        $skippedValue->getCode()->willReturn('THIS_CODE_SHOULD_NOT_BE_PARSED');

        $this->create([$skippedValue, $serializedValue])->shouldBeLike([new ProductAttributeValueView()]);
    }
}
