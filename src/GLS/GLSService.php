<?php

namespace Wappz\Sender\GLS;

use Carbon\Carbon;
use HTTP_Request2;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Wappz\Sender\Contact;
use Wappz\Sender\Order;
use Wappz\Sender\Platform;

class GLSService
{
    public function sendwithgls(Order $order, Contact $contact, Platform $platform)
    {
        // This sample uses the Apache HTTP client from HTTP Components (http://hc.apache.org/httpcomponents-client-ga/)

        $request = new Http_Request2('https://api.gls.nl/V1/api/Label/Create?api-version=1.0');
        $url     = $request->getUrl();

        $headers = array(
            // Request headers
            'Content-Type'              => 'application/json-patch+json',
            'Ocp-Apim-Subscription-Key' => $platform->data['apikey'],
        );

        $request->setHeader($headers);

        $parameters = array(
            'api-version' => '1.0',
        );

        $url->setQueryVariables($parameters);

        $request->setMethod(HTTP_Request2::METHOD_POST);

        $body = [
            "trackingLinkType"      => "U",
            "labelType"             => "pdf",
            "labelA4StartPosition"  => 0,
            "labelA4MoveXMm"        => 0,
            "labelA4MoveYMm"        => 0,
            "notificationEmail"     => [
                "sendMail"           => true,
                "senderName"         => "Stock and Trade",
                "senderReplyAddress" => "floris@stockandtrade.nl",
                "senderContactName"  => "Floris van den Broek",
                "senderPhoneNo"      => "string",
                "emailSubject"       => "Your order has been shipped!",
            ],
            "returnRoutingData"     => true,
            "addresses"             => [
                "deliveryAddress" => [
                    "addresseeType" => "P",
                    "name1"         => $contact->full_name,
                    "street"        => $contact->street,
                    "houseNo"       => $contact->house_number,
                    "houseNoExt"    => $contact->house_number_extension,
                    "zipCode"       => $contact->zipcode,
                    "city"          => $contact->city,
                    "countryCode"   => $contact->country_code,
                    "contact"       => $contact->full_name,
                    "phone"         => $contact->phone,
                    "email"         => $contact->email
                ]
            ],
            "shippingDate"          => Carbon::now()->format('Y-m-d'),
            "reference"             => $order->order_number,
            "units"                 => [
                [
                    "unitId"                => "A",
                    "weight"                => 5,
                ]
            ],
            "shiptype"              => "P",
            "username"              => $platform->data['email'],
            "password"              => $platform->data['password']
        ];
// Request body
        return json_encode($body);
        $request->setBody(json_encode($body));

        try {
            $response = $request->send();
            echo $response->getBody();
        } catch (HttpException $ex) {
            echo $ex;
        }
    }
}
