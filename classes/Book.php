<?php

class Book
{
    public int $id = 0;
    public string $title;
    public array $author1_name;
    public array $author2_name;
    public array $author_id;
    public int $grade;
    public int $read;

    public function __construct(string $title, int $grade, int $read)
    {
        $this->title = $title;
        $this->grade = $grade;
        $this->read = $read;
    }
}