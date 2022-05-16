<?php

/*
    指引：
    1、92行的https://filebroker.alibaba.com/x/upload是图床接口
    2、97行是$data是图床接口所需的参数
    3、109行$headers[]是接口所需的cookie
    4、115-119行是$uploadimg返回的数据格式进行解释，这里是返回了JSON
    5、如果抓到了其他平台的接口，可以在本程序进行修改，即可用到其他接口上
    6、阿里图床抓取来源：https://rfq.alibaba.com/rfq/profession.htm?tracelog=rfq-market-industry-card
    创建者：里客云科技 - TANKING（2022-05-16）
    博客：https://segmentfault.com/u/tanking
    Github：https://github.com/likeyun/
 */

// 返回JSON
header("Content-type:application/json");
 
// 获取文件
$file = $_FILES["file"]["name"];
 
// 允许上传的图片后缀
$allowedExts = array("gif", "jpeg", "jpg", "png", "GIF", "JPEG", "JPG", "PNG");

// 获取文件信息（文件后缀名）
$fileinfo = pathinfo($file);
$extension = $fileinfo['extension'];

// 重命名
$newfilename = date('Ymd').time().'.'.$extension;

// 判断文件类型
if ((($_FILES["file"]["type"] == "image/gif")
|| ($_FILES["file"]["type"] == "image/jpeg")
|| ($_FILES["file"]["type"] == "image/jpg")
|| ($_FILES["file"]["type"] == "image/pjpeg")
|| ($_FILES["file"]["type"] == "image/x-png")
|| ($_FILES["file"]["type"] == "image/png"))
&& ($_FILES["file"]["size"] < 2048000)
&& in_array($extension, $allowedExts)){

    // 如果发生错误
    if ($_FILES["file"]["error"] > 0){

        // 上传失败
        $result = array(
            "code" => 202,
            "msg" => "上传失败"
        );
    }else{

        // 判断是否有临时文件夹
        $upfile = iconv("UTF-8", "GBK", "upload/");
        if (!file_exists($upfile)){

            // 创建文件夹
            mkdir ($upfile, 0777, true);
            // 将临时文件移动到文件夹
            move_uploaded_file($_FILES["file"]["tmp_name"], "upload/".$newfilename);

            // 完整的上传路径
            $realpath = realpath(dirname(__FILE__))."/upload/".$newfilename;

            // 执行上传函数上传到服务器
            $url = UploadImg($realpath);
        }else{

            // 将临时文件移动到文件夹
            move_uploaded_file($_FILES["file"]["tmp_name"], "upload/".$newfilename);

            // 完整的上传路径
            $realpath = realpath(dirname(__FILE__))."/upload/".$newfilename;

            // 执行上传函数上传到服务器
            $url = UploadImg($realpath);
        }

        // 返回上传结果
        if (empty($url) || $url == '') {

            $result = array(
                "code" => 203,
                "msg" => "上传失败，可能是cookie过期了"
            );
        }else{

            $result = array(
                "code" => 200,
                "msg" => "上传成功",
                "path" => $url
            );
        }
        
    }
}else{

    // 如果文件后缀或文件类型不符合
    $result = array(
        "code" => 201,
        "msg" => "格式不符合规则"
    );
}

// 上传到服务器
function UploadImg($realpath){

    // 图床服务器
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, 'https://filebroker.alibaba.com/x/upload');
    curl_setopt($ch, CURLOPT_POST, true);

    // 上传参数
    $data = array(
        'file' => new CURLFile($realpath),
        'bizCode' => 'icbu_rfq'
    );
    curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    // 请求头（user-agent）
    $headers[] = "user-agent:Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/69.0.3947.100 Safari/537.36";
    // 请求头（cookie）
    $headers[] = "cookie:t=adfa749834579b0251b5138de40f6320; ali_apache_id=11.183.37.33.164854035737.429079.1; cookie2=1cee63cf01ec4230399088100f58dc64; _tb_token_=7e3be338ae537; cna=NYKKGusdmDUCATspoBnVd4Sf; xlly_s=1; _m_h5_tk=4a8e7d87e12451f1b198d3185f34a09f_1652694048781; _m_h5_tk_enc=9cf6e96916c99220bf0d69db93f1eca7; _samesite_flag_=true; _hvn_login=4; intl_locale=zh_CN; xman_i=aid=724156739; ali_apache_tracktmp=W_signed=Y; _ga=GA1.2.1441021116.1652686615; _gid=GA1.2.568122399.1652686615; _bl_uid=hglw035w8ehfwg1vRzas4ktx5Ia8; xman_us_f=x_locale=zh_CN&x_l=1&last_popup_time=1652686592452&x_user=CN|TANKING|GUO|cnfm|240487683&no_popup_today=n&acs_rt=a2a935c57214440d94513d18c4467edf; _m_h5_c=5b55ff8b19c9dd8960bd99d9f22da5dd_1652706581077%3B479dd2f58a68709e1c45fad0e6ca704e; ali_apache_track=mt=2|mid=cn1530258826rvut; _umdata=GEFEAAC533FC795AFFBECCC57F8559B3CCA836C; sgcookie=E100tPqxZPZfRfbAwx37m%2BkThjAWJOeQtu9bV1c9dUEL6EUbrSFyNKHqCaGt29psyiTnz4vgnRB4fZUWUMTZDUHZYDveMNcVAVtmxG%2Bt0lM7jZ4%3D; csg=d7728f0d; xman_us_t=ctoken=u4i2zp1_tbue&l_source=alibaba&x_user=T6sS2BDoVpZhH70amELNnXrX3nXDTD8MjrSZVuSnsow=&x_lid=cn1530258826rvut&sign=y&need_popup=y; intl_common_forever=U3TB02mcqElzcMH+P/LQykEvoxFAYIv4ofwNwnZRXn6WP9IUvVwm+w==; xman_f=EpYAoLqD2lu8QrS2q8zdfbO5kstr3viWB/z3EUXCrYzdVHmNo/t3QtJNpPSMfx0DeoeCmwQLAvggbSfCEF7LLAaK+2wopPbB2isX0sLIADyJP2JgoYl7Qb6uhWce/nrW+drEDvQdA03NbfTg6RecQMPE4nvawFFujpjzECOuCTqLTra1KE9T7rsHOl34K9M7SbCCqLy+/s0S2rs9G4HaSopgdsqC2s2kFPB1ZgYvvEkybZb5ZdSE0NQ4yx9T1jWtFt98UXFZNc7cdDnidcVfdl7vRPXMOB2KuBwOpB2yuSyRHQKJWDeG0Dx+HcXBMKzjdp0okWQ4h3naozDr5WRAex+EYodn6aq2HdhtNsu7irMlwZDeYNfrnKHh+QnN2XTDzJuO4H+sTblelG4uoW/5OeIpyQJyVxJo; acs_usuc_t=x_csrf=3o_8du3qnlc3&acs_rt=a2a935c57214440d94513d18c4467edf; JSESSIONID=AF212CEF499E5BBB87988FFC47AF2C3E; xman_t=Aj0BlYafVH3I3ZhYDwWJyJfpdU9o6Hvw4PEt+VDxLSZvjVbuoo04+O2WmK41FIq8tdEfkRG2nzhGEg8l/TPPQy2US22jIU5NUT2tOPhURbYY8kCm1AroZpOjwAG1VYAuNre0EH5XUE27vvA/vTNXZDL7Ej3oFvzAxXbYyKZdIXiJd+yHq3ysWyGMJoB9E1r5/1z0MvXCKcCdaKoi5AiEFmEeg+F5YJpdvnZiDfJhsVrjg8TNRdjkc6Y6xFz1TCPksUDqz1R3UefFb6XzEdHOhZNxc+Tvgr241vEUdDN9YsSILWVXCPg2WadWwUvxUDySC4qU17CFTNgfsYTGTaUURXHrgpvKCseMcMx7hrzpBi0RawXBod0of5Jozov3lXTT8251YU/Q2oRaqCTeGCaipCLpW9Kt0sidUAr0M31EcZyhrRpqUKkwJkWHTr0gwK66CBRM/iL9bEKhK+PfsJ/K9nkFe+KaFffVvymeIjO4kQVInu4XsnowrVUkuSFkl8zLwtZ/iOUiZyL5hNLxZ8hxoWXToDtJGSOCWTQlXN653WWD+alS8CWYoYdkUpcSNuweQ92ry4vp05gfGp9x/4h8C1Sq677CcA45JEsdIrITSC7/UIqdRdWoDLbmG5pX/mqhwOfFmqSA0UCfDk7auzVCilNu8UcCKUFA+Zd1Ru7HQ/haXaS574emsw==; l=eBjGD9wnLBzBtauhoOfwourza77OSIRAguPzaNbMiOCP_T5p5nvRW6fVVa89C3GVh6mBR3r6G1BuBeYBqnm_ckXME9uGt6Dmn; tfstk=cx2GB7A2s5l1fuh3NOM1hprO7ojdZDpqr-yQLGAuK8gjExyFisOeMo-GxVXMkI1..; isg=BMnJIcYuz_qwgbONJXQz84_m2PUjFr1IM6KGWGs-RbDvsunEs2bNGLfs9BYE6lWA";
    curl_setopt($ch, CURLOPT_HTTPHEADER,$headers);

    // 上传返回的信息
    $uploadimg = curl_exec($ch);

    // 解析上传结果
    $arr_result = json_decode($uploadimg, true);
    $img_data = $arr_result["url"];

    // 获得图片地址
    $imgurl = $img_data;
    return $imgurl;

    // 关闭请求
    curl_close($ch);

    // 删除临时文件
    unlink($upload_filepath);
}

//输出json
echo json_encode($result,true);
?>