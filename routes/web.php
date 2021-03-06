<?php

/** @var \Laravel\Lumen\Routing\Router $router */

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It is a breeze. Simply tell Lumen the URIs it should respond to
| and give it the Closure to call when that URI is requested.
|
*/

$router->get('/', 'PublicController@index');
$router->get('/inspire', 'PublicController@inspireMe');

$router->group(
    ['middleware' => ['auth:api', 'throttle:30,1']],
    function () use ($router) {
        // categories
        $router->group(
            ['prefix' => 'category'],
            function () use ($router) {
                $router->get('base', 'CategoryController@index');
                $router->get('sub/ids', 'CategoryController@getSubIds');
                $router->get('sub/list', 'CategoryController@getSubList');
                $router->get('/sub/{slug:[a-z0-9]+(?:-[a-z0-9]+)*}', 'CategoryController@show');

                $router->post('/sub', [
                    'middleware' => 'scopes:create-sub',
                    'uses' => 'CategoryController@store'
                ]);
            }
        );

        // products
        $router->group(
            ['prefix' => 'product'],
            function () use ($router) {
                $router->get('ids[/{perPage:[0-9]+}]', 'ProductController@indexIds');
                $router->get('list[/{perPage:[0-9]+}]', 'ProductController@index');
                // search by slug or name
                $router->get(
                    'find[/{perPage:[0-9]+}]',
                    'ProductController@search'
                );
                $router->get(
                    'sub/{slug:[a-z0-9]+(?:-[a-z0-9]+)*}[/{perPage:[0-9]+}]',
                    'ProductController@indexSubCat'
                );
                $router->get('collect/{ids:[0-9]+(?:,[0-9]+)*}', 'ProductController@showCollection');

                $router->post('', 'ProductController@store');
                $router->post('{slug:[a-z0-9]+(?:-[a-z0-9]+)*}/patch', 'ProductController@update');
                $router->post('{slug:[a-z0-9]+(?:-[a-z0-9]+)*}/delete', 'ProductController@destroy');

                // filters
                $router->get(
                    'filter/sub/{slug:[a-z0-9]+(?:-[a-z0-9]+)*}/brands/{brands}[/{perPage:[0-9]+}]',
                    'ProductController@indexByBrands'
                );
                $router->get(
                    'filter/sub/{slug:[a-z0-9]+(?:-[a-z0-9]+)*}/condition/{cond:[0-1]}[/{perPage:[0-9]+}]',
                    'ProductController@indexByCondition'
                );
                $router->get(
                    'filter/sub/{slug:[a-z0-9]+(?:-[a-z0-9]+)*}/price/{prices}[/{perPage:[0-9]+}]',
                    'ProductController@indexByPrice'
                );

                $router->get('{slug:[a-z0-9]+(?:-[a-z0-9]+)*}', 'ProductController@show');

                // rates
                $router->get(
                    '{slug:[a-z0-9]+(?:-[a-z0-9]+)*}/rates[/{perPage:[0-9]+}]',
                    'RateController@index'
                );
                $router->post(
                    '{slug:[a-z0-9]+(?:-[a-z0-9]+)*}/rates',
                    'RateController@store'
                );
                $router->post(
                    '{slug:[a-z0-9]+(?:-[a-z0-9]+)*}/rates/{rate}',
                    'RateController@update'
                );
            }
        );

        // user
        $router->group(['prefix' => 'user'],
        function () use ($router) {
            $router->get('list[/{perPage:[0-9]+}]', 'UserController@index');
            $router->get('ids[/{perPage:[0-9]+}]', 'UserController@indexIds');
            $router->get('/profile[/{userId:[0-9]+}]', 'UserController@show');
            $router->get('/orders[/{userId:[0-9]+}]', 'UserController@showOrders');
            $router->get('/products[/{userId:[0-9]+}]', 'UserController@showProducts');

            $router->post('{userId:[0-9]+}/role/patch', [
                'uses' => 'UserController@update',
                'middleware' => 'scopes:patch-role'
            ]);
        });
    }
);
