<?php


    /**
     * 发送邮件方法
     * @param int $code 返回代码
     * @param string $data 返回数据
     */
    function json_data($code,$message,$data){
        $json_data = [
            'code'      =>  $code,
            'message'   =>  $message,
            'data'      =>  $data
        ];
        return $json_data;
    }


    /**
     * 发送邮件方法
     * @param string $objectmail 目标邮箱地址
     * @param string $title 标题
     * @param string $content 内容
     */
    function send_email($objectmail,$title,$content) {
        //Create a new PHPMailer instance
        $mail = new \PHPMailer\PHPMailer\PHPMailer();
        //Tell PHPMailer to use SMTP
        $mail->isSMTP();
        //Enable SMTP debugging
        // 0 = off (for production use)
        // 1 = client messages
        // 2 = client and server messages
        $mail->SMTPDebug = 0;
        //Set the hostname of the mail server
        $mail->Host = 'smtp.qq.com';
        //Set the SMTP port number - likely to be 25, 465 or 587
        $mail->Port = 465;
        //Whether to use SMTP authentication
        $mail->SMTPAuth = true;
        //Username to use for SMTP authentication
        $mail->Username = '312850391@qq.com';
        //Password to use for SMTP authentication
        $mail->Password = 'itjrogdgqwvpcabb';
        $mail->SMTPSecure = "ssl";// 使用ssl协议方式</span><span style="color:#333333;">
        //Set who the message is to be sent from
        $mail->setFrom('312850391@qq.com', 'SendFrom');
        //Set an alternative reply-to address
        $mail->addReplyTo('312850391@qq.com', 'ReplyTo');
        //Set who the message is to be sent to
        $mail->addAddress($objectmail);
        //Set the subject line
        $mail->Subject = $title;
        //Read an HTML message body from an external file, convert referenced images to embedded,
        //convert HTML into a basic plain-text alternative body
    //        $mail->msgHTML(file_get_contents('contents.html'), dirname(__FILE__));
        //Replace the plain text body with one created manually
        $mail->Body = $content;
        //Attach an image file
    //        $mail->addAttachment('images/phpmailer_mini.png');
        //send the message, check for errors
        if (!$mail->send()) {
//            echo 'Mailer Error: ' . $mail->ErrorInfo;
            return false;
        } else {
//            echo 'Message sent!';
            return true;
        }
    }



    /**
     * 图片缩放函数（可设置高度固定，宽度固定或者最大宽高，支持gif/jpg/png三种类型）
     * Author : Specs
     * Homepage: https://9iphp.com
     *
     * @param string $source_path 源图片
     * @param int $target_width 目标宽度
     * @param int $target_height 目标高度
     * @param string $fixed_orig 锁定宽高（可选参数 width、height或者空值）
     * @return string
     */
    function myImageResize($source_path, $target_width = 200, $target_height = 200, $fixed_orig = ''){
        $source_info = getimagesize($source_path);
        $source_width = $source_info[0];
        $source_height = $source_info[1];
        $source_mime = $source_info['mime'];
        $ratio_orig = $source_width / $source_height;
        if ($fixed_orig == 'width'){
            //宽度固定
            $target_height = $target_width / $ratio_orig;
        }elseif ($fixed_orig == 'height'){
            //高度固定
            $target_width = $target_height * $ratio_orig;
        }else{
            //最大宽或最大高
            if ($target_width / $target_height > $ratio_orig){
                $target_width = $target_height * $ratio_orig;
            }else{
                $target_height = $target_width / $ratio_orig;
            }
        }
        switch ($source_mime){
            case 'image/gif':
                $source_image = imagecreatefromgif($source_path);
                break;

            case 'image/jpeg':
                $source_image = imagecreatefromjpeg($source_path);
                break;

            case 'image/png':
                $source_image = imagecreatefrompng($source_path);
                break;

            default:
                return false;
                break;
        }
        $target_image = imagecreatetruecolor($target_width, $target_height);
        imagecopyresampled($target_image, $source_image, 0, 0, 0, 0, $target_width, $target_height, $source_width, $source_height);
        //header('Content-type: image/jpeg');
        $imgArr = explode('.', $source_path);
        $target_path = $imgArr[0] . '_new.' . $imgArr[1];
        imagejpeg($target_image, $target_path, 80);
    }


    /**
     * 压缩图片函数，目的压缩到2m以下
     * @param $source_path
     * @return bool
     */
    function compresspic($source_path){
        $source_info = getimagesize($source_path);
        $source_mime = $source_info['mime'];

        switch ($source_mime){
            case 'image/gif':
                $source_image = imagecreatefromgif($source_path);
                break;

            case 'image/jpeg':
                $source_image = imagecreatefromjpeg($source_path);
                break;

            case 'image/png':
                $source_image = imagecreatefrompng($source_path);
                break;

            default:
                return false;
                break;
        }

        $imgArr = explode('.', $source_path);
        $target_path = $imgArr[0] . '_new.' . $imgArr[1];
        imagejpeg($source_image, $target_path, 80);
    }
