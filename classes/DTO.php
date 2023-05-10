<?php
include_once __DIR__ . '/Author.php';
include_once __DIR__ . '/Book.php';
include_once __DIR__ . '/DB.php';

class DTO
{
    private $conn;

    public function __construct()
    {
        $this->conn = DB::getInstance();
    }

    public function getAuthors(): array
    {
        $authors_array = [];

        $query = "SELECT * FROM authors ORDER BY id";
        $stmt = $this->conn->query($query);

        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $author = new Author(urldecode($row["first_name"]), urldecode($row["last_name"]), $row["grade"]);
            $author->id = $row["id"];
            $authors_array[] = $author;
        }

        return $authors_array;
    }

    public function addAuthor(Author $author)
    {
        $query = "INSERT INTO authors (first_name, last_name, grade) VALUES (?, ?, ?)";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([$author->first_name, $author->last_name, $author->grade]) or die('Autori lisamine ebaõnnestus: ' . $this->conn->errorInfo()[2]);
    }

    public function getBooks(): array
    {
        $books_array = [];

        $sql = "SELECT books.id, books.title, mandatory_author.first_name AS mandatory_first_name, mandatory_author.last_name AS mandatory_last_name,
        optional_author.first_name AS optional_first_name, optional_author.last_name AS optional_last_name, books.grade, books.isRead
        FROM books
        LEFT JOIN authors AS mandatory_author ON books.mandatory_author_id = mandatory_author.id
        LEFT JOIN authors AS optional_author ON books.optional_author_id = optional_author.id
        ORDER BY books.id";


        $stmt = $this->conn->query($sql);

        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $author1 = urldecode($row["mandatory_first_name"] .' '. $row["mandatory_last_name"]);
            $author2 = urldecode($row["optional_first_name"] .' '. $row["optional_last_name"]);
            $book = new Book(urldecode($row["title"]), $row["grade"], $row["isRead"]);
            $book->title = urldecode($book->title);
            $book->id = $row["id"];
            $book->author1_name = [$author1];
            $book->author2_name = [$author2];
            $books_array[] = $book;

        }
        return $books_array;
    }

    public function addBook(Book $book, $author1, $author2)
    {

        if ($author1 === '') {
            $author1 = 52;
        }
        if ($author2 === '') {
            $author2 = 52;
        }

        $stmt = $this->conn->prepare("INSERT INTO books (title, mandatory_author_id, optional_author_id, grade, isRead) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([urlencode($book->title), intval($author1), intval($author2), $book->grade, $book->read]) or die('Autori lisamine ebaõnnestus: '. $conn->errorInfo()[2]);
        $book->title = urldecode($book->title);
    }

    public function editBook(Book $book, $author1, $author2)
    {
        if ($author1 === ''){
            $author1 = 52;
        }
        if ($author2 === '') {
            $author2 = 52;
        }

        $stmt = $this->conn->prepare("UPDATE books SET title = :title, mandatory_author_id = :author1, optional_author_id = :author2, grade = :rating, isRead = :isRead WHERE id = :id");
        $stmt->bindParam(':title', $book->title);
        $stmt->bindParam(':author1', $author1);
        $stmt->bindParam(':author2', $author2);
        $stmt->bindParam(':rating', $book->grade);
        $stmt->bindParam(':isRead', $book->read);
        $stmt->bindParam(':id', $book->id);
        $stmt->execute() or die('Autori muutmine ebaõnnestus: '. $conn->errorInfo()[2]);
    }

    public function editAuthor(Author $author)
    {
        $query = "UPDATE authors SET first_name = ?, last_name = ?, grade = ? WHERE id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([$author->first_name, $author->last_name, $author->grade, $author->id]) or die('Autori muutmine ebaõnnestus: ' . $conn->errorInfo()[2]);
    }

    public function removeBook($search_id)
    {
        $stmt = $this->conn->prepare("DELETE FROM books WHERE id = :id");
        $stmt->bindParam(':id', $search_id);
        $stmt->execute() or die('Autori kustutamine ebaõnnestus: '. $conn->errorInfo()[2]);
    }

    public function removeAuthor($search_id)
    {
        $query = "DELETE FROM authors WHERE id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([$search_id]) or die('Autori kustutamine ebaõnnestus: ' . $conn->errorInfo()[2]);
    }

    public function getBookById($search_id)
    {
        $books_array = $this->getBooks();

        foreach ($books_array as $book) {
            if ($book->id == $search_id):
                return $book;
            endif;
        }
        return null;
    }

    public function getAuthorById($search_id)
    {
        $authors_array = $this->getAuthors();

        foreach ($authors_array as $author) {
            if ($author->id == $search_id):
                return $author;
            endif;
        }
        return null;
    }

    public function getAuthorId($search_id): ?array
    {
        $ids = [];

        $query = "SELECT mandatory_author_id, optional_author_id
              FROM books WHERE books.id = ?";

        $stmt = $this->conn->prepare($query);
        $stmt->execute([$search_id]);

        $author_ids = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($author_ids as $author) {
            $ids[] = $author;
        }

        if (empty($ids)) {
            return null;
        }

        return $ids;
    }


}