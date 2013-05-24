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
// function home
$app->get('/', function() use ($app) {
    $data = array(
        'url' => $app->urlFor('explore')
    );
    $app->render('index.html', $data);
});

// 图片浏览
// function explore
$app->get('/explore(/:way(/:page))', function($way = 'latest', $page = 1) use ($app) {
    switch ($way) {
    case 'latest':
        $orderby = 'id';
        break;
    case 'hot':
        $orderby = 'viewed';
        break;
    case 'controversial':
        $orderby = 'stared';
        break;
    }

    $limit = 120;
    $offset = $limit * ($page - 1);

    $images = R::findAll('image', 'order by :orderby desc limit :offset, :limit', array(
        ':orderby' => $orderby,
        ':offset' => $offset,
        ':limit' => $limit
    ));

    $data = array(
        'images' => $images,
        'offset' => $offset,
        'limit' => $limit,
        'current' => $way,
        'auth' => auth($app)
    );
    $app->render('explore.html', $data);
})->name('explore');

// 查看图片
// function view
$app->get('/view/:id', function($id) use ($app) {
    $image = R::findOne('image', 'id=:id', array(
        ':id' => $id
    ));

    $data = array(
        'image' => $image,
        'auth' => auth($app)
    );
    $app->render('view.html', $data);
})->name('view');

// 留言
// function comment
$app->post('/comment/:id', function($id) use ($app) {
})->name('comment');

// 上传
// function upload
$app->map('/upload', function() use ($app) {
    // 未登录
    if (!auth($app)) {
        $app->redirect('/signin');
    }

    if ($app->request()->isGet()) {
        $data = array(
            'current' => 'upload',
            'auth' => auth($app)
        );
        $app->render('upload.html', $data);
    } elseif ($app->request()->isPost()) {
        if (isset($_POST['url']) && $_POST['url']) {
            $images = scratch_image($_POST['url']);
        }

        $image = R::dispense('image');
        $image->image = $images['image'];
        $image->thumb = $images['thumb'];
        $image->created_at = $image->updated_at = time();
        if ($id = R::store($image)) {
            $app->redirect($app->urlFor('view', array('id' => $id)));
        }
    }
})->via('GET', 'POST');

// 登录
// function signin
$app->map('/signin', function() use ($app) {
    // 已登录
    if (auth($app)) {
        $app->redirect('/explore');
    }

    if ($app->request()->isGet()) {
        $data = array(
            'current' => 'signin'
        );
        $app->render('signin.html', $data);
    } elseif ($app->request()->isPost()) {
        $user = R::findOne('user', 'username=:username || email=:email', array(
            ':username' => $_POST['username'],
            ':email' => $_POST['username']
        ));

        if ($user) {
            if (md5($_POST['password']) == $user->password) {
                $auth = array(
                    'id' => $user->id,
                    'username' => $user->username,
                    'email' => $user->email
                );
                set_auth_cookie($app, $auth);
                $app->redirect('/explore');
            } else {
                $app->redirect('/signin');
            }
        } else {
            $app->redirect('/signin');
        }
    }
})->via('GET', 'POST');

// 注册
// function signup
$app->map('/signup', function() use ($app) {
    // 已登录
    if (auth($app)) {
        $app->redirect('/explore');
    }

    if ($app->request()->isGet()) {
        $data = array(
            'current' => 'signup'
        );
        $app->render('signup.html', $data);
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
            set_auth_cookie($app, $auth);
            $app->redirect('/explore');
        }
    }
})->via('GET', 'POST');

// 登出
// function signout
$app->get('/signout', function() use ($app) {
    $app->deleteCookie('auth');
    $app->redirect('/signin');
});

// =================================
// some hepler functions
// =================================

// 格式化打印变量
function dump($var = '') {
    ob_start();
    var_dump($var);
    $output = ob_get_clean();
    echo '<pre>';
    echo htmlspecialchars($output);
    echo '</pre>';
}

// 登录
function set_auth_cookie($app = null, $auth = array()) {
    return $app->setEncryptedCookie('auth', serialize($auth));
}

// 获取用户登录信息
function auth($app) {
    return unserialize($app->getEncryptedCookie('auth'));
}

// 抓取图片
function scratch_image($url) {
    // 抓取图片比较耗时，30秒吧
    set_time_limit(30);

    // 临时文件名
    $tmp_file_name = realpath('.') . '/tmp/' . sha1(uniqid()) . '.png';
    // 获取图片，写入临时文件
    file_put_contents($tmp_file_name, file_get_contents($_POST['url']));
    // 取得文件扩展名
    $imageinfo = getimagesize($tmp_file_name);
    $ext = (get_extension($imageinfo['mime']));

    // 目标目录
    $dir = '/files/' . date('Y/m/d/');
    if (!file_exists(realpath('.') . $dir)) {
        mkdir(realpath('.') . $dir, 0777, true);
    }

    // 图片文件名，缩略图文件名
    $unique = sha1(uniqid());
    $image_file = $dir . $unique . $ext;
    $thumb_file = $dir . $unique . '_thumb' . $ext;

    // 写入文件
    WideImage\WideImage::load($tmp_file_name)->saveToFile(realpath('.') . $image_file);
    WideImage\WideImage::load($tmp_file_name)->resize(200, 200)->saveToFile(realpath('.') . $thumb_file);

    // @todo 加水印

    // 删除临时文件
    unlink($tmp_file_name);

    return array('image' => $image_file, 'thumb' => $thumb_file);
}

// 通过 mime 获取文件扩展名
function get_extension($imagetype) {
    if (empty($imagetype)) {
        return false;
    }
    switch($imagetype) {
        case 'image/bmp': return '.bmp';
        case 'image/cis-cod': return '.cod';
        case 'image/gif': return '.gif';
        case 'image/ief': return '.ief';
        case 'image/jpeg': return '.jpg';
        case 'image/pipeg': return '.jfif';
        case 'image/tiff': return '.tif';
        case 'image/x-cmu-raster': return '.ras';
        case 'image/x-cmx': return '.cmx';
        case 'image/x-icon': return '.ico';
        case 'image/x-portable-anymap': return '.pnm';
        case 'image/x-portable-bitmap': return '.pbm';
        case 'image/x-portable-graymap': return '.pgm';
        case 'image/x-portable-pixmap': return '.ppm';
        case 'image/x-rgb': return '.rgb';
        case 'image/x-xbitmap': return '.xbm';
        case 'image/x-xpixmap': return '.xpm';
        case 'image/x-xwindowdump': return '.xwd';
        case 'image/png': return '.png';
        case 'image/x-jps': return '.jps';
        case 'image/x-freehand': return '.fh';
        default: return false;
    }
}

$app->run();
