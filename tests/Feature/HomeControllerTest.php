<?php

namespace Tests\Feature;

use App\Author;
use App\Book;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\DatabaseMigrations;

class HomeControllerTest extends TestCase
{
    use RefreshDatabase;
    use WithoutMiddleware;

    /**
     * Test Books endpoint. Gather all books in DB.
     */
    public function testBooks()
    {
        $expectedBookCount = 5;

        $books = [];
        for ($i = 1; $i <= $expectedBookCount; $i++) {
            $books[] = factory(Book::class)->create();
        }

        $response = $this->get('books');

        $responseData = $response->getOriginalContent()->getData();

        $this->assertTrue(isset($responseData['books']));
        $this->assertEquals($expectedBookCount, count($responseData['books']));
    }

    /**
     * Test Authors endpoint. Get all Authors in DB.
     */
    public function testAuthors()
    {
        $expectedAuthorCount = 5;

        $books = [];
        for ($i = 1; $i <= $expectedAuthorCount; $i++) {
            $books[] = factory(Author::class)->create();
        }

        $response = $this->get('authors');

        $responseData = $response->getOriginalContent()->getData();

        $this->assertTrue(isset($responseData['authors']));
        $this->assertEquals($expectedAuthorCount, count($responseData['authors']));
    }

    /**
     * Test addition of an author
     */
    public function testAddAuthor()
    {
        $authorData = [
            'name'      => 'Test Tester',
            'birthday'  => '2018-12-01',
            'biography' => 'Some test biography.'
        ];

        //@TODO - REMOVE THIS LINE WHEN CONTROLLER PROPERLY USES REQUEST OBJECT
        $_POST = array_merge($_POST, $authorData);

        //Execute action with request
        $response = $this->post('authors', $authorData);

        //Assert redirect to 'authors' after POST operation
        $response->assertStatus(302);

        //Assert author can be loaded and matches data fed in post
        $author = Author::all()->first();

        $this->assertNotNull($author);
        $this->assertEquals($authorData['name'], $author->name);
        $this->assertEquals($authorData['birthday'], $author->birthday);
        $this->assertEquals($authorData['biography'], $author->biography);
    }

    /**
     * Test addition of an author with invalid input
     * @dataProvider dataProviderTestAddAuthorWithBadInput
     */
    public function testAddAuthorWithBadInput($authorData)
    {
        //@TODO - REMOVE THIS LINE WHEN CONTROLLER PROPERLY USES REQUEST OBJECT
        $_POST = array_merge($_POST, $authorData);

        //Execute action with request
        $response = $this->post('authors', $authorData);

        //Assert redirect after POST operation
        $response->assertStatus(302);
    }

    public function dataProviderTestAddAuthorWithBadInput()
    {
        //Set up request
        $goodPostData = [
            'name'      => 'Test Tester',
            'birthday'  => '2018-12-01',
            'biography' => 'Some test biography.'
        ];

        //Bad author data includes birthday not in expected YYYY-MM-DD format
        $badPostData = $goodPostData;
        $badPostData['birthday'] = 'NOT IN YYYY-MM-DD-FORMAT!'; //This is expected to fail since controller does not handle invalid input

        $emptyPostData = [];

        return [[$badPostData], [$emptyPostData]];
    }

    /**
     * Test deletion of an author
     *
     * @dataProvider dataProviderTestDeleteAuthor
     * @param $authorId
     */
    public function testDeleteAuthor($authorId, $expectedHttpCode, $expectedEmpty)
    {
        /** @var Author $author */
        $author = factory(Author::class)->create();

        $response = $this->get('authors/delete/'.$authorId);

        $response->assertStatus($expectedHttpCode);

        $this->assertEquals($expectedEmpty, Author::all()->isEmpty());
    }

    public function dataProviderTestDeleteAuthor()
    {
        return [
            [1, 302, true],     //Id of Author recently saved
            [9999, 302, false], //Id that will not exist
            ['', 404, false]    //Id that cannot be used in SQL
        ];
    }

    /**
     * Test Author deletion and whether associated books are properly deleted as well
     */
    public function testDeleteAuthorAndBookRelations() {
        $relatedBookCount   = 5;
        $unrelatedBookCount = 5;

        //Create Author
        $author = factory(Author::class)->create();

        //Create several books NOT related to our newly crated Author
        for ($i = 1; $i <= $relatedBookCount; $i++) {
            factory(Book::class)->create();
        }

        //Create another Author
        $anotherAuthor = factory(Author::class)->create();
        for ($i = 1; $i <= $unrelatedBookCount; $i++) {
            factory(Book::class)->create(['author_id' => $anotherAuthor->id]);
        }

        //Delete our first author and all books associated with it
        $this->get('authors/delete/'.$author->id);

        //Assert the second author is still around and their books have not been deleted
        $this->assertTrue(Author::find($anotherAuthor->id));
        $allBooks = Book::all();
        $this->assertEquals($unrelatedBookCount, count($allBooks));

        //Assert sure remaining books are related to second author
        foreach ($allBooks as $book) {
            $this->assertEquals($anotherAuthor->id, $book->author_id);
        }
    }

