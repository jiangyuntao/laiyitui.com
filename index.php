<?php
require 'vendor/autoload.php';

$app = new \Slim\Slim(array(
    'model' => 'development',
    'templates.path' => './templates',
    'cookies.lifetime' => '3 years',
    'cookies.secret_key' => 'laiyitui',
    'cookies.cipher' => MCRYPT_RIJNDAEL_256,
    'cookies.cipher_mode' => MCRYPT_MODE_CBC,
));

// 首页
$app->get('/', function() use ($app) {
    $data = array(
        'url_explore_hot' => $app->urlFor('explore', array('way' => 'hot', 'page' => 1))
    );
    $app->render('index.html', $data);
});

// 图片浏览
$app->get('/explore/:way/:page', function($way, $page) use ($app) {
    var_dump($way);
    var_dump($page);
    $app->render('index.html');
})->name('explore');

// 查看图片
$app->get('/view/:id', function($id) use ($app) {
});

// 留言
$app->post('/comment/:id', function($id) use ($app) {
});

// 上传页面
$app->get('/upload', function() use ($app) {
});
// 上传处理
$app->post('/upload', function() use ($app) {
});

$app->run();
