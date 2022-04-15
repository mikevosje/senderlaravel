<?php

namespace App\Packages\Sender;

use Illuminate\Support\Collection;
use App\Packages\Sender\BOL\BolService;
use App\Packages\Sender\GLS\GLSService;

class Sender
{
    public $contact;
    public $platform;

    public static function init(): Sender
    {
        return new self();
    }

    public function setContact(array $contactdata): Sender
    {
        $this->contact = new Contact($contactdata);

        return $this;
    }

    public function setOrder(Order $order): Sender
    {
        $this->order = new Order($order);

        return $this;
    }

    public function setPlatform(array $platform): Sender
    {
        $this->platform = new Platform($platform);

        return $this;
    }

    public function send(): Collection
    {
        switch ($this->platform->partner) {
            case 'bol' :
                return (new BolService($this->order, $this->platform))->sendwithbol();
            case 'dpd' :
                return 'dpd';
            case 'gls':
                return (new GLSService())->sendwithgls($this->order, $this->contact, $this->platform);
            case 'postnl':
                return 'postnl';
            case 'dhl':
                return 'dhl';
        }

        return $this;
    }
}
