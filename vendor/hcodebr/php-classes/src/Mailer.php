<?php

namespace Hcode;

use Rain\Tpl;

class Mailer {

    const USERNAME = "leonardocsanova@gmail.com";
    const PASSWORD = "Arn0ld77";
    const NAMEFROM = "Hcode Store";

    private $mail;

    public function __construct($toAddress, $toName, $subject, $tplName, $data = array()) {

        $config = array(
            "tpl_dir" =>$_SERVER["DOCUMENT_ROOT"]."/views/email/",
            "cache_dir" => $_SERVER["DOCUMENT_ROOT"] ."/views-cache/",
            "debug" => false, // set to false to improve the speed
        );

        Tpl::configure($config);

        $this->tpl = new Tpl();

        foreach ($data as $key => $value) {
            $this->tpl->assign($key, $value);
        }

        $html = $this->tpl->draw($tplName, true);
        $this->mail = new \PHPMailer;

        //Tell PHPMailer to use SMTP
        $this->mail->isSMTP();
        $this->mail->SMTPOptions = array(
            'ssl' => array(
                'verify_peer' => false,
                'verify_peer_name' => false,
                'allow_self_signed' => true,
            ),
        );

        //Enable SMTP debugging
        // 0 = off (for production use)
        // 1 = client messages
        // 2 = client and server messages
        $this->mail->SMTPDebug = 0;

        //Ask for HTML-friendly debug outpu
        $this->mail->Debugoutput = 'html';

        //Set the hostname of the mail server
        $this->mail->Host = 'smtp.gmail.com';

        // use
        // $this->mail->Host = gethostbyname('smtp.gmail.com');
        // if your network does not support SMTP over IPv6
        //Set the SMTP port number - 587 for authenticated TLS, a.k.a. RFC4409 SMTP submission
        $this->mail->Port = 587;

        //Set the encryption system to use - ssl (deprecated) or tls
        $this->mail->SMTPSecure = 'tls';

        //Whether to use SMTP authentication
        $this->mail->SMTPAuth = true;

        // Nome de usuário para usar na autenticação SMTP - use o endereço de e-mail completo para o gmail
        $this->mail->Username = Mailer::USERNAME;

        // Senha para usar para autenticação SMTP
        $this->mail->Password = Mailer::PASSWORD;

        // Defina para quem a mensagem deve ser enviada
        $this->mail->setFrom(Mailer::USERNAME, Mailer::NAMEFROM);

        //Set an alternative reply-to address
        //$this->mail->addReplyTo('replyto@example.com', 'First Last');

        // Definir para quem a mensagem deve ser enviada
        $this->mail->addAddress($toAddress, $toName);

        // Definir a linha de assunto
        $this->mail->Subject = $subject;

        // Leia um corpo de mensagem HTML de um arquivo externo, converta imagens referenciadas em
        // converte HTML em um corpo alternativo de texto simples básico
        $this->mail->msgHTML($html);

        //Replace the plain text body with one created manually
        $this->mail->AltBody = 'Inscrição feita, para concluir efetetue o pagamento ';

        //Attach an image file
        //$this->mail->addAttachment('images/phpmailer_mini.png');

        //send the message, check for errors
        //Section 2: IMAP
        //IMAP commands requires the PHP IMAP Extension, found at: https://php.net/manual/en/imap.setup.php
        //Function to call which uses the PHP imap_*() functions to save messages: https://php.net/manual/en/book.imap.php
        //You can use imap_getmailboxes($imapStream, '/imap/ssl') to get a list of available folders or labels, this can
        //be useful if you are trying to get this working on a non-Gmail IMAP server.
        /*function save_mail($mail)
    {
    //You can change 'Sent Mail' to any other folder or tag
    $path = "{imap.gmail.com:993/imap/ssl}[Gmail]/Sent Mail";
    //Tell your server to open an IMAP connection using the same username and password as you used for SMTP
    $imapStream = imap_open($path, $mail->Username, $mail->Password);
    $result = imap_append($imapStream, $path, $mail->getSentMIMEMessage());
    imap_close($imapStream);
    return $result;
    }*/
    }
    public function send() {

        return $this->mail->send();
    }
}
