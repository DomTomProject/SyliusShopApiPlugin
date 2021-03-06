<?php

namespace Sylius\ShopApiPlugin\Request;

use Sylius\ShopApiPlugin\Command\PutOptionBasedConfigurableItemToCart;
use Symfony\Component\HttpFoundation\Request;

final class PutOptionBasedConfigurableItemToCartRequest
{
    /**
     * @var string
     */
    private $token;

    /**
     * @var string
     */
    private $productCode;

    /**
     * @var array|null
     */
    private $options;

    /**
     * @var int
     */
    private $quantity;

    /**
     * @param Request $request
     */
    public function __construct(Request $request)
    {
        $this->token = $request->attributes->get('token');
        $this->productCode = $request->request->get('productCode');
        $this->options = $request->request->get('options');
        $this->quantity = $request->request->getInt('quantity');
    }

    /**
     * @return PutOptionBasedConfigurableItemToCart
     */
    public function getCommand()
    {
        return new PutOptionBasedConfigurableItemToCart($this->token, $this->productCode, $this->options, $this->quantity);
    }
}
