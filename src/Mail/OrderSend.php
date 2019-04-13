<?php

namespace Imediasun\Widgets\Mail;

//use App\Mail\Mailable as Mailable;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Contracts\Mail\Mailer as MailerContract;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class OrderSend extends Mailable
{
    use Queueable, SerializesModels;
    /**
     * The order instance.
     *
     * @var Order
     */
    protected $order;
    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($mail)
    {

        $this->sub=$mail['sub'];
        $this->recipient=$mail['recipient'];
        $this->sender=$mail['sender'];
        $this->template=$mail['template'];
        $this->exception=$mail['exception'];
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
            return $this->from($this->sender)->to($this->recipient)->subject($this->sub)->view($this->template,
                [
                    'exception'=>(isset($this->exception)) ? $this->exception : null,
                ]
                );
    }


}
