<?php

namespace Wappz\Sender;


class Contact
{
    public $full_name = '';
    public $street = '';
    public $house_number = '';
    public $house_number_extension = '';
    public $country_code = '';
    public $zipcode = '';
    public $city = '';
    public $email = '';
    public $phone = '';
    public $company = '';

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
        $checks = ['zipcode', 'country_code', 'street', 'house_number', 'city', 'full_name'];
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
