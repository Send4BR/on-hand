<?php

use Mockery as m;
use Onhand\EloquentBuilderTrait;

class EloquentBuilderTraitTest extends Orchestra\Testbench\TestCase
{
    use EloquentBuilderTrait;

    public function testParametersAreAppliedCorrectly()
    {
        $mock = m::mock('Illuminate\Database\Eloquent\Builder');
        $mock->shouldReceive('with')->once()->with(m::mustBe([
            'children1', 'children2'
        ]));
        $mock->shouldReceive('orderBy')->once()->with('property');
        $mock->shouldReceive('limit')->once()->with(20);
        $mock->shouldReceive('offset')->once()->with(40);
        $mock->shouldReceive('where')->once()->with(m::type('callable'));

        $this->applyResourceOptions($mock, [
            'includes' => ['children1', 'children2'],
            'sort' => 'property',
            'limit' => 20,
            'page' => 2,
            'filter_groups' => [
                [
                    'filters' => [
                        'name:eq(foo)',
                        'name:ct(bar)',
                        'name:!eq(baz)'
                    ]
                ]
            ]
        ]);
    }

    public function testNoParamersAreApplied()
    {
        $mock = m::mock('Illuminate\Database\Eloquent\Builder');

        $this->applyResourceOptions($mock);
    }
}
