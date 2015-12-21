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

$app->container->singleton('loginHelper', function () use ($app) {
   return new LoginHelper($app->em->getRepository('Uppu4\Entity\User'), $app->request->cookies, $app->response->cookies);
});

$app->view->appendData(array(
    'loginHelper' => $app->loginHelper,
    'helper' => new FormatHelper
));

$app->map('/', function () use ($app) {
    $page = 'index';
    $message = '';
    if ($app->request->isPost()) {
        if (file_exists($_FILES['load']['tmp_name']) && UPLOAD_ERR_OK == 0) {
            $user = $app->loginHelper->getUser();
            if (!$user) {
                $user = new UserHelper($app->em, $app->response->cookies);
                $user = $user->saveAnonymousUser();
            }
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

$app->map('/login', function() use ($app) {
    $page = 'login';
    if ($app->request->isPost()) {
        $app->loginHelper->authenticateUser();

//        if ($user = $app->em->getRepository('Uppu4\Entity\User')->findOneByLogin($app->request->params('login'))) {
//            if ($user->getHash() === \Uppu4\Helper\HashGenerator::generateHash())
//        }
    }
});

$app->get('/view/:id/', function ($id) use ($app) {
    $file = $app->em->find('Uppu4\Entity\File', $id);
    if (!$file) {
        $app->notFound();
    }
    $user = $app->em->getRepository('Uppu4\Entity\User')->findOneById($file->getUploadedBy());
    //$comments = $app->em->getRepository('Uppu4\Entity\Comment')->findBy(array('fileId' => $id), array('path' => 'ASC'));
    $app->render('view.html', array('file' => $file, 'user' => $user/*, ('comments' => $comments*/));

});

$app->run();