<?php
namespace App\Http\Controllers;

use App\Http\Requests;
use App\User;
use App\Http\Controllers\Controller;
// use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

use App\Mail\AlugLembreteMensal;

class SendEmailController extends Controller {

	/**
	 * Display a listing of the resource.
	 *
	 * @return Response
	 */
	public function sendemail_via_httpget() {

		$email_sent_msg = 'an email is yet to be sent';
		return view('emails.sendemail_form', [
			'email_sent_msg' => $email_sent_msg,
		]);

	} // ends sendemail_via_httpget()

	public function sendemail_via_httppost(Request $request) {

		$email_sent_msg = 'an email is yet to be sent';
		$do_send_checkbox = $request->input('do_send_checkbox');
		if ($do_send_checkbox == '1') {
			//-------------------
			$this->sendemail();
			//-------------------
			$email_sent_msg = 'an email has just been sent';
		}
		return view(
			'emails.sendemail_form', [
				'email_sent_msg' => $email_sent_msg,
			]);
	} // ends sendemail_via_httppost()

	public function sendemail() {
		//$lembrete_email = 1;
		$email_obj = new AlugLembreteMensal();
		$user = User::where('email', 'luizplus@yahoo.com.br')->first();
		Mail::to($user)->send($email_obj);
	} // ends sendemail()

} // ends class UserDashboardController extends Controller
