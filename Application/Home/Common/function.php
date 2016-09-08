<?php
	function deldir($dir) {
        //先删除目录下的文件：
        $dh=opendir($dir);
        while ($file=readdir($dh)) {
            if($file!="." && $file!="..") {
                $fullpath=$dir."/".$file;
                if(!is_dir($fullpath)) {
                    unlink($fullpath);
                } else {
                    deldir($fullpath);
                }
            }
        }

        closedir($dh);
        //删除当前文件夹：
        if(rmdir($dir)) {
            return true;
        } else {
            return false;
        }
    }

    function cmp($a, $b) {
        if ($a['price'] == $b['price']) {
            return 0;
        }
        return ($a['price'] > $b['price']) ? -1 : 1;
    }

    function cmp_bid_count($a, $b) {
        if ($a['bid_count'] == $b['bid_count']) {
            return 0;
        }
        return ($a['bid_count'] > $b['bid_count']) ? -1 : 1;
    }

    // 发送邮件
    function sendMail($to, $title, $content) {
        Vendor('PHPMailer.PHPMailerAutoload');     
        $mail = new PHPMailer(); //实例化
        $mail->IsSMTP(); // 启用SMTP
        $mail->Host = C('MAIL_HOST'); //smtp服务器的名称（这里以QQ邮箱为例）
        $mail->SMTPAuth = C('MAIL_SMTPAUTH'); //启用smtp认证
        $mail->Username = C('MAIL_USERNAME'); //你的邮箱名
        $mail->Password = C('MAIL_PASSWORD') ; //邮箱密码
        $mail->From = C('MAIL_FROM'); //发件人地址（也就是你的邮箱地址）
        $mail->FromName = C('MAIL_FROMNAME'); //发件人姓名
        $mail->AddAddress($to, $to);
        $mail->WordWrap = 50; //设置每行字符长度
        $mail->IsHTML(C('MAIL_ISHTML')); // 是否HTML格式邮件
        $mail->CharSet = C('MAIL_CHARSET'); //设置邮件编码
        $mail->Subject = $title; //邮件主题
        $mail->Body = $content; //邮件内容
        // $mail->AltBody = "这是一个纯文本的身体在非营利的HTML电子邮件客户端"; //邮件正文不支持HTML的备用显示
        return ($mail->Send());
    }
    // 生成随机字符串
    function genRandStr() {
        $str = '';
        for ($i = 0; $i < 20; $i++) { 
            $str .= rand(0, 9);
        }
        return substr(md5($str), 0, 8);
    }
?>