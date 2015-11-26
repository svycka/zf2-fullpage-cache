<?php

namespace StrokerCacheTest\Strategy;

use StrokerCache\Strategy\ControllerCacheAll;
use Zend\Mvc\MvcEvent;
use Zend\Mvc\Router\RouteMatch;

class ControllerCacheAllTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ControllerName
     */
    private $strategy;

    public function setUp()
    {
        $this->strategy = new ControllerCacheAll();
    }

    /**
     * @return array
     */
    public static function shouldCacheProvider()
    {
        $except = [
            
            'namespaces' => [
                'Namespace\Controller\Console',
                'Namespace\Controller\Debug',
            ],

            'controllers' => [
                'Namespace\Controller\Company\Contact\Index',
                'Namespace\Controller\Company\Mail\Index',
            ],

            'actions' => [
                'Namespace\Controller\Media\Newsletters\Subscribe' => [
                    'index',
                    'another',
                    'more',
                ]
            ],

        ];

        return [

            // test for namespaces

            [ $except, 'Namespace\Controller\Console\Index'                     , null      , false ],
            [ $except, 'Namespace\Controller\Console\Index'                     , 'index'   , false ],
            [ $except, 'Namespace\Controller\Console\Another'                   , null      , false ],
            [ $except, 'Namespace\Controller\Console\Another'                   , 'boo'     , false ],
            [ $except, 'Namespace\Controller\Console\Another\Another\Another'   , null      , false ],
            [ $except, 'Namespace\Controller\Console\Another\Another\Another'   , 'bar'     , false ],

            [ $except, 'Namespace\Controller\Foo\Bar'                           , null      , true  ],
            [ $except, 'Namespace\Controller\Foo\Bar'                           , 'boo'     , true  ],


            // test for controllers

            [ $except, 'Namespace\Controller\Company\Contact\Index'             , null      , false ],
            [ $except, 'Namespace\Controller\Company\Contact\Index'             , 'index'   , false ],
            [ $except, 'Namespace\Controller\Company\Mail\Index'                , null      , false ],
            [ $except, 'Namespace\Controller\Company\Mail\Index'                , 'boo'     , false ],

            [ $except, 'Namespace\Controller\Company\Bar\Index'                 , null      , true  ],
            [ $except, 'Namespace\Controller\Company\Bar\Index'                 , 'bar'     , true  ],


            // test for actions

            [ $except, 'Namespace\Controller\Media\Newsletters\Subscribe'       , 'index'   , false ],
            [ $except, 'Namespace\Controller\Media\Newsletters\Subscribe'       , 'another' , false ],
            [ $except, 'Namespace\Controller\Media\Newsletters\Subscribe'       , 'more'    , false ],

            [ $except, 'Namespace\Controller\Media\Newsletters\Subscribe'       , 'bar'     , true ],


            // and finally

            [ $except, 'Another\Different\Controller'                           , 'bar'     , true ],

        ];
    }

    /**
     * @param array   $except
     * @param string  $requestedController
     * @param boolean $expectedResult
     * @dataProvider shouldCacheProvider
     */
    public function testShouldCache($except, $requestedController, $requestedAction, $expectedResult)
    {
        $this->strategy->setExcept($except);

        $mvcEvent = new MvcEvent();
        $mvcEvent->setControllerClass($requestedController);
        $mvcEvent->setRouteMatch(new RouteMatch(array('controller' => $requestedController, 'action' => $requestedAction)));

        $this->assertEquals($expectedResult, $this->strategy->shouldCache($mvcEvent));
    }

    /**
     * @expectedException StrokerCache\Exception\BadConfigurationException
     */
    public function testShouldCacheException()
    {
        $except = ['missing'=>1];

        $this->strategy->setExcept($except);

        $mvcEvent = new MvcEvent();
        $mvcEvent->setControllerClass('Namespace\Controller\Console');
        $mvcEvent->setRouteMatch(new RouteMatch(array('controller' => 'Boo', 'action' => 'foo')));

        $this->strategy->shouldCache($mvcEvent);
    }

}
