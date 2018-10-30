<?php
class Ugc_Mailer {

	private $FROM = "ugc@mail.co";
	private $SUBJECT = "";
	private $TO = "";
	private $BODY = "";

	function __construct($options = array()) {
		if(isset($options['from'])) {
			$this->FROM = $options['from'];
		}
		if(isset($options['subject'])) {
			$this->SUBJECT = $options['subject'];
		}
		if(isset($options['to'])) {
			$this->TO = $options['to'];
		}
		if(isset($options['body'])) {
			$this->BODY = $options['body'];
		}
	}

	public function set_from($from = "") {
		$this->FROM = $from;
	}

	public function set_subject($subject) {
		$this->SUBJECT = $subject;
	}

	public function set_to($email) {
		$this->TO = $email;
	}

	public function set_template($template_path = "") {
		if(!empty($template_path)) {
			$this->BODY = file_get_contents($template_path);
		} 
	}

	public function set_content_pair($pairs = array()) {
		foreach ($pairs as $key => $value) {
			$this->BODY = str_replace("*|".$key."|*", $value, $this->BODY);
		}
	}

	public function get_header() {
		$headers = "From: " . strip_tags($this->FROM) . "\r\n";
		$headers .= "MIME-Version: 1.0\r\n";
		$headers .= "Content-Type: text/html; charset=ISO-8859-1\r\n";
		return $headers;
	}

	public function send() {
		$meta = array(
					'*|SUBJECT|*' => $this->SUBJECT,
					'*|HEADERIMAGE|*' => plugins_url('ugc/images/header_email.png')
				);
		foreach ($meta as $key => $value) {
			$this->BODY = str_replace($key, $value, $this->BODY);
		}

		return mail($this->TO, $this->SUBJECT, $this->BODY, $this->get_header());
	}

}