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

function sendTemplateEmail($recepientEmail, $recepientName, $subject, $showmsg = false) {

    if ($recepientEmail == '') {
        return;
    }

    /*
     *  Set sender information			
     */
    $senderName = 'Nham Phap';
    $senderEmail = 'thang.fgc1207@gmail.com';
    $sender = array('name' => $senderName,
        'email' => $senderEmail);
    /*
     * get Store Id
     */
    $storeId = Mage::app()->getStore()->getId();
    /*
     * get inro customer
     */
    if (Mage::getSingleton('customer/session')->isLoggedIn()) {
        $customer = Mage::getSingleton('customer/session')->getCustomer();
        $customer_id = $customer->getId();
    } else {
        $customer_id = 0;
    }

    $customerData = Mage::getModel('customer/customer')->load($customer_id);


    /*
     * Set variables that can be used in email template
     * Khoi tao cac bien de su hien thi trong file html template mail
     * De su dung ta chi can goi {{username}}
     */
    $emailTemplateVariables = array();
    $emailTemplateVariables['username'] = $customerData->getName();
    $emailTemplateVariables['email'] = $customerData->getEmail();
    $emailTemplateVariables['password'] = $customerData->getPasswordHash();
    $emailTemplateVariables['store_email'] = Mage::getStoreConfig('trans_email/ident_general/email');
    $emailTemplateVariables['store_phone'] = Mage::getStoreConfig('general/store_information/phone');
    $emailTemplateVariables['phone'] = '123456789';


    /*
     * Loads the html file named 'contact_form.html' from
     * app/locale/en_US/template/email/contact_form.html
     */
    $email = Mage::getModel('core/email_template')
            ->loadDefault('tv_articles_email_template');

    $processedTemplate = $email->getProcessedTemplate($emailTemplateVariables);

    $email->setReplyTo($senderEmail);
    $email->setSenderName($senderName);
    $email->setSenderEmail($senderEmail);
    $email->setTemplateSubject($subject);

    try {
        $result = $email->send(
                $recepientEmail, $recepientName, array(
            'message' => $processedTemplate
                )
        );
        if ($result) {
            $msg = "Email has sent to $receiver_email<br/>";
        } else {
            $msg = "Email hasn't sent to $receiver_email<br/>";
        }
        /*
         * echo $msg;
         * return $msg;
         * file_put_contents(Mage::getBaseDir('media').'/test.txt', $msg);
         */
    } catch (Exception $error) {
        $msg = "<b>" . $error->getMessage() . "<b><br/>";
        /* /
         * echo $msg;
         * file_put_contents(Mage::getBaseDir('media').'/test.txt', $msg);
         * return $msg;
         * 
         */
    }
    if ($showmsg) {
        echo $msg;
    }
}

function sendTransactionalEmail($recepientEmail, $recepientName, $templateId) {
    if ($recepientEmail == '') {
        return;
    }
    /*
     *  Set sender information			
     */
    $senderName = 'Nham Phap';
    $senderEmail = 'thang.fgc1207@gmail.com';
    $sender = array('name' => $senderName,
        'email' => $senderEmail);
    /*
     *  Get Store ID		
     */
    $storeId = Mage::app()->getStore()->getId();

    /*
     * Set variables that can be used in email template
     * Khoi tao cac bien de su hien thi trong transactional mail
     * De su dung ta chi can goi {{username}}
     */
    $vars = array(
        'username' => $senderName,
        'email' => $recepientName,
        'password' => 'fgc123456',
        'store_email' => 'trantrongthang1207@gmail.com',
        'store_phone' => '123456789',
        'phone' => '123456789',
    );

    $translate = Mage::getSingleton('core/translate');

    /*
     *  Send Transactional Email
     */
    Mage::getModel('core/email_template')
            ->sendTransactional($templateId, $sender, $recepientEmail, $recepientName, $vars, $storeId);

    $translate->setTranslateInline(true);
}

/*
  sendTemplateEmail('thang.fgc1207@gmail.com', 'Nham Phap', "Thu go, duoc gui toi tu site 'Mua Ban Tai Nang'");
  sendTransactionalEmail('thang.fgc1207@gmail.com', 'Nham Phap', 1);
 */
$to = "thang.fgc1207@gmail.com";
$subject = "This is subject";

$message = "<b>This is HTML message.</b>";
$message .= "<h1>This is headline.</h1>";

$header = "From:thang.fgc1207@gmail.com \r\n";
$header .= "Cc:thang.fgc1207@gmail.com \r\n";
$header .= "MIME-Version: 1.0\r\n";
$header .= "Content-type: text/html\r\n";

$retval = mail($to, $subject, $message, $header);

if ($retval == true) {
    echo "Message sent successfully...";
} else {
    echo "Message could not be sent...";
}

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
        Mage::getSingleton('core/session')->addSuccess('Your request has been sent');
    } catch (Exception $e) {
        Mage::getSingleton('core/session')->addError('Unable to send.');
    }
}

sendMail2Action();
