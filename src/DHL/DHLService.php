<?php

namespace Wappz\Sender\DHL;

use Mvdnbrk\DhlParcel\Client;
use Mvdnbrk\DhlParcel\Resources\Parcel;

class DHLService
{
    public function sendwithdhl($data)
    {
        $dhlparcel = new Client();
        $dhlparcel->setUserId('your-user-id');
        $dhlparcel->setApiKey('your-api-key');
        $dhlparcel->setAccountId('123456');

        $parcel = new Parcel([
            'reference' => 'your own reference for the parcel (optional)',
            'recipient' => [
                'first_name'    => 'John',
                'last_name'     => 'Doe',
                'street'        => 'Poststraat',
                'number'        => '1',
                'number_suffix' => 'A',
                'postal_code'   => '1234AA',
                'city'          => 'Amsterdam',
                'cc'            => 'NL',
            ],
            'sender'    => [
                'company_name' => 'Your Company Name',
                'street'       => 'Pakketstraat',
                'number'       => '99',
                'postal_code'  => '9999AA',
                'city'         => 'Amsterdam',
                'cc'           => 'NL',
            ],
            // Optional. This will be set as the default.
            'pieces'    => [
                [
                    'parcel_type' => \Mvdnbrk\DhlParcel\Resources\Piece::PARCEL_TYPE_SMALL,
                    'quantity'    => 1,
                ],
            ],
        ]);

        $shipment = $dhlparcel->shipments->create($parcel);

        $shipment->id;
// For shipments with multiple pieces:
        $shipment->pieces->each(function ($item) {
            $item->label_id;
            $item->barcode;
        });
// For a shipment with one single piece:
        $shipment->label_id;
        $shipment->barcode;
    }
}
