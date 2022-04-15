<?php

namespace App\Packages\Sender;

class Platform
{
    public $partner = '';
    public $data = '';
    public $shippingLabelOfferId = null;

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
        $checks = ['partner'];
        if ($this->partner === 'bol') {
            $checks[] = 'shippingLabelOfferId';
        } else {
            $checks[] = 'data';
        }
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
