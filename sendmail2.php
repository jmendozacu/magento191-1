<?php

/*
  Created on : Nov 10, 2016, 3:21:57 PM
  Author     : Tran Trong Thang
  Email      : trantrongthang1207@gmail.com
  Skype      : trantrongthang1207
 */

ini_set('memory_limit', '3072M');
set_time_limit(0);
require 'app/Mage.php';
$app = Mage::app();
umask(0);
echo 'function sendmail <br>';
function sendMail2Action() {
    $html = "Test send mail";
    $mail = Mage::getModel('core/email');
    $mail->setToName('Magenot');
    $mail->setToEmail('thang.fgc1207@gmail.com');
    $mail->setBody($html);
    $mail->setSubject('Test sen mail');
    $mail->setType('html'); // YOu can use Html or text as Mail format

    try {
        $mail->send();
        echo "true";
    } catch (Exception $e) {
        echo "false";
    }
}

sendMail2Action();
