<?php
require 'vendor/autoload.php';

$app = new \Slim\Slim(array(
    'model' => 'development',
    'view' => new \Slim\Extras\Views\Twig(),
    'templates.path' => './templates',
    'cookies.lifetime' => '3 years',
    'cookies.secret_key' => 'laiyitui',
    'cookies.cipher' => MCRYPT_RIJNDAEL_256,
    'cookies.cipher_mode' => MCRYPT_MODE_CBC,
));

class R extends RedBean_Facade {}
R::setup('mysql:host=localhost;dbname=laiyitui_com', 'root', '');

// 首页
$app->get('/', function() use ($app) {
    $data = array(
        'url' => $app->urlFor('explore')
    );
    $app->render('index.html', $data);
});

// 图片浏览
$app->get('/explore(/:way(/:page))', function($way = 'latest', $page = 1) use ($app) {
    $data = array(
        'latest_url' => $app->urlFor('explore', array('way' => 'latest', 'page' => 1)),
        'hot_url' => $app->urlFor('explore', array('way' => 'hot', 'page' => 1)),
        'controversial_url' => $app->urlFor('explore', array('way' => 'controversial', 'page' => 1)),
        'current' => $way,
        'auth' => auth($app)
    );
    $app->render('explore.html', $data);
})->name('explore');

// 查看图片
$app->get('/view/:id', function($id) use ($app) {
})->name('view');

// 留言
$app->post('/comment/:id', function($id) use ($app) {
})->name('comment');

// 上传
$app->map('/upload', function() use ($app) {
    $app->render('upload.html');
})->via('GET', 'POST');

// 登录
$app->map('/signin', function() use ($app) {
    $app->render('signin.html');
})->via('GET', 'POST');

// 注册
$app->map('/signup', function() use ($app) {
    if ($app->request()->isGet()) {
        $app->render('signup.html');
    } elseif ($app->request()->isPost()) {
        $user = R::dispense('user');
        $user->username = $_POST['username'];
        $user->email = $_POST['email'];
        $user->password = md5($_POST['password']);
        $user->created_at = $user->updated_at = time();
        if ($id = R::store($user)) {
            $auth = array(
                'id' => $user->id,
                'username' => $user->username,
                'email' => $user->email
            );
            signin($app, $auth);
            $app->redirect('/explore');
        }
    }
})->via('GET', 'POST');

// =================================
// some hepler functions
// =================================
function dump($var = '') {
    ob_start();
    var_dump($var);
    $output = ob_get_clean();
    echo '<pre>';
    echo htmlspecialchars($output);
    echo '</pre>';
}

function signin($app = null, $auth = array()) {
    return $app->setEncryptedCookie('auth', serialize($auth));
}

function auth($app) {
    return unserialize($app->getEncryptedCookie('auth'));
}

$app->run();
