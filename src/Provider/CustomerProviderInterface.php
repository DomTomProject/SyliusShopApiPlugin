<?php

declare(strict_types = 1);

namespace Sylius\ShopApiPlugin\Provider;

use Sylius\Component\Core\Model\CustomerInterface;

interface CustomerProviderInterface
{
    public function provide(string $email): CustomerInterface;
}
