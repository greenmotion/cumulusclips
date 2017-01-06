<?php

Plugin::triggerEvent('upload_complete.start');

// Verify if user registrations are enabled
$config = Registry::get('config');
if (!$config->enableUserUploads) App::throw404();

// Verify if user is logged in
$userService = new UserService();
$this->view->vars->loggedInUser = $userService->loginCheck();
Functions::redirectIf($this->view->vars->loggedInUser, HOST . '/login/');

// Establish page variables, objects, arrays, etc
App::enableUploadsCheck();

// Verify user completed upload process
if (isset($_SESSION['upload']->videoId)) {
    unset($_SESSION['upload']);
} else {
    header('Location: ' . HOST . '/account/upload/info/');
    exit();
}

Plugin::triggerEvent('upload_complete.end');