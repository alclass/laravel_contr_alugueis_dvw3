#!/usr/bin/env python3
'''

This is an Example Script to send email via the 3rd-party service called MailGun
This Python script is below this docstring. Here the example is complemented,
 for learning purposes, also in curl and php

via curl
========
curl -s --user 'api:<key-hidden-in-the-dotenv-file>' \
    https://api.mailgun.net/v3/sandbox7191337403324e4dbd5303cdc5282418.mailgun.org/messages \
        -F from='Mailgun Sandbox <postmaster@sandbox7191337403324e4dbd5303cdc5282418.mailgun.org>' \
        -F to='Luiz <luizplus@yahoo.com.br>' \
        -F subject='Hello Luiz' \
        -F text='Congratulations Luiz, you just sent an email with Mailgun!  You are truly awesome!'


via php
=======
# Include the Autoloader (see "Libraries" for install instructions)
require 'vendor/autoload.php';
use Mailgun\Mailgun;

# Instantiate the client.
$mailgun_api_key = '<key-hidden-in-the-dotenv-file>'
$mgClient = new Mailgun($mailgun_api_key);
$domain = "sandbox7191337403324e4dbd5303cdc5282418.mailgun.org";

# Make the call to the client.
$result = $mgClient->sendMessage("$domain",
          array('from'    => 'Mailgun Sandbox <postmaster@sandbox7191337403324e4dbd5303cdc5282418.mailgun.org>',
                'to'      => 'Luiz <luizplus@yahoo.com.br>',
                'subject' => 'Hello Luiz',
                'text'    => 'Congratulations Luiz, you just sent an email with Mailgun!  You are truly awesome! '));

# You can see a record of this email in your logs: https://mailgun.com/app/logs .

# You can send up to 300 emails/day from this sandbox server.
# Next, you should add your own domain so you can send 10,000 emails/month for free.
'''

import requests
import dotenv

class MailGunExampleSender:

  def __init__(self):

    dotenv.load()
    self.mailgun_api_key = dotenv.get('MAILGUN_API_KEY')
    if self.mailgun_api_key is None:
      raise Exception("mailgun_key is None, it's probably caused by missing .env config file on folder.")
    self.init_email_fields()

  def init_email_fields(self):

    self.from_field = "Mailgun Sandbox <postmaster@sandbox7191337403324e4dbd5303cdc5282418.mailgun.org>"
    self.to_field   = "Luiz <luizplus@yahoo.com.br>"
    self.subject    = "Hello Luiz"
    self.email_text = "Congratulations Luiz, you just sent an email with Mailgun!  You are truly awesome!"
    self.url        = "https://api.mailgun.net/v3/sandbox7191337403324e4dbd5303cdc5282418.mailgun.org/messages"

  def send_simple_message(self):
    '''
    You can see a record of this email in your logs: https://mailgun.com/app/logs .

    You can send up to 300 emails/day from this sandbox server.
    Next, you should add your own domain so you can send 10,000 emails/month for free.
    
    :return: 
    '''
    return requests.post(
      self.url,
      auth = ("api", self.mailgun_api_key),
      data = {
        "from"    : self.from_field,
        "to"      : self.to_field,
        "subject" : self.subject,
        "text"    : self.email_text,
      }
    )

  def email_show_confirm_send(self):
    print ('='*40)
    print ('Example MailGun message')
    print ('='*40)
    print ('From   : ', self.from_field)
    print ('To     : ', self.to_field)
    print ('Subject: ', self.subject)
    print ('='*40)
    print ('Email Text is:')
    print (self.email_text)
    print ('='*40)
    answer = input('Do you really want to send the example MailGun message? (y/n) ')
    if answer in ['y', 'Y']:
      print('send_simple_message()')
      returned_value = self.send_simple_message()
      print ('=' * 40)
      print('returned_value = ', returned_value)

if __name__ == '__main__':
  sender = MailGunExampleSender()
  sender.email_show_confirm_send()
