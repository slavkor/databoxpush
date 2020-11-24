<?php

// Define app routes

use App\Middleware\UserAuthMiddleware;
use Slim\App;
use Slim\Routing\RouteCollectorProxy;

return function (App $app) {
    $app->get('/', \App\Action\Home\HomeAction::class)->setName('root');

    /*
    $app->get('/hello/{name}', \App\Action\Hello\HelloAction::class)->setName('hello');
    // HTML view
    $app->get('/users/{id}', \App\Action\User\UserViewAction::class)->setName('user-view');
    // API endpoint
    $app->post('/api/users', \App\Action\User\UserCreateAction::class)->setName('api-user-create');
    */
    
    $app->get('/login', \App\Action\Auth\LoginAction::class)->setName('login');
    $app->post('/login', \App\Action\Auth\LoginSubmitAction::class);
    $app->post('/authlogin', \App\Action\Auth\AuthLoginAction::class)->setName('authlogin');
    $app->get('/logout', \App\Action\Auth\LogoutAction::class)->setName('logout');

    //protected area
    $app->group('/users', function (RouteCollectorProxy $group) {
        $group->get('', \App\Action\UserListAction::class)->setName('user-list');
        $group->post('/datatable', \App\Action\User\UserListDataTableAction::class)->setName('user-datatable');
    })->add(UserAuthMiddleware::class);
    
    
    // Password protected area
    $app->group('/push', function (RouteCollectorProxy $group) {
        $group->get('', \App\Action\Push\PushAction::class)->setName('push-view');
        $group->post('/list', \App\Action\Push\PushListAction::class)->setName('push-list');
        $group->post('/execute', \App\Action\Push\PushExecuteAction::class)->setName('push-execute');
    })->add(UserAuthMiddleware::class);
};
