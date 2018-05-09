<?php namespace App\Jobs;

use App\User;
use Cartalyst\Stripe\Stripe;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

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
     * @param Stripe $stripe
     * @return void
     */
    public function handle(Stripe $stripe)
    {
        $customer = $stripe->customers()->create([
            'email' => $this->user->email
        ]);

        // Update stripe_id
        User::find($this->user->id)->update(['stripe_id' => $customer['id']]);
    }
}
