<?php namespace App\Mail;

use App\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

/**
 * Class VerifyMail
 * @package App\Mail
 */
class VerifyMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    /**
     * @var $user
     */
    public $user;

    /**
     * VerifyMail constructor.
     *
     * @param User $user
     */
    public function __construct(User $user)
    {
        $this->onQueue('emails');
        $this->user = $user;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->view('emails.verifyUser', ['user' => $this->user]);
    }
}
