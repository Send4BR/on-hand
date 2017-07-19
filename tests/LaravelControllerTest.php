<?php

use Mockery as m;
use Illuminate\Http\Request;

require_once __DIR__.'/Controller.php';

class LaravelControllerTest extends Orchestra\Testbench\TestCase
{

    public function testDefaultsWorks()
    {
        $request = $this->createRequest([], null);
        $controller = $this->createControllerMock($request);

        $options = $controller->getResourceOptions();

        $this->assertEquals('name', $options['sort']);
    }

    public function testResponseIsGenerated()
    {
        $controller = new Controller;
        $response = $controller->getResponseWithResourceCollection();
        $data = $response->getData();

        $this->assertTrue($response instanceof \Illuminate\Http\JsonResponse);
        $this->assertTrue(is_array($data));
    }

    public function testParametersAreAppliedCorrectly()
    {
        $request = $this->createRequest(['children', 'children2'], 'name', 100, 2, [
            [
                'filters' => [
                    'name:eq(foo)',
                    'name:ct(bar)'
                ]
            ]
        ]);
        $controller = $this->createControllerMock($request);

        $options = $controller->getResourceOptions();

        $this->assertEquals($options['includes'], [
            'children', 'children2'
        ]);
        $this->assertEquals($options['sort'], 'name');
        $this->assertEquals($options['limit'], 100);
        $this->assertEquals($options['page'], 2);
        $this->assertTrue(count($options['filter_groups']) > 2);
    }

    public function testArchitectIsFired()
    {
        $request = $this->createRequest(['children:ids']);
        $controller = $this->createControllerMock($request);

        $options = $controller->getResourceOptions();
        $resources = $controller->getParsedResourceCollection($options);

        $this->assertEquals($resources['resources']->get(0)['children']->toArray(), [
            1, 2, 3
        ]);
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testThatExceptionIsThrownWhenSettingPageButNotLimit()
    {
        $request = $this->createRequest(['children', 'children2'], 'name', null, 2);
        $controller = $this->createControllerMock($request);

        $controller->getResourceOptions();
    }

    private function createControllerMock(Request $request)
    {
        $routerMock = m::mock('Illuminate\Routing\Router');
        $routerMock->shouldReceive('getCurrentRequest')->andReturn($request);

        $controller = m::mock('Controller[getRouter]');
        $controller->shouldReceive('getRouter')->once()->andReturn($routerMock);

        return $controller;
    }

    private function createRequest(array $includes = [], $sort = 'property', $limit = null, $page = null, array $filters = [])
    {
        $vars = [];
        if (!empty($includes)) {
            $vars['includes'] = $includes;
        }

        if (!is_null($sort)) {
            $vars['sort'] = $sort;
        }

        if (!is_null($limit)) {
            $vars['limit'] = $limit;
        }

        if (!is_null($page)) {
            $vars['page'] = $page;
        }

        if (!empty($filters)) {
            $vars['filters'] = $filters;
        }

        return new Request($vars);
    }
}
