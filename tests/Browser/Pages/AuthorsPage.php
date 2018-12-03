<?php

namespace Tests\Browser;

use App\User;
use App\Author;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Tests\DuskTestCase;
use Laravel\Dusk\Browser;
use Illuminate\Foundation\Testing\DatabaseMigrations;

class AuthorsPage extends DuskTestCase
{
    use DatabaseMigrations;

    /** @var User */
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
            $browser->loginAs($this->user->id)
                    ->visit('/authors')
                    ->type('name', 'Test Tester')
                    ->type('birthday', '2018-12-01')
                    ->type('biography', 'Some test biography.')
                    ->press('Submit')
                    ->assertPathIs('/authors')
                    ->assertSee('Author Added!') //Flash success message
                    ->assertSee('Test Tester') //Name in list of Authors
                    ->assertDontSee('Whoops'); //Possible exception dump page, cannot check HTTP code here =(
        });
    }

    /**
     * Test author form submission with bad author birthday (not yyyy-mm-dd)
     */
    public function testAuthorFormSubmissionWithBadDate()
    {
        $this->browse(function (Browser $browser) {
            $browser->loginAs($this->user)
                ->visit('/authors')
                ->type('name', 'Test Tester')
                ->type('birthday', 'NOT IN YYYY-MM-DD FORMAT!')
                ->type('biography', 'Some test biography.')
                ->press('Submit')
                ->assertDontSee('Whoops'); //Possible exception dump page
        });
    }

    /**
     * Test author form submission with no fields filled out
     */
    public function testAuthorFormSubmissionWithEmptyData()
    {
        $this->browse(function (Browser $browser) {
            $browser->loginAs($this->user)
                ->visit('/authors')
                ->press('Submit')
                ->assertDontSee('Whoops'); //Possible exception dump page
        });
    }

    /**
     * Test author deletion
     */
    public function testAuthorDeleteLink()
    {
        //Create a book for the sacrifice
        $book = factory(Author::class)->create();

        $this->browse(function (Browser $browser) use ($book) {
            $browser->loginAs($this->user)
                ->visit('/authors/delete/'.$book->id) //Would love to click link by id here...
                ->assertPathIs('/authors')
                ->assertSee('Author Deleted!')
                ->assertDontSee('Whoops'); //Possible exception dump page
        });
    }
}
