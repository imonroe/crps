<?php

namespace Tests\Unit;

use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use imonroe\crps\Aspect;

class AspectTest extends TestCase
{
    use \Illuminate\Foundation\Testing\DatabaseMigrations;
    use RefreshDatabase;

    protected $test_aspect;

    protected function setUp()
    {
        parent::setUp();
        //Artisan::call('migrate');
        $this->test_aspect = new Aspect;
    }

    protected function tearDown()
    {
        //Artisan::call('migrate:reset');
        parent::tearDown();
    }

    
    public function testCreation()
    {
        
        $this->test_aspect->aspect_type = 0;
        $this->test_aspect->title = 'Unit Testing Aspect';
        $this->test_aspect->aspect_data = 'Unit Testing Aspect data';
        // $this->test_aspect->aspect_notes
        $this->test_aspect->aspect_source = 'Unit testing aspect source';
        $this->test_aspect->hidden = 1;
        $this->test_aspect->folded = 0;
        $this->test_aspect->user = 1;
        $this->test_aspect->save();

        $test_id = $this->test_aspect->id;
        $loaded_aspect = new Aspect;
        $loaded_aspect->id = $test_id;
        $loaded_aspect->manual_load();


        $this->assertDatabaseHas('aspects', ['title' => 'Unit Testing Aspect']);
        //$this->assertEquals(0, $loaded_aspect->id);
        $this->assertEquals('Unit Testing Aspect', $loaded_aspect->title);
        $this->assertEquals('Unit Testing Aspect data', $loaded_aspect->aspect_data);
        $this->assertEquals('Unit testing aspect source', $loaded_aspect->aspect_source);
        $this->assertEquals(1, $loaded_aspect->hidden);
        $this->assertEquals(0, $loaded_aspect->folded);
        $this->assertEquals(1, $loaded_aspect->user);
        $this->assertEquals(100, $loaded_aspect->display_weight);
        $this->assertTrue(is_numeric(strtotime($loaded_aspect->created_at)));
        $this->assertTrue(is_numeric(strtotime($loaded_aspect->updated_at)));

    }
}
