 <?php
$this->load->library('email');
$config['protocol'] = 'smtp';

$config['smtp_user'] = 'marcus@junstrom.se';
$config['smtp_pass'] = 'clivia14';
$config['protocol']    = 'smtp';
        $config['smtp_host']    = 'ssl://smtp.gmail.com';
        $config['smtp_port']    = '465';
        $config['smtp_timeout'] = '7';

        $config['charset']    = 'utf-8';
        $config['newline']    = "\r\n";
        $config['mailtype'] = 'text'; // or html
        $config['validation'] = TRUE; // bool whether to validate email or not

//$config['charset'] = 'iso-8859-1';
$config['wordwrap'] = TRUE;

$this->email->initialize($config);



$this->email->from('marcus@junstrom.se', 'Your Name');
$this->email->to('marcus@junstrom.se'); 



$this->email->subject('Email Test');
$this->email->message('Testing the email class.');	

$this->email->send();

echo $this->email->print_debugger();


?>