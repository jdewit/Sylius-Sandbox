<?php

namespace Application\Bundle\SalesBundle\Processor\Operation;

use Symfony\Component\DependencyInjection\ContainerAware;
use Sylius\Bundle\SalesBundle\Model\OrderInterface;
use Sylius\Bundle\SalesBundle\Processor\Operation\OperationInterface;

class ProcessOperation extends ContainerAware implements OperationInterface
{
    public function prepare(OrderInterface $order)
    {
        $cart = $this->container->get('sylius_cart.provider')->getCart();
        
        if ($cart->isEmpty()) {
            throw new \LogicException('The cart must be not empty.');
        }
    }
    
    public function process(OrderInterface $order)
    {      
        $cart = $this->container->get('sylius_cart.provider')->getCart();
        $cart->setLocked(true);
        
        // Set order value.
        $order->setCart($cart);
        
        $orderValue = 0.00;
        
        foreach ($cart->getItems() as $item) {
            $orderValue += $item->getProduct()->getPrice() * $item->getQuantity();
        }
        
        $order->setValue($orderValue);
        
        // Clear cart.
        $cart = $this->container->get('sylius_cart.manager.cart')->createCart();
        $this->container->get('sylius_cart.provider')->setCart($cart);
        $this->container->get('request')->getSession()->set('sylius_cart.id', false);
    }
}
