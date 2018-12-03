<?php

namespace Tests\Browser;

use App\Author;
use App\User;
use App\Book;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Tests\DuskTestCase;
use Laravel\Dusk\Browser;
use Illuminate\Foundation\Testing\DatabaseMigrations;

class BooksPage extends DuskTestCase
{
    use DatabaseMigrations;

    /** @var User */
    protected $user;

    /** @var Author */
    protected $author; //Used to relate populate authors dropdown

    public function setUp()
    {
        parent::setUp();

        $this->user = factory(User::class)->create();

        $this->author = factory(Author::class)->create();
    }

    /**
     * Test book form submission
     */
    public function testBooksFormSubmission()
    {
        $this->browse(function (Browser $browser) {
            $browser->loginAs($this->user->id)
                    ->visit('/books')
                    ->type('title', 'Once Upon A Test')
                    ->type('publication_date', '2018-12-01')
                    ->type('description', 'Some test description.')
                    ->type('pages', '300')
                    ->select('author_id', $this->author->id)
                    ->press('Submit')
                    ->assertPathIs('/books')
                    ->assertSee('Book Added!') //Flash success message
                    ->assertSee('Once Upon A Test') //Name in list of Authors
                    ->assertDontSee('Whoops'); //Possible exception dump page
        });
    }

    /**
     * Test book form submission with bad author birthday (not yyyy-mm-dd)
     */
    public function testBookFormSubmissionWithBadDate()
    {
        $this->browse(function (Browser $browser) {
            $browser->loginAs($this->user)
                ->visit('/books')
                ->type('title', 'Once Upon A Test')
                ->type('publication_date', 'NOT IN YYYY-MM-DD FORMAT!')
                ->type('description', 'Some test description.')
                ->type('pages', '300')
                ->select('author_id', $this->author->id)
                ->press('Submit')
                ->assertPathIs('/books')
                ->assertDontSee('Whoops'); //Possible exception dump page
        });
    }

    /**
     * Test book form submission with bad author birthday (not yyyy-mm-dd)
     */
    public function testBookFormSubmissionWithEmptyDatas()
    {
        $this->browse(function (Browser $browser) {
            $browser->loginAs($this->user)
                ->visit('/books')
                ->select('author_id', $this->author->id)
                ->press('Submit')
                ->assertPathIs('/books')
                ->assertDontSee('Whoops'); //Possible exception dump page
        });
    }

    /**
     * Test author form submission with no fields filled out
     */
    public function testBookFormDeletion()
    {
        //Create a book for the sacrifice
        $book = factory(Book::class)->create();

        $this->browse(function (Browser $browser) use ($book) {
            $browser->loginAs($this->user)
                ->visit('/books/delete/'.$book->id) //Would love to click link by id here...
                ->assertPathIs('/books')
                ->assertSee('Book Deleted!')
                ->assertDontSee('Whoops'); //Possible exception dump page
        });
    }
}
