<?php

// OPTIONAL FEATURE: OOP DEMO
class Category
{
    private $categoryId;
    private $categoryName;

    public function __construct($categoryId, $categoryName)
    {
        $this->categoryId = $categoryId;
        $this->categoryName = $categoryName;
    }

    public function getCategoryId()
    {
        return $this->categoryId;
    }

    public function getCategoryName()
    {
        return $this->categoryName;
    }
}

