<?php

use tplLib\Request;

include_once 'classes/DTO.php';
include_once 'vendor/tpl.php';
include_once 'vendor/Request.php';

$dto = new DTO();

$request = new Request($_REQUEST);
$cmd = $request->param('cmd')
    ? $request->param('cmd')
    : 'book-list-page';

if($cmd === 'author-list-page'){

    $authors = $dto->getAuthors();


    $data = [
        'authors' => $authors,
        'pageID' => 'author-list-page',
        'path' => 'author-list.html'
    ];

    print renderTemplate('tpl/main.html', $data);

} else if ($cmd === 'author-form-page') {

    $first_name = $_POST['firstName'] ?? '';
    $last_name = $_POST['lastName'] ?? '';
    $grade = $_POST['grade'] ?? 0;

    if ($_SERVER["REQUEST_METHOD"] === "POST") {

        $author = new Author(urlencode($first_name), urlencode($last_name), intval($grade));
        if (strlen($first_name) < 1 or strlen($first_name) > 21) {
            $error = "Pealkiri peab olema rohkem kui 1 tähte ja vähem kui 21";
        } else if (strlen($last_name) < 2 or strlen($last_name) > 22) {
            $error = "Pealkiri peab olema rohkem kui 2 tähte ja vähem kui 22";
        } else {
            if (!is_numeric($grade)) {
                $grade = 1;
            }
            if (empty($error)) {
                $dto->addAuthor($author);
                header("Location: /?cmd=author-list-page&saved=1");
                exit;
            }
        }
    }
    $data = [
        'firstName' => $first_name,
        'lastName' => $last_name,
        'grade' => $grade,
        'author' => $author ?? '',
        'error' => $error ?? '',
        'pageID' => 'author-form-page',
        'path' => 'author-add.html'
    ];
    print renderTemplate('tpl/main.html', $data);

} else if($cmd === 'book-list-page'){

    $books = $dto->getBooks();

    $data = [
        'books' => $books,
        'pageID' => 'book-list-page',
        'path' => 'book-list.html'
    ];
    print renderTemplate('tpl/main.html', $data);

} else if($cmd === 'book-form-page'){

    $title = $_POST['title'] ?? '';
    $author1_id = $_POST["author1"] ?? '';
    $author2_id = $_POST["author2"] ?? '';
    $grade = $_POST['grade'] ?? 1;
    $read = $_POST['isRead'] ?? 0;

    $authors = $dto->getAuthors();

    if ($_SERVER["REQUEST_METHOD"] === "POST") {
        if (strlen($title) < 3 or strlen($title) > 23) {
            $error = "Pealkiri peab olema rohkem kui 3 tähte ja vähem kui 23";
        } if (!is_numeric($author1_id)) {
            $author1_id = 93;
        } if (!is_numeric($author2_id)) {
            $author2_id = 93;
        } if (!is_numeric($grade)) {
            $grade = 1;
        } if (!is_numeric($read)) {
            $read = 0;
        } if (empty($error)) {
            $book = new Book($title, intval($grade), intval($read));

            $dto->addBook($book, $author1_id, $author2_id);
            header("Location: /?cmd=book-list-page&saved=1");
            exit;
        }
    }

    $data = [
        'authors' => $authors,
        'book' => $book ?? '',
        'isRead' => $read,
        'title' => $title,
        'mark' => $grade,
        'error' => $error ?? '',
        'pageID' => 'book-form-page',
        'path' => 'book-add.html'
    ];
    print renderTemplate('tpl/main.html', $data);

} else if($cmd === 'book-edit-page'){

    $book = $dto->getBookById($_GET['id']);
    $author_ids = $dto->getAuthorId($_GET['id']);
    $authorid1 = $author_ids[0] ?? 1;
    $authorid2 = $author_ids[1] ?? 1;

    $authors = $dto->getAuthors();

    $title = $_POST['title'] ?? urldecode($book->title);
    $grade = $_POST['grade'] ?? $book->grade;
    $read = $_POST['isRead'] ?? $book->read;
    $author1_id = $_POST["author1"] ?? 72;
    $author2_id = $_POST["author2"] ?? 72;

    if (isset($_POST["deleteButton"])) {
        $dto->removeBook($book->id);
        header("Location: /?cmd=book-list-page&deleted=1");
    } elseif (isset($_POST["submitButton"])) {

        if (!isset($_POST['isRead'])) {
            $read = 0;
        } else {
            $read = 1;
        }

        $edited_book = new Book(urlencode($title), intval($grade), $read);
        $edited_book->id = $_GET['id'];
        $dto->editBook($edited_book, $author1_id, $author2_id);

        header("Location: /?cmd=book-list-page&saved=1");
    }

    $data = [
        'author1' => $authorid1,
        'author2' => $authorid2,
        'authors' => $authors,
        'book' => $book,
        'title' => $title,
        'read' => $read,
        'pageID' => 'book-edit-page',
        'path' => 'book-edit.html'
    ];
    print renderTemplate('tpl/main.html', $data);

}else if($cmd === 'author-edit-page'){

    $author = $dto->getAuthorById($_GET['id']);

    $first_name = $_POST['firstName'] ?? urldecode($author->first_name);
    $last_name = $_POST['lastName'] ?? urldecode($author->last_name);
    $grade = $_POST['grade'] ?? $author->grade;

    if ($_SERVER["REQUEST_METHOD"] === "POST") {

        if (isset($_POST["deleteButton"])) {
            $dto->removeAuthor($author->id);
            header("Location: /?cmd=author_book-list-page&deleted=1");
        } elseif (isset($_POST["submitButton"])) {
            $edited_author = new Author(urlencode($first_name), urlencode($last_name), intval($grade));
            $edited_author->id = $author->id;
            $dto->editAuthor($edited_author);
            header("Location: /?cmd=author-list-page&saved=1");
        }
    }

    $data = [
        'firstName' => $first_name,
        'lastName' => $last_name,
        'author' => $author,
        'grade' => $grade,
        'pageID' => 'author-edit-page',
        'path' => 'author-edit.html'
    ];
    print renderTemplate('tpl/main.html', $data);

}else{

    $books = $dto->getBooks();
    $data = [
        'books' => $books,
        'saved' => $_GET['saved'] ?? '',
        'deleted' => $_GET['deleted'] ?? '',
        'pageID' => 'book-list-page',
        'path' => 'book-list.html'
    ];
    print renderTemplate('tpl/main.html', $data);

}