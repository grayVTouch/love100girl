<?php
/**
 * Created by PhpStorm.
 * User: grayVTouch
 * Date: 2019/1/6
 * Time: 22:06
 */

$s_time = time();

require_once __DIR__ . '/database/app.php';

// 图片保存地址
$addr = __DIR__ . '/res/';

use Core\Lib\DBConnection;
$conn = get_conn();
$host = 'https://love100girl.com/';
$url_for_page = $host . 'page/';


// 抓取程序核心
$run = function($min_page , $max_page , $all = true) use($addr , $url_for_page)
{
    for ($page = $min_page; $page <= $max_page; ++$page)
    {
        $url = $url_for_page . $page;
        echo "总共 {$max_page} 页，正在抓取第 {$page} 页的数据...";
        $origin = curl($url);
        $s_pos = mb_strpos($origin , 'archive-posts');
        $e_pos = mb_strpos($origin , '#archive-posts');
        $len = $e_pos - $s_pos;
        $res = mb_substr($origin , $s_pos , $len);
        $reg = '/<a\s*href=[\'"](.+)[\'"]\s*.*rel=[\'"]bookmark[\'"].*>(.+)<\/a>/';
        preg_match_all($reg , $res , $matches);
        if (count($matches) !== 3) {
            log_error($url , $origin);
//        todo 调试时开启
//        debug($url , $origin);

            // 继续抓取
            echo "发生错误，等待 3s 后继续抓取...";
            sleep(3);
            echo "等待结束\n";
            continue ;
        }
        echo PHP_EOL;
        // 保存页数
        $id = db()->table('page')->insertGetId([
            'url'  => $url ,
            'page' => $page
        ]);
        // 查重
        $link = $matches[1];
        $name = $matches[2];
        $is_repeat = false;
        foreach ($link as $k => $v)
        {
            echo "总共 {$max_page} 页，正在抓取第 {$page} 页第" . ($k + 1) . "条详情...";
            $obj_for_page = db()->table('page')
                ->where('page' , $page)
                ->first();
            if (!is_null($obj_for_page)) {
                $count = db()->table('detail')->where([
                    ['page_id' , '=' , $obj_for_page->id] ,
                    ['url' , '=' , $v] ,
                ])->count();
                if ($count > 0) {
                    if (!$all) {
                        $is_repeat = true;
                        echo "数据重复，结束\n";
                        break;
                    }
                }
            }
            // 创建目录
            $dir = $addr . $name[$k] . '/';
            if (!file_exists($dir)) {
                mkdir($dir , 777);
            }
            $origin = curl($v);
            if ($origin === false) {
                log_error($v , '' , '访问 url 失败');
                echo "访问 url 失败，结束\n";
                continue ;
            }
            $s_pos = mb_strpos($origin , '頁面代碼');
            $e_pos = mb_strrpos($origin , '頁面代碼');
            $len = $e_pos - $s_pos;
            $res = mb_substr($origin , $s_pos , $len);
            $reg = '/<input\s*src=[\'"](.+)[\'"]\s*type/';
            preg_match_all($reg , $res , $matches);
//        print_r($matches);
            if (count($matches) !== 2) {
                log_error($v , $origin);
                echo "不规范的数据，结束\n";
                continue ;
            }
            $image = $matches[1];
//            echo "{$name[$k]} 内含图片 " . count($image) . " 张...";
            echo mb_substr(md5($name[$k]) , 0 , 10) . " 内含图片 " . count($image) . " 张...";
            foreach ($image as $k1 => $v1)
            {
                $file = $dir . get_filename($v1);
                if (!file_exists($file)) {
                    file_put_contents($file , file_get_contents($v1));
                }
                echo ($k1 + 1) . ' ';
            }
            db()->table('detail')->insert([
                'page_id' => $id ,
                'url' => $v
            ]);
            echo "抓取成功\n";
        }
        echo "总共 {$max_page} 页，第 {$page} 页的数据抓取完毕\n";
        if ($is_repeat) {
            // 开始重复抓取了！结束循环
            break;
        }
    }
};

