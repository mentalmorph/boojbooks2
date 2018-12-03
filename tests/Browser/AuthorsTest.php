<?php

namespace Tests\Browser;

use App\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Tests\DuskTestCase;
use Laravel\Dusk\Browser;
use Illuminate\Foundation\Testing\DatabaseMigrations;

class AuthorsTest extends DuskTestCase
{
    use RefreshDatabase;

    protected $user;

    public function setUp()
    {
        parent::setUp();

        $this->user = factory(User::class)->create();
    }

    /**
     * Test author form submission
     */
    public function testAuthorFormSubmission()
    {
        $this->browse(function (Browser $browser) {
            $browser->loginAs($this->user)
                    ->visit('/authors')
                    ->waitFor('')
                    ->type('name', 'Test Tester')
                    ->type('birthday', '2018-12-01')
                    ->type('biography', 'Some test biography.')
                    ->press('Submit')
                    ->assertPathIs('/authors')
                    ->assertSee('Author Added!');
        });
    }
}
