<?php

namespace App\Domains\Products\Traits;

use Illuminate\Support\Facades\Session;

trait SessionCart
{

  public function getCart()
  {
    return Session::get('customerCart', []);
  }

  public function updateCart($cart)
  {
    Session::put('customerCart', $cart);
    return $this->getCart();
  }

  public function clearCart()
  {
    Session::remove('customerCart');
    return $this->getCart();
  }
}
