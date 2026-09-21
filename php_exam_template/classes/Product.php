<?php

// OPTIONAL FEATURE: OOP DEMO
// Interface mô tả một hành vi mà class phải cài đặt.
interface Printable
{
    public function printInfo();
}

// Class là khuôn mẫu; object được tạo bằng từ khóa new.
class Product implements Printable
{
    // private: chỉ được dùng bên trong class Product.
    private $name;
    private $price;

    // Constructor chạy khi tạo object mới.
    public function __construct($name, $price)
    {
        $this->name = $name;
        $this->price = $price;
    }

    public function getName()
    {
        return $this->name;
    }

    public function setName($name)
    {
        $this->name = $name;
    }

    public function getPrice()
    {
        return $this->price;
    }

    public function setPrice($price)
    {
        $this->price = $price;
    }

    public function printInfo()
    {
        return $this->name . ' - ' . number_format($this->price, 0, ',', '.') . ' đ';
    }
}

// Ví dụ tạo object:
// $product = new Product('Laptop', 15000000);
// echo $product->getName();
// echo $product->printInfo();

