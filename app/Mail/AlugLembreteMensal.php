<?php
namespace App\Mail;
// use App\Mail\AlugLembreteMensal;


use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class AlugLembreteMensal extends Mailable {
  use Queueable, SerializesModels;

  // Not sure if Parent class has an attribute $from_email
  // So variable name got prefixed by 'instances_'
  public $instances_from_email = null;

  /**
   * Create a new message instance.
   *
   * @return void
   */
  public function __construct() {
    // __construct()
  }

  public function set_domain_based_from_email_field($request, $before_at_sign=null) {
    $email_prefix = 'admin';
    $hostname = $request->getHost();
    if ($before_at_sign != null) {
      $email_prefix = $before_at_sign;
    }
    $this->instances_from_email = "$email_prefix@$hostname";
  } // ends set_domain_based_from_email_field()

  /**
   * Build the message.
   *
   * @return $this
   */
  public function build() {

    if ($this->instances_from_email == null) {
      // last try
      $hostname = env('APP_HOSTNAME');
      $this->instances_from_email = 'admin@' . $hostname;
    }

    return $this->from($this->instances_from_email) // email@domain.tld
      ->view('emails.emailtempl_AlugLembreteMensal');

  } // ends build()

} // class AlugLembreteMensal extends Mailable
