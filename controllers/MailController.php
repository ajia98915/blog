<?php
namespace controllers;

use libs\Mail;
class MailController
{
    public function send()
    {
        $redis = \libs\Redis::getInstance();

        $mailer = new Mail;
        //设置PHP 永不超时
        ini_set('default_socket_timeout',-1);
        echo "发邮件队列启动成功..\r\n";

        //循环从队列中取消息并发邮件
        while(true)
        {
        // 1.循环队列中取消息
        //从 email里取消息 ，0：代表如果没有消息就阻塞在这里直到看到有消息才向后执行代码
        //$data 结构：
        /* 
          $data = [
              'email,
              '消息的字符串'，
          ];
        */
        $data = $redis->brpop('email',0);
        // var_dump($data);
        //取出消息并反序列化（转回数组）
        //json_deocde:默认数据转成一个对象，如果要转成数组需要设置第二个参数为true
        $message = json_decode($data[1],TRUE);
        // var_dump($message);
        
        //2.发邮件
        $mailer->send($message['title'],$message['content'],$message['from']);

        echo "发送成功！继续等待下一个！\r\n";
    }
  }
}

