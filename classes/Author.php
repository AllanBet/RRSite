<?php

class Author
{
    public int $id = 0;
    public string $first_name;
    public string $last_name;
    public int $grade;

    public function __construct(string $first_name, string $last_name, int $grade)
    {
        $this->first_name = $first_name;
        $this->last_name = $last_name;
        $this->grade = $grade;
    }

}