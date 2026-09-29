<?php
use App\Router\Router;

/** @var Router $router */

// Public routes
$router->get('/', 'App\Controllers\PropertyController@index')->name('home');

// Authentication routes
$router->get('/login', 'App\Controllers\AuthController@showLogin')->name('login');
$router->post('/login', 'App\Controllers\AuthController@login');
$router->post('/logout', 'App\Controllers\AuthController@logout')->name('logout');

// Language switcher
$router->post('/language', 'App\Controllers\LanguageController@switch');

// Protected routes (require authentication)
$router->group(['middleware' => 'App\Middleware\AuthMiddleware'], function($router) {
    // Dashboard
    $router->get('/dashboard', 'App\Controllers\DashboardController@index')->name('dashboard');
    
    // Properties
    $router->get('/properties', 'App\Controllers\PropertyController@index')->name('properties.index');
    $router->get('/properties/create', 'App\Controllers\PropertyController@create')->name('properties.create');
    $router->post('/properties', 'App\Controllers\PropertyController@store');
    $router->get('/properties/{id}', 'App\Controllers\PropertyController@show')->name('properties.show');
    $router->get('/properties/{id}/edit', 'App\Controllers\PropertyController@edit')->name('properties.edit');
    $router->put('/properties/{id}', 'App\Controllers\PropertyController@update');
    $router->delete('/properties/{id}', 'App\Controllers\PropertyController@destroy');
    
    // Property photos
    $router->post('/properties/{id}/photos', 'App\Controllers\PropertyPhotoController@upload');
    $router->put('/properties/{id}/photos/reorder', 'App\Controllers\PropertyPhotoController@reorder');
    $router->put('/properties/{id}/photos/{photo_id}/hero', 'App\Controllers\PropertyPhotoController@setHero');
    $router->delete('/properties/{id}/photos/{photo_id}', 'App\Controllers\PropertyPhotoController@destroy');
    
    // Offices
    $router->get('/offices', 'App\Controllers\OfficeController@index')->name('offices.index');
    $router->get('/offices/create', 'App\Controllers\OfficeController@create')->name('offices.create');
    $router->post('/offices', 'App\Controllers\OfficeController@store');
    $router->get('/offices/{id}', 'App\Controllers\OfficeController@show')->name('offices.show');
    $router->get('/offices/{id}/edit', 'App\Controllers\OfficeController@edit')->name('offices.edit');
    $router->put('/offices/{id}', 'App\Controllers\OfficeController@update');
    $router->delete('/offices/{id}', 'App\Controllers\OfficeController@destroy');
    
    // Agents
    $router->get('/agents', 'App\Controllers\AgentController@index')->name('agents.index');
    $router->get('/agents/create', 'App\Controllers\AgentController@create')->name('agents.create');
    $router->post('/agents', 'App\Controllers\AgentController@store');
    $router->get('/agents/{id}', 'App\Controllers\AgentController@show')->name('agents.show');
    $router->get('/agents/{id}/edit', 'App\Controllers\AgentController@edit')->name('agents.edit');
    $router->put('/agents/{id}', 'App\Controllers\AgentController@update');
    $router->delete('/agents/{id}', 'App\Controllers\AgentController@destroy');
    
    // Users (for internal auth)
    $router->get('/users', 'App\Controllers\UserController@index')->name('users.index');
    $router->get('/users/create', 'App\Controllers\UserController@create')->name('users.create');
    $router->post('/users', 'App\Controllers\UserController@store');
    $router->get('/users/{id}/edit', 'App\Controllers\UserController@edit')->name('users.edit');
    $router->put('/users/{id}', 'App\Controllers\UserController@update');
    $router->delete('/users/{id}', 'App\Controllers\UserController@destroy');
});
