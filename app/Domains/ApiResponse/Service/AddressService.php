<?php

namespace App\Domains\ApiResponse\Service;

use App\Domains\Auth\Models\User;
use App\Domains\Cart\Models\Address;
use App\Domains\Cart\Models\CustomerCart;
use Illuminate\Http\Request;

/**
 * Class AddressService.
 */
class AddressService
{

    public function list()
    {
        $auth_id = auth('sanctum')->id();
        return Address::query()
            ->where('user_id', $auth_id)
            ->get();
    }


    public function updateUserInfoIfNeeded($address, $user_id)
    {
        try {
            $user = User::query()->find($user_id);
            if ($user) {
                $name = $address->name ?? '';
                $phone = $address->phone ?? '';
                $save = false;
                if (!$user->name && $name) {
                    $user->name = $name;
                    $save = true;
                }
                if (!$user->phone && $phone) {
                    $user->phone = $address->phone ?? '';
                    $save = true;
                }
                if ($save) {
                    $user->save();
                }
            }
        } catch (\Throwable $th) {
            //throw $th;
        }
    }

    public function updateAddressToCart($cart, $address)
    {
        if (!$address && !$cart) {
            return false;
        }
        $newAddress = [
            'id' => $address->id,
//            'uuid' => $address->uuid,
            'name' => $address->name,
            'phone' => $address->phone,
            'zip_code' => $address->zip_code,
            'city' => $address->city,
            'address' => $address->address,
        ];

        if ($address->company) {
            $newAddress['company'] = $address->company;
        }
        if ($address->tax_id) {
            $newAddress['tax_id'] = $address->tax_id;
        }
        if ($address->state) {
            $newAddress['tax_id'] = $address->state;
        }
        if ($cart) {
            $cart->shipping_address = json_encode($newAddress);
            $cart->billing_address = json_encode($newAddress);
            $cart->save();
            $cart->refresh();
        }

        return $cart;
    }

    public function store()
    {
        $address_uuid = request('uuid');
        $auth_id = auth('sanctum')->id();

        $address = Address::query()
            ->where('user_id', $auth_id)
            ->where('uuid', $address_uuid)
            ->first();
        $address = $address ?: new Address();
        $address->name = request('name');
        $address->company = request('company');
        $address->phone = request('phone');
        $address->tax_id = request('tax_id');
        $address->zip_code = request('zip_code');
        $address->city = request('city');
        $address->state = request('state');
        $address->country = request('country');
        $address->address = request('address');
        $address->user_id = $auth_id;
        $address->save();
        $address->refresh();

        $this->updateUserInfoIfNeeded($address, $auth_id);


        $token = request()->header('Cart-Token');
        $cart = CustomerCart::query()
            ->where('uuid', $token)
            ->where('user_id', $auth_id)
            ->first();

        $this->updateAddressToCart($cart, $address);

        return $this->list();
    }


    public function delete()
    {
        $address_uuid = request('uuid');
        $auth_id = auth('sanctum')->id();

        $token = request()->header('Cart-Token');
        $cart = CustomerCart::query()
            ->where('uuid', $token)
            ->where('user_id', $auth_id)
            ->first();

        $shipping = $cart ? $cart->shipping_address : null;
        $shipping = $shipping ? json_decode($shipping, true) : null;
        $cart_address_uuid = $shipping['uuid'] ?? '';

        Address::query()
            ->where('user_id', $auth_id)
            ->where('uuid', $address_uuid)
            ->delete();

        if ($cart_address_uuid === $address_uuid) {
            $last_address = Address::query()
                ->where('user_id', $auth_id)
                ->orderByDesc('id')
                ->first();
            $this->updateAddressToCart($last_address, $auth_id);
        }

        return $this->list();
    }
}
