<?php namespace App\Jobs;

use App\User;
use Cartalyst\Stripe\Stripe;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\Log;

/**
 * Class NewCustomer
 * @package App\Jobs
 */
class NewCustomer implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * @var $user
     */
    protected $user;

    /**
     * NewCustomer constructor.
     *
     * @param User $user
     */
    public function __construct(User $user)
    {
        $this->onQueue('stripe');
        $this->user = $user;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $stripe = Stripe::make(env('STRIPE_SECRET'));
        $customer = $stripe->customers()->create([
            'email' => $this->user->email
        ]);

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
}
