<?php

namespace App\Packages\Sender\GLS;

use Carbon\Carbon;
use HTTP_Request2;
use Symfony\Component\HttpKernel\Exception\HttpException;
use App\Packages\Sender\Contact;
use App\Packages\Sender\Models\Sending;
use App\Packages\Sender\Order;
use App\Packages\Sender\Platform;

class GLSService
{
    public function sendwithgls(Order $order, Contact $contact, Platform $platform)
    {
        // This sample uses the Apache HTTP client from HTTP Components (http://hc.apache.org/httpcomponents-client-ga/)

        $request = new Http_Request2('https://api.gls.nl/Test/V1/api/Label/Create?api-version=1.0');
        $url     = $request->getUrl();

        $headers = [
            // Request headers
            'Content-Type' => 'application/json-patch+json',
            'Ocp-Apim-Subscription-Key' => $platform->data['apikey'],
        ];

        $request->setHeader($headers);

        $parameters = [
            'api-version' => '1.0',
        ];

        $url->setQueryVariables($parameters);

        $request->setMethod(HTTP_Request2::METHOD_POST);

        $body = [
            "trackingLinkType"  => "U",
            "labelType"         => "pdf",
            "notificationEmail" => [
                "sendMail"           => true,
                "senderName"         => "Stock and Trade",
                "senderReplyAddress" => "floris@stockandtrade.nl",
                "senderContactName" => "Floris van den Broek",
                "senderPhoneNo" => "string",
                "emailSubject" => "Your order has been shipped!",
            ],
            "returnRoutingData" => true,
            "addresses"         => [
                "deliveryAddress" => [
                    "addresseeType" => "P",
                    "name1" => $contact->full_name,
                    "street" => $contact->street,
                    "houseNo" => $contact->house_number,
                    "houseNoExt" => $contact->house_number_extension,
                    "zipCode" => $contact->zipcode,
                    "city" => $contact->city,
                    "countryCode" => $contact->country_code,
                    "contact" => $contact->full_name,
                    "phone" => $contact->phone,
                    "email" => $contact->email,
                ],
            ],
            "shippingDate"      => Carbon::now()->format('Y-m-d'),
            "reference"         => $order->order_number,
            "units"             => [
                [
                    "unitId" => "A",
                    "weight" => 5,
                ]
            ],
            "shiptype"          => "P",
            "username"          => $platform->data['email'],
            "password"          => $platform->data['password']
        ];
        // Request body
        $request->setBody(json_encode($body));

        try {
            $response = $request->send();

            return $this->parseData($response->getBody());
        } catch (HttpException $ex) {
            echo $ex;
        }
    }

    public function parseData($data)
    {
        $newdata  = json_decode($data);
        $sendings = [];
        if ($newdata->error === false) {
            foreach ($newdata->units as $unit) {
                $sending = Sending::create([
                    'carrier'       => 'gls',
                    'barcode'       => $unit->unitNo,
                    'packagenumber' => $unit->uniqueNo,
                    'trackinglink'  => $unit->unitTrackingLink,
                    'zipcode'       => $unit->routingData->zipCode
                ]);
                $sending->addMediaFromBase64($unit->label,
                    'application/pdf')->usingName($unit->uniqueNo)->usingFileName($unit->uniqueNo . '.pdf')->toMediaCollection('gls');
                $sendings[] = $sending;
            }
        }

        return collect($sendings);
    }
}
