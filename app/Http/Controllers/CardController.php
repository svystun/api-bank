<?php namespace App\Http\Controllers;

use Illuminate\Http\Request;

/**
 * Class StripeController
 * @package App\Http\Controllers
 */
class CardController extends Controller
{

    public function create(Request $request)
    {
        $token = $stripe->tokens()->create([
            'card' => [
                'number'    => '4242424242424242',
                'exp_month' => 10,
                'cvc'       => 314,
                'exp_year'  => 2020
            ]
        ]);
        $card = $stripe->cards()->create($customer['id'], $token['id']);

        Log::info('CARD: ' . var_export($card, true));
    }

    public function charge()
    {
        //
    }


}