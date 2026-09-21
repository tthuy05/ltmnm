<?php

// OPTIONAL FEATURE: OOP DEMO - INHERITANCE
class Person
{
    // protected: class con có thể sử dụng thuộc tính này.
    protected $name;

    public function __construct($name)
    {
        $this->name = $name;
    }

    public function getName()
    {
        return $this->name;
    }
}

class Customer extends Person
{
    private $email;

    public function __construct($name, $email)
    {
        parent::__construct($name);
        $this->email = $email;
    }

    public function getEmail()
    {
        return $this->email;
    }
}

// OPTIONAL FEATURE: OOP DEMO - ABSTRACT CLASS
abstract class Payment
{
    abstract public function pay($amount);
}

class CashPayment extends Payment
{
    public function pay($amount)
    {
        return 'Đã thanh toán tiền mặt: ' . number_format($amount, 0, ',', '.') . ' đ';
    }
}

// Ví dụ:
// $customer = new Customer('Nguyễn An', 'an@example.com');
// $payment = new CashPayment();
// echo $customer->getName();
// echo $payment->pay(500000);

