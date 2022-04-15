<?php

namespace App\Packages\Sender;

class Order
{
    public $order_number = '';
    public $teampartner = '';

    public function __construct(array $data)
    {
        if ($data) {
            foreach ($data as $key => $value) {
                $newkey = str_replace("'", '', $key);
                if (property_exists($this, $newkey)) {
                    $this->{$newkey} = $value;
                }
            }
        }
        $this->checkErrors();
    }

    public function checkErrors()
    {
        $errors = [];
        $checks = ['order_number'];
        foreach ($checks as $check) {
            if (! $this->{$check} || $this->{$check} === '') {
                $errors[$check] = $check . ' cannot be empty';
            }
        }
        if (count($errors) > 0) {
            die(json_encode($errors));
        }
    }

    public function toArray()
    {
        return (array)$this;
    }
}