    /**
     * Test addition of Books and their relationship with Authors
     */
    public function testAddBook()
    {
        $bookData = [
            'title'             => 'Test Book',
            'publication_date'  => '2018-12-01',
            'description'       => 'Some test description',
            'pages'             => 300,
            'author_id'         => 1
        ];

        $author = factory(Author::class)->create();

        //@TODO - REMOVE THIS LINE WHEN CONTROLLER PROPERLY USES REQUEST OBJECT
        $_POST = array_merge($_POST, $bookData);

        //Execute action with request
        $response = $this->post('books', $bookData);

        //Assert redirect after POST operation
        $response->assertStatus(302);

        //Assert book can be loaded and matches data fed in post
        $book = Book::all()->first();

        $this->assertNotNull($book);

        foreach (['title', 'publication_date', 'description', 'pages', 'author_id'] as $param) {
            $this->assertEquals($bookData[$param], $book->$param);
        }
    }

    /**
     * Test addition of Books and their relationship with Authors using bad data
     *
     * @dataProvider dataProviderTestAddBook
     * @param $bookData
     */
    public function testAddBookWithBadInput($bookData)
    {
        //@TODO - REMOVE THIS LINE WHEN CONTROLLER PROPERLY USES REQUEST OBJECT
        $_POST = array_merge($_POST, $bookData);

        //Execute action with request
        $response = $this->post('books', $bookData);

        //Assert redirect after POST operation
        $response->assertStatus(302);
    }

    public function dataProviderTestAddBook()
    {
        //Set up request
        $goodPostData = [
            'title'             => 'Test Book',
            'publication_date'  => '2018-12-01',
            'description'       => 'Some test description',
            'pages'             => 300,
            'author_id'         => 1
        ];

        $badDatePostData = $goodPostData;
        $badDatePostData['publication_date'] = 'NOT IN YYYY-MM-DD-FORMAT!'; //This is expected to fail since controller does not handle invalid input

        $badPagesPostData = $goodPostData;
        $badPagesPostData['pages'] = 'NOT NUMERICAL'; //This is expected to fail since controller does not handle invalid input

        $badAuthorIdPostData = $goodPostData;
        $badAuthorIdPostData['author_id'] = 'NOT NUMERICAL'; //This is expected to fail since controller does not handle invalid input

        $badAuthorIdPostData2 = $goodPostData;
        $badAuthorIdPostData2['author_id'] = 9999; //Author ID that will not appear in testing. This is expected to fail since controller does not handle invalid input

        $badEmptyPostData = []; //Missing all POST params, this is expected to fail

        return [[$badDatePostData], [$badPagesPostData], [$badAuthorIdPostData], [$badAuthorIdPostData2], [$badEmptyPostData]];
    }

    /**
     * Test deletion of books
     *
     * @param $expectedHttpCode
     * @param $expectedEmpty
     */
    public function testDeleteBook()
    {
        $book       = factory(Book::class)->create(); //Associated author created in factory
        $authorId   = $book->author_id;

        $response = $this->get('books/delete/'.$book->id);

        $response->assertStatus(302);

        $this->assertTrue(Book::all()->isEmpty());

        //Assert that associated Author is not deleted with the book
        $this->assertNotNull(Author::find($authorId));
    }

    /**
     * Test deletion of books with bad data
     *
     * @dataProvider dataProviderTestDeleteBookWithBadInput
     * @param $expectedHttpCode
     * @param $expectedEmpty
     */
    public function testDeleteBookWithBadInput($bookId, $expectedHttpCode, $booksEmpty)
    {
        $book       = factory(Book::class)->create(); //Associated author created in factory
        $authorId   = $book->author_id;

        $response = $this->get('books/delete/'.$bookId);

        $response->assertStatus($expectedHttpCode);

        $this->assertEquals($booksEmpty, Book::all()->isEmpty());

        //Assert that associated Author is not deleted with the book
        $this->assertNotNull(Author::find($authorId));
    }

    public function dataProviderTestDeleteBookWithBadInput()
    {
        return [
            [9999, 302, false], //Id that will not exist
            ['', 404, false]    //Id that cannot be used in SQL
        ];
    }
}
