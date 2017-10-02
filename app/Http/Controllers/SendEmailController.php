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
			$this->sendemail($request);
			//-------------------
			$email_sent_msg = 'an email has just been sent';
		}
		return view(
			'emails.sendemail_form', [
				'email_sent_msg' => $email_sent_msg,
			]);
	} // ends sendemail_via_httppost()

	public function sendemail($request) {
		//$lembrete_email = 1;
		$email_obj = new AlugLembreteMensal();
		$email_obj->set_domain_based_from_email_field($request);
		$user = User::where('email', 'luizplus@yahoo.com.br')->first();
		Mail::to($user)->send($email_obj);
	} // ends sendemail()

} // ends class dashboardController extends Controller
