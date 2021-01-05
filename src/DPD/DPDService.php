<?php

namespace Wappz\Sender\DPD;

use Exception;
use Illuminate\Support\Facades\Storage;
use MCS\DPDAuthorisation;
use MCS\DPDShipment;

class DPDService
{
    public function sendwithdpd($data)
    {
        $customer = $data->get('customer');
        $receiver = $data->get('receiver');
        $sender = $data->get('sender');

        try {
            $authorisation = $this->authorisation($customer);
            $shipment = new DPDShipment($authorisation);
            $shipment->setGeneralShipmentData([
                'product' => 'CL',
                'mpsCustomerReferenceNumber1' => 'Test shipment',
            ]);
            $shipment->setTrackingLanguage('nl_NL');
            $shipment->setReceiver($this->returnReceiver($data->get('receiver')));
            $shipment->setSender($this->returnSender($data->get('sender')));
            $shipment->setPrintOptions([
                'printerLanguage' => 'PDF',
                'paperFormat' => 'A6',
            ]);

            $shipment->addParcel([
                'weight' => 4000, // In gram, always 4 kilograms, otherwise bills after delivery
                'height' => 10, // In centimeters
                'width' => 10,
                'length' => 10,
            ]);

            $shipment->submit();
            $response = $shipment->getParcelResponses();
            $barcode = $response[0]['airWayBill'];
            $this->uploadLabel($shipment->getLabels(), $barcode);

            $sending = (new SendingController())->makeSending($customer->id, 'dpd', $barcode, $receiver, $sender);
            if (! $sending) {
                return 'no sending';
            }

            return [
                'labelurl' => route('label', ['uuid' => $sending->uuid]),
                'barcode' => $barcode,
                'id' => (string)$sending->uuid,
                'carrier' => 'dpd',
                'trackandtraceurl' => route('trackandtrace', ['uuid' => $sending->uuid]),
            ];
        } catch (Exception $e) {
            die($e->getMessage());
        }
    }

    public function authorisation($customer)
    {
        if (! $customer->service) {
            die('No DPD service connected for this environment or user');
        }

        try {
//            return new DPDAuthorisation([
//                'staging'         => false,
//                'delisId'         => 'auctioncli',
//                'password'        => '5#O31Y27',
//                'messageLanguage' => 'nl_NL',
//                'customerNumber'  => '05220000000203470'
//            ]);
            return new DPDAuthorisation([
                'staging' => false,
                'delisId' => $customer->service->credentials->delisId,
                'password' => $customer->service->credentials->password,
                'messageLanguage' => 'nl_NL',
                'customerNumber' => $customer->service->credentials->customerNumber,
            ]);
        } catch (Exception $e) {
            die($e->getMessage());
        }
    }

    public function returnSender($data)
    {
        return [
            'name1' => $data->name,
            'street' => $data->street,
            'houseNo' => $data->housenumber,
            'country' => $data->country,
            'zipCode' => str_replace(' ', '', $data->zipcode),
            'city' => $data->city,
            'email' => $data->email,
            'phone' => $data->phone,
        ];
    }

    public function returnReceiver($data)
    {
        return [
            'name1' => $data->name,
            'street' => $data->street,
            'country' => $data->country,
            'zipCode' => str_replace(' ', '', $data->zipcode),
            'city' => $data->city,
            'email' => $data->email,
            'phone' => $data->phone,
            'houseNo' => $data->housenumber,
        ];
    }

    public function uploadLabel($data, $barcode)
    {
        return Storage::disk('s3')->put(
            'dpd/' . $barcode . '.pdf',
            $data,
            'public'
        );
    }
}
