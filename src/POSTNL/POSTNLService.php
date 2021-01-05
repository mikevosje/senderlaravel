<?php

namespace Wappz\Sender\POSTNL;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use ThirtyBees\PostNL\Entity\Address;
use ThirtyBees\PostNL\Entity\Customer;
use ThirtyBees\PostNL\Entity\Dimension;
use ThirtyBees\PostNL\Entity\Shipment;
use ThirtyBees\PostNL\PostNL;

class POSTNLService
{
    public function sendwithpostnl($data)
    {
        $customer = $data->get('customer');
        $receiver = $data->get('receiver');
        $sender = $data->get('sender');
        $apikey = $customer->service->credentials->apikey;
        $sandbox = true;

        try {
            $postnl = new PostNL($this->createCustomer($customer, $sender), $apikey, $sandbox);
            $barcode = $postnl->generateBarcodeByCountryCode('NL');
            $countrycode = 'NL';
            $postalcode = '5528AW';
            $shipment = Shipment::create([
                'Addresses' => [
                    Address::create([
                        'AddressType' => '01',
                        'City' => $receiver->city,
                        'Countrycode' => $receiver->country,
                        'HouseNr' => $receiver->housenumber,
                        'Name' => $receiver->name,
                        'Street' => $receiver->street,
                        'Zipcode' => $receiver->zipcode,
                    ]),
                ],
                'Barcode' => $barcode,
                'Dimension' => new Dimension('2000'),
                'ProductCodeDelivery' => '3085',
            ]);
            $label = $postnl->generateLabel($shipment, 'GraphicFile|PDF', true);

            //upload label to amazon s3
            Storage::disk('s3')->put(
                'postnl/' . $barcode . '.pdf',
                base64_decode($label->getResponseShipments()[0]->getLabels()[0]->getContent()),
                'public'
            );

            $sending = (new SendingController())->makeSending($customer->id, 'postnl', $barcode, $receiver, $sender);
            if (! $sending) {
                return 'no sending';
            }

            //get
            return [
                'labelurl' => route('label', ['uuid' => $sending->uuid]),
                'barcode' => $barcode,
                'id' => (string) $sending->uuid,
                'carrier' => 'postnl',
                'trackandtraceurl' => route('trackandtrace', ['uuid' => $sending->uuid]),
            ];
        } catch (\Exception $e) {
            Log::error($e, $data->toArray());
            die($e);
        }
    }

    public function createCustomer($customer, $sender)
    {
        return Customer::create([
            'CollectionLocation' => $customer->service->credentials->collectionLocation,
            'CustomerCode' => $customer->service->credentials->customerCode,
            'CustomerNumber' => $customer->service->credentials->customerNumber,
            'ContactPerson' => $sender->name,
            'Address' => Address::create([
                'AddressType' => '02',
                'City' => $sender->city,
                'CompanyName' => $sender->name,
                'Countrycode' => $sender->country,
                'HouseNr' => $sender->housenumber,
                'Street' => $sender->street,
                'Zipcode' => str_replace(' ', '', $sender->zipcode),
            ]),
            'Email' => $sender->email,
            'Name' => $sender->name,
        ]);
    }
}
