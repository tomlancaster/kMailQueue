<?php defined('SYSPATH') or die('No direct script access.');

/**
 * MailQueue Main Class.
 *
 * @package 	kMailQueue
 * @category  	Core
 * @author 		Alex Gisby <alex@solution10.com>
 */

class Kohana_MailQueue
{
	/**
	 * Adds an email to the Queue
	 *
	 * @param 	string|array 	Recipient. Either email, or array(email, name)
	 * @param 	string|array 	Sender. Either email or array(email, name)
	 * @param 	string			Subject
	 * @param 	string 			Body
	 * @param 	int 			Priority (1 is low, 1,000 is high etc)
	 * @return 	Model_MailQueue
	 */
	public static function add_to_queue($recipient, $sender, $subject, $body, $priority = 1)
	{
		return Model_MailQueue::add_to_queue($recipient, $sender, $subject, $body, $priority);
	}
	
	
	/**
	 * Send out a batch of emails. The number sent is dependant on config('mailqueue.batch_size')
	 *
	 * @return 	array 	The number of emails sent and failed.
	 */
	public static function batch_send()
	{
		$config = Kohana::$config->load('mailqueue');		
		$stats = array('sent' => 0, 'failed' => 0);
		
		$emails = Model_MailQueue::find_batch($config->batch_size);
		foreach($emails as $email)
		{
			$recipient = $email->recipient_email;
			if($email->recipient_name != null)
			{
				$recipient = $email->recipient_name . ' <' . $email->recipient_email . '>';
			}
			
			$sender = $email->sender_email;
			if($email->sender_name != null)
			{
				$sender = $email->sender_name . ' <' . $email->sender_email . '>';
			}
			
			$headers  = 'MIME-Version: 1.0' . "\r\n";
			$headers .= 'Content-type: text/html; charset=utf-8' . "\r\n";
			$headers .= 'From: ' . $sender . "\r\n";
			
			if(mail($recipient, $email->subject, $email->body->body, $headers))
			{
				$email->sent();
				$stats['sent'] ++;
			}
			else
			{
				$stats['failed'] ++;
				$email->failed();
			}
		}
		
		return $stats;
	}
	
	public static function batch_send_postmark()
	{
		$config = kohana::$config->load('mailqueue');
		$stats = array('sent' => 0, 'failed' => 0);
		$emails = Model_MailQueue::find_batch($config->batch_size);
		
		if ( ! class_exists('Kohana_Postmark'))
		 {
			require Kohana::find_file('classes', 'kohana/postmark');
		 }
		foreach($emails as $email)
		{
			$postmark = Kohana_Postmark::compose();
			$postmark->addTo($email->recipient_email, $email->recipient_name);
			$postmark->from($email->sender_email, $email->sender_name);
			$postmark->subject($email->subject);
			$postmark->messageHTML($email->body->body);
			
			
		  try {	
		  	if($postmark->send())
			  {
				  $email->sent();
				  $stats['sent'] ++;
			  }
			  else
			  {
			  	$stats['failed'] ++;
				  $email->failed();
        }
       } catch (Exception $e) 
       {
         $stats['failed'] ++;
         $email->failed();
       }
		 }
		
		return $stats;
	}
}