$start = function($count = 0) use($host , $s_time , &$run , &$start){
    try {
        // 第一步：从第一页开始抓，抓到最后一页，如果碰到重复数据，立即停止抓取
        // 第一步获取页数
        $origin = curl($host);
        if ($origin === false) {
            throw new Exception("访问链接 {$host} 失败！");
        }
        $pos = mb_strpos($origin , 'wp_page_numbers');
        $len = 1700;
        $res = mb_substr($origin , $pos , $len);
        $page_reg = "/<li\s*class=['\"]first_last_page['\"]\s*>\s*<a.*>\s*(\d+)\s*<\/a>\s*<\/li>/";
        preg_match($page_reg , $res , $matches);
        if (count($matches) !== 2) {
            debug($host , $origin , 'page');
        }
        $min_page = 1;
        $max_page = intval($matches[1]);
        $run($min_page , $max_page , false);

        echo "最新数据同步完成，继续尚未完成的抓取任务...\n";

        // 第二部：从最后一条记录开始抓取，抓到最后一页
        $page = db()->table('page')->max('page');
        if (!is_null($page)) {
            $run($page , $max_page , true);
        }
        $e_time = time();
        $d = $e_time - $s_time;
        echo sprintf("开始时间：%s；结束时间：%s；耗时（s）：%ds；耗时（m）：%.2fm；耗时（h）：%.2fh\n" , date('Y-m-d H:i:s' , $s_time) , date('Y-m-d H:i:s' , $e_time) , $d , $d / 60 , $d / 3600);
    } catch(Exception $e) {
        if ($count < 100 && $e->getCode() == 'HY000') {
            reconnect();
            // 数据库太长时间未操作导致服务器断掉连接
        }
        log_file($e);
        if ($count < 100) {
            // 其他错误，尝试重新开始程序
            // 三次失败，则抛出异常
            $start(++$count);
        } else {
            echo "错误尝试次数过多（尝试次数：{$count}）！！程序异常退出，请查看错误日志！！\n\n";
            throw $e;
        }
    }
};
$start();

function curl($url = ''){
    $curl = curl_init();
    curl_setopt_array($curl , [
        CURLOPT_RETURNTRANSFER => true ,
        CURLOPT_HEADER => false ,
        CURLOPT_URL => $url ,
        CURLOPT_POST => false ,
        CURLOPT_HTTPHEADER => [] ,
        CURLOPT_USERAGENT => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/71.0.3578.98 Safari/537.36' ,
        CURLOPT_SSL_VERIFYPEER => false ,
        CURLOPT_FOLLOWLOCATION  => true ,
        CURLOPT_MAXREDIRS   => 3 ,
        CURLOPT_COOKIE => '' ,
        CURLOPT_POSTFIELDS => []
    ]);
    return curl_exec($curl);
}

function db(){
    global $conn;
    return $conn;
}

// http://as1d.ciame22.com:66/index.php

function debug($url , $res , $file){
    if ($res === false) {
        exit("curl 请求：{$url} 错误");
    }

    $file = __DIR__ . "/{$file}.html";
    if (file_exists($file)) {
        unlink($file);
    }
    file_put_contents($file , $res , LOCK_EX);
    exit("抓取链接：{$url} 失败，错误信息已经保存在： {$file}，请注意查看！\n");
}

function get_content($file = ''){
    return file_get_contents(__DIR__ . "/{$file}.html");
}

function encode($str = ''){
    return addslashes(htmlspecialchars($str));
}

function log_error($url = '' , $origin = '' , $remark = ''){
    return db()->table('error_log')->insert([
        'url' => $url ,
        'content' => encode($origin) ,
        'remark' => $remark
    ]);
}

function get_conn(){
    return new DBConnection([
        'type' => 'mysql' ,
        'host' => '127.0.0.1' ,
        'name' => 'love100girl' ,
        'user' => 'root' ,
        'prefix' => 'love_' ,
        'password' => '364793' ,
        'persistent' => false ,
        'charset' => 'utf8mb4'
    ]);
}

function reconnect($count = 0){
    try {
        global $conn;
        $conn = get_conn();
    } catch(Exception $e) {
        if ($count < 3) {
            reconnect(++$count);
            return ;
        }
        throw $e;
    }
}

function log_file(Exception $e){
    static $once = true;
    $log = __DIR__ . '/error.log';
    if ($once) {
        if (file_exists($log)) {
            unlink($log);
        }
        $once = false;
    }
    $msg = sprintf("Time: %s；Message：%s；Code：%s；Line：%d；File：%s\n" , date('Y-m-d H:i:s') , $e->getMessage() , $e->getCode() , $e->getLine() , $e->getFile());
    file_put_contents($log , $msg , LOCK_EX | FILE_APPEND);
}