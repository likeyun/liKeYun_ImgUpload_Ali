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
    $headers[] = "填写你的cookie";
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
    unlink($realpath);
}

//输出json
echo json_encode($result,true);
?>
