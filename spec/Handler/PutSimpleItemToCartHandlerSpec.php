<?php

namespace spec\Sylius\ShopApiPlugin\Handler;

use Doctrine\Common\Persistence\ObjectManager;
use Sylius\Component\Core\Factory\CartItemFactoryInterface;
use Sylius\Component\Core\Model\OrderInterface;
use Sylius\Component\Core\Model\OrderItemInterface;
use Sylius\Component\Core\Model\ProductInterface;
use Sylius\Component\Core\Model\ProductVariantInterface;
use Sylius\Component\Core\Repository\OrderRepositoryInterface;
use Sylius\Component\Core\Repository\ProductRepositoryInterface;
use Sylius\Component\Order\Modifier\OrderItemQuantityModifierInterface;
use Sylius\Component\Order\Processor\OrderProcessorInterface;
use Sylius\ShopApiPlugin\Command\PutSimpleItemToCart;
use Sylius\ShopApiPlugin\Handler\PutSimpleItemToCartHandler;
use PhpSpec\ObjectBehavior;

final class PutSimpleItemToCartHandlerSpec extends ObjectBehavior
{
    function let(
        OrderRepositoryInterface $cartRepository,
        ProductRepositoryInterface $productRepository,
        CartItemFactoryInterface $cartItemFactory,
        OrderItemQuantityModifierInterface $orderItemModifier,
        OrderProcessorInterface $orderProcessor,
        ObjectManager $manager
    ) {
        $this->beConstructedWith($cartRepository, $productRepository, $cartItemFactory, $orderItemModifier, $orderProcessor, $manager);
    }

    function it_handles_putting_new_item_to_cart(
        OrderItemInterface $cartItem,
        OrderInterface $cart,
        CartItemFactoryInterface $cartItemFactory,
        OrderItemQuantityModifierInterface $orderItemModifier,
        OrderProcessorInterface $orderProcessor,
        OrderRepositoryInterface $cartRepository,
        ProductInterface $product,
        ProductRepositoryInterface $productRepository,
        ProductVariantInterface $productVariant,
        ObjectManager $manager
    ) {
        $productRepository->findOneBy(['code' => 'T_SHIRT_CODE'])->willReturn($product);
        $product->getVariants()->willReturn([$productVariant]);
        $product->isSimple()->willReturn(true);

        $cartRepository->findOneBy(['tokenValue' => 'ORDERTOKEN'])->willReturn($cart);
        $cartItemFactory->createForCart($cart)->willReturn($cartItem);

        $cartItem->setVariant($productVariant)->shouldBeCalled();
        $orderItemModifier->modify($cartItem, 5)->shouldBeCalled();

        $orderProcessor->process($cart)->shouldBeCalled();

        $manager->persist($cart)->shouldBeCalled();

        $this->handle(new PutSimpleItemToCart('ORDERTOKEN', 'T_SHIRT_CODE', 5));
    }

    function it_throws_an_exception_if_cart_has_not_been_found(OrderRepositoryInterface $cartRepository)
    {
        $cartRepository->findOneBy(['tokenValue' => 'ORDERTOKEN'])->willReturn(null);

        $this->shouldThrow(\InvalidArgumentException::class)->during('handle', [
            new PutSimpleItemToCart('ORDERTOKEN', 'T_SHIRT_CODE', 5),
        ]);
    }

    function it_throws_an_exception_if_product_has_not_been_found(
        OrderInterface $cart,
        OrderRepositoryInterface $cartRepository,
        ProductRepositoryInterface $productRepository
    ) {
        $cartRepository->findOneBy(['tokenValue' => 'ORDERTOKEN'])->willReturn($cart);
        $productRepository->findOneBy(['code' => 'T_SHIRT_CODE'])->willReturn(null);

        $this->shouldThrow(\InvalidArgumentException::class)->during('handle', [
            new PutSimpleItemToCart('ORDERTOKEN', 'T_SHIRT_CODE', 5),
        ]);
    }

    function it_throws_an_exception_if_product_is_configurable(
        OrderInterface $cart,
        OrderRepositoryInterface $cartRepository,
        ProductInterface $product,
        ProductRepositoryInterface $productRepository,
        ProductVariantInterface $productVariant
    ) {
        $cartRepository->findOneBy(['tokenValue' => 'ORDERTOKEN'])->willReturn($cart);
        $productRepository->findOneBy(['code' => 'T_SHIRT_CODE'])->willReturn($product);
        $product->getVariants()->willReturn([$productVariant]);
        $product->isSimple()->willReturn(false);

        $this->shouldThrow(\InvalidArgumentException::class)->during('handle', [
            new PutSimpleItemToCart('ORDERTOKEN', 'T_SHIRT_CODE', 5),
        ]);
    }
}
