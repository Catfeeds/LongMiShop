<?php

function sendMail($to, $title, $content) {
    Vendor('phpmailer.PHPMailerAutoload');
    $mail = new PHPMailer(); //实例化
    $config = tpCache('smtp');
    $mail->IsSMTP(); // 启用SMTP
    $mail->Host = $config['smtp_server']; //smtp服务器的名称（这里以QQ邮箱为例）
    $mail->SMTPAuth = $config['smtp_port']; //启用smtp认证
    $mail->Username = $config['smtp_user']; //你的邮箱名
    $mail->Password = $config['smtp_pwd']; //邮箱密码
    $mail->From = $config['smtp_user']; //发件人地址（也就是你的邮箱地址）
    $mail->FromName = "龙米"; //发件人姓名
    $mail->AddAddress($to,"尊敬的客户");
    $mail->WordWrap = 50; //设置每行字符长度
    $mail->IsHTML(C('MAIL_ISHTML')); // 是否HTML格式邮件
    $mail->CharSet=C('MAIL_CHARSET'); //设置邮件编码
    $mail->Subject =$title; //邮件主题
    $mail->Body = $content; //邮件内容
    $mail->AltBody =$title; //邮件正文不支持HTML的备用显示
    return($mail->Send());
}