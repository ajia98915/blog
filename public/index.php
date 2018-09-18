<?php
ini_set('session.save_handler', 'redis');   // 使用 redis 保存 SESSION
ini_set('session.save_path', 'tcp://127.0.0.1:6379?database=3');  // 设置 redis 服务器的地址、端口、使用的数据库    
ini_set('session.gc_maxlifetime', 1440);   // 设置 SESSION 10分钟过期
session_start();

//如果用户以post方式访问网站时需要验证令牌 
if($_SERVER['REQUEST_METHOD'] == 'POST')
{
     if(!isset($_POST['_token']))
     die('违法操纵！');

     if($_POST['_token'] != $_SESSION['token'])
     die('违法操作！');
}

// 定义常量
define('ROOT', dirname(__FILE__) . '/../');

// 引入 composer 自动加载文件
require(ROOT.'vendor/autoload.php');

// 实现类的自动加载
function autoload($class)
{
    $path = str_replace('\\', '/', $class);

    require(ROOT . $path . '.php');
}
spl_autoload_register('autoload');

// 添加路由 ：解析 URL 浏览器上 blog/index  CLI中就是 blog index

if(php_sapi_name() == 'cli')
{
    $controller = ucfirst($argv[1]) . 'Controller';
    $action = $argv[2];
}
else
{
    if( isset($_SERVER['PATH_INFO']) )
    {
        $pathInfo = $_SERVER['PATH_INFO'];
        // 根据 / 转成数组
        $pathInfo = explode('/', $pathInfo);

        // 得到控制器名和方法名 ：
        $controller = ucfirst($pathInfo[1]) . 'Controller';
        $action = $pathInfo[2];
    }
    else
    {
        // 默认控制器和方法
        $controller = 'IndexController';
        $action = 'index';
    }
}


// 为控制器添加命名空间
$fullController = 'controllers\\'.$controller;


$_C = new $fullController;
$_C->$action();

// 加载视图
// 参数一、加载的视图的文件名
// 参数二、向视图中传的数据
function view($viewFileName, $data = [])
{
    // 解压数组成变量
    extract($data);

    $path = str_replace('.', '/', $viewFileName) . '.html';

    // 加载视图
    require(ROOT . 'views/' . $path);
}

// 获取当前 URL 上所有的参数，并且还能排除掉某些参数
// 参数：要排除的变量
function getUrlParams($except = [])
{
    // ['odby','odway']
    // 循环删除变量
    foreach($except as $v)
    {
        unset($_GET[$v]);

        // unset($_GET['odby']);
        // unset($_GET['odway']);
    }

    /*
    $_GET['keyword'] = 'xzb';
    $_GET['is_show] = 1

    // 拼出：  keyword=abc&is_show=1
    */

    $str = '';
    foreach($_GET as $k => $v)
    {
        $str .= "$k=$v&";
    }

    return $str;

}

// 获取配置文件（特点：无论调用多次，只包含一次配置文件）
// 静态局部变量：函数执行结束，也不会销毁，一直存在到整个脚本结束
// 普通局部亦是：函数执行完就销毁了
function config($name)
{
    static $config = null;
    if($config === null)
    {
        // 引入配置文件 
        $config = require(ROOT.'config.php');
    }
    return $config[$name];
}

function redirect($url)
{
    header('Location:'. $url);
    exit;
}
//跳回
function back()
{
   redirect($_SERVER('HTTP_REFERER'));
}
//成功 (green)
//type:0:alert 1:显示的消息页面 2：在下一个页面显示
//说明 second 只有在1的时候有效
function message($message,$type,$url,$seconds = 5)
{
    if($type ==0)
    {
       echo" <script>alert('{$message}');location.href='{$url}'; </script>";
       exit;
    }
    else if($type == 1)
    {
       view('common.success',[
           'message'=>$message,
           'url'=>$url,
           'seconds'=>$seconds
       ]);
    }
    else if($type == 2)
    {
      //把消息保存到session中
      $_SESSION['_MESS_'] = $message;
      //跳转到下一个页面
      redirect($url);
    }
}

//操作失败 （red)
function error()
{

}
 //xss过滤 
function e($content)
{
    return htmlspecialchars($content);
}
// 过滤
 function hpe($content)
{
   // 1. 生成配置对象
   $config = \HTMLPurifier_Config::createDefault();

   // 2. 配置
   // 设置编码
   $config->set('Core.Encoding', 'utf-8');
   $config->set('HTML.Doctype', 'HTML 4.01 Transitional');
   // 设置缓存目录
   $config->set('Cache.SerializerPath', ROOT.'cache');
   // 设置允许的 HTML 标签
   $config->set('HTML.Allowed', 'div,b,strong,i,em,a[href|title],ul,ol,ol[start],li,p[style],br,span[style],img[width|height|alt|src],*[style|class],pre,hr,code,h2,h3,h4,h5,h6,blockquote,del,table,thead,tbody,tr,th,td');
   // 设置允许的 CSS
   $config->set('CSS.AllowedProperties', 'font,font-size,font-weight,font-style,margin,width,height,font-family,text-decoration,padding-left,color,background-color,text-align');
   // 设置是否自动添加 P 标签
   $config->set('AutoFormat.AutoParagraph', TRUE);
   // 设置是否删除空标签
   $config->set('AutoFormat.RemoveEmpty', true);

   // 3. 过滤
   // 创建对象
   $purifier = new \HTMLPurifier($config);
   // 过滤
   $clean_html = $purifier->purify($content);
}


function csrf()
{
    if(!isset($_SESSION['token']))
    {
        // 生成一个随机的字符串
        $token = md5( rand(1,99999) . microtime() );
        $_SESSION['token'] = $token;
    }
    return $_SESSION['token'];
}


// 生成令牌隐藏域
function csrf_field()
{
    $csrf = isset($_SESSION['token']) ? $_SESSION['token'] : csrf();
    echo "<input type='hidden' name='_token' value='{$csrf}'>";
}
