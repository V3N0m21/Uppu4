<?php
require_once '../vendor/autoload.php';
require_once '../app/bootstrap.php';
use Uppu4\Helper\LoginHelper;
use Uppu4\Helper\UserHelper;
use Uppu4\Helper\FileHelper;
use Uppu4\Helper\FormatHelper;

$app = new \Slim\Slim(array('view' => new \Slim\Views\Twig(), 'templates.path' => '../app/templates'));

$app->container->singleton('em', function () use ($entityManager) {
   return $entityManager;
});

$app->container->singleton('userHelper', function () use ($app) {
   return new UserHelper($app->em, $app->request->cookies, $app->response->cookies);
});
//$app->container->singleton('loginHelper', function () use ($app) {
//   return new LoginHelper($app->em->getRepository('Uppu4\Entity\User'), $app->request->cookies, $app->response->cookies);
//});


$app->view->appendData(array(
    'helper' => new FormatHelper,
    'userHelper' => $app->userHelper
));

$app->map('/', function () use ($app) {
    $page = 'index';
    $message = '';
    if ($app->request->isPost()) {
        if (file_exists($_FILES['load']['tmp_name']) && UPLOAD_ERR_OK == 0) {
            $user = $app->userHelper->getUser();
            $fileHelper = new FileHelper($app->em);
            $fileHelper->fileValidate($_FILES['load']);
            if (empty($fileHelper->errors)) {
                $file = $fileHelper->fileSave($_FILES['load'], $user);
                $id = $file->getId();
                $app->redirect("/view/$id");
            } else {
                $message = $fileHelper->errors[0];
            }
        } else {
            $message = "Вы не выбрали файл";
        }
    }
    $app->render('file_load.html', array('page' => $page, 'message' => $message));
})->via('GET', 'POST');

$app->map('/register/', function () use ($app) {
    $errors = '';
    $data = '';

    if ($app->request->isPost()) {
        $validation = new \Uppu4\Helper\DataValidator();
        $validation->validateUserData($app->request->post());
        if (empty($validation->error)) {
        $user = $app->userHelper->getUser();
        $user = $app->userHelper->saveUser($user, $app->request->post());
        $id = $user->getId();
        $app->redirect("/users/$id");
        } else {
            $errors = $validation->error;
            $data = $app->request->post();
        }
    }

    $app->render('register.html', array('errors' => $errors, 'data' => $data));
})->via('GET', 'POST');

$app->map('/login/', function() use($app) {
    $page = 'login';
    $error = '';
    if ($app->request->isPost()) {
        $login = $app->request->params('login');
        $password = $app->request->params('password');
        $user = $app->userHelper->authenticateUser($login, $password);

        if($user){
           $id = $user->getId();
           $app->redirect("/users/$id/");
        } else {
            $error = "Неправильный логин или пароль";
        }
    }

    $app->render('login_form.html', array('errors' => $error, 'page' => $page));

})->via('GET', 'POST');

$app->get('/logout', function () use ($app) {
    $app->userHelper->logout();
    $app->redirect('/');
});

$app->get('/users/', function() use($app) {
   $page = 'users';
    $users = $app->em->getRepository('Uppu4\Entity\User')->findBy([], ['created' => 'DESC']);
    $filesCount = $app->em->createQuery('SELECT IDENTITY(u.uploadedBy), count(u.uploadedBy) FROM Uppu4\Entity\File u GROUP BY u.uploadedBy');
    $filesCount = $filesCount->getArrayResult();
    $list = [];
    foreach ($filesCount as $count) {
        $list[$count[1]] = $count[2];
    }
    $app->render('users.html', array('users' => $users, 'page' => $page, 'filesCount' => $list));
});

$app->get('/users/:id/', function ($id) use ($app) {
    $page = 'users';
    $user = $app->em->getRepository('Uppu4\Entity\User')->findOneById($id);
    $files = $app->em->getRepository('Uppu4\Entity\File')->findByUploadedBy($id);
    $app->render('user.html', array('user' => $user, 'files' => $files, 'page' => $page));
});

$app->delete('/users/:id/', function ($id) use ($app) {
    $app->userHelper->userDelete($id);
    $app->redirect('/users');
});

$app->get('/list', function () use ($app) {
    $page = 'list';
    $files = $app->em->getRepository('Uppu4\Entity\File')->findBy([], ['uploaded' => 'DESC'], 50, 0);
    $app->render('list.html', array('files' => $files, 'page' => $page));
});

$app->get('/view/:id/', function ($id) use ($app) {
    $file = $app->em->find('Uppu4\Entity\File', $id);
    if (!$file) {
        $app->notFound();
    }
    $user = $app->em->getRepository('Uppu4\Entity\User')->findOneById($file->getUploadedBy());
    $comments = $app->em->getRepository('Uppu4\Entity\Comment')->findBy(array('fileId' => $id), array('path' => 'ASC'));
    $app->render('view.html', array('file' => $file, 'user' => $user, 'comments' => $comments));

});

$app->delete('/view/:id/', function ($id) use ($app) {
    $fileHelper = new \Uppu4\Helper\FileHelper($app->em);
    $fileHelper->fileDelete($id);
    $app->redirect('/list');
});

$app->map('/ajaxComments/:id', function ($id) use ($app) {
    if ($app->request->isPost()) {
        $parent = isset($_POST['parent']) ? $app->em->find('Uppu4\Entity\Comment', $app->request->params('parent')) : null;
        $file = $app->em->find('Uppu4\Entity\File', $id);
        $user = $app->em->find('Uppu4\Entity\User', $app->request->params('userId'));
        $validation = new \Uppu4\Helper\DataValidator;
        $commentHelper = new CommentHelper($_POST, $app->em, $parent, $file, $user);
        $comment = $commentHelper->comment;
        $validation->validateComment($comment);
        if (!$validation->hasErrors()) {
            $commentHelper->commentSave();
        };
        $comments = $app->em->getRepository('Uppu3\Entity\Comment')->findBy(array('fileId' => $id), array('path' => 'ASC'));
        $app->render('comments.html', array('comments' => $comments));
    }
})->via('GET', 'POST');

$app->get('/download/:id/:name', function ($id, $name) use ($app) {
    $file = $app->em->find('Uppu4\Entity\File', $id);
    $name = FormatHelper::formatDownloadFile($id, $file->getName());

    if (file_exists($name)) {
        header("X-Sendfile:" . realpath(dirname(__FILE__)) . '/' . $name);
        header("Content-Type: application/octet-stream");
        header("Content-Disposition: attachment");
        return;
    } else {
        $app->notFound();
    }
});

$app->run();