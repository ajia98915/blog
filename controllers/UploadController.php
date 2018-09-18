<?php
namespace controllers;

class UploadController
{
    public function upload()
    {
        //接受图片
        $file = $_FILES['image'];

        //生成随机文件名
        $name = time();

        // 移动图片
        move_uploaded_file($file['tmp_name'], ROOT . 'public/uploads/'.$name.'.png');
     
           /*
        {
        "success": true/false,
        "msg": "error message", # 可选
        "file_path": "[real file path]"
        }*/
   
        // php 不支持session 要求把数组变成 json 并返回 
        echo json_encode([
            'success' => true,
            // 'msg' => '上传姿势不对',
            'file_path' => 'public/uplosds/'.$name.'.png',
        ]);
    }
}




?>