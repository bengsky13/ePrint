<?php

namespace App\Http\Controllers;

use App\Models\SessionModel;
use App\Models\Setting;
use App\Models\Payment;
use League\ColorExtractor\Palette;
use GuzzleHttp;

class ApiController extends Controller
{
    public function __construct()
    {
        sleep(1);
    }
    public function checkSession($id)
    {
        $check = SessionModel::where("sessionId", $id)->first();
        if (!$check) {
            $status = 0;
            SessionModel::create(
                [
                    "sessionId" => $id,
                    "status" => 0
                ]
            );
        } else {
            $status = $check->status;
        }

        $response = array(
            "status" => $status
        );
        if ($status == 2) {
            $coloredPage = array();
            $files = array_diff(scandir('../public/uploads/' . $id . '/photo'), array('..', '.'));
            $x = 1;
            foreach ($files as $file) {
                $palette = Palette::fromFilename('../public/uploads/' . $id . '/photo/' . $file)->getMostUsedColors(5);
                unset($palette[0]);
                unset($palette[16777215]);
                if (count($palette) > 0)
                    array_push($coloredPage, $x);
                $x++;
            }
            $setting = Setting::where("id", 1)->first();
            $page = count($files);
            $output = array(
                "page" => $page,
                "coloredPage" => $coloredPage,
                "price" => array(
                    "bnw" => $page * $setting->bnw,
                    "colored" => ($page * $setting->bnw) + (($setting->colored - $setting->bnw) * count($coloredPage))
                )
            );
            $response = array_merge($response, $output);
        } else if ($status == 3) {
            $payment = Payment::where("session_id", $check->id)->first();
            $secret_key = env('MIDTRANS_SERVER_KEY');
            $path = env("MIDTRANS_ENDPOINT");
            $client = new \GuzzleHttp\Client();
            $curl = $client->request(
                'GET',
                $path . "/v2/" . $payment->trx_id . "/status",
                [
                    "headers" => [
                        "content-type" => "application/json",
                        "Authorization" => "Basic " . base64_encode($secret_key . ":")
                    ]
                ],
            );
            $content = json_decode($curl->getBody());
            if (+$content->status_code == 200) {
                $status = 4;
                $check->status = 4;
                $check->touch();
                $check->save();
                $payment->status = 200;
                $payment->touch();
                $payment->update();
                if ($payment->jenis == 1)
                    $target = "DESK-BNW";
                else if ($payment->jenis == 2)
                    $target = "DESK-COLOR";
                exec("lp ../public/uploads/$id/$id.pdf -d $target");
            }
            $output = array(
                "status_code" => +$content->status_code,
                "qr" => $payment->qr,
                "time" => (strtotime($content->transaction_time) + 120) * 1000,
                "price" => $payment->amount
            );
            $response = array_merge($response, $output);
        } else if ($check->status == 4) {
            $a = exec("lpstat -W not-completed");
            if ($a == "") {
                $check->status = 5;
                $check->touch();
                $check->save();
            }
        }
        return $response;
    }

    public function init()
    {
        exec("rm -rf ../public/uploads/*");
        return array(
            "success" => true
        );
    }

    public function print($id, $type)
    {
        $check = SessionModel::where("sessionId", $id)->first();
        if (!$check) {
            return array(
                "success" => false,
                "msg" => "Wrong URL"
            );
        } else if ($check->status !== 2) {

            return array(
                "success" => false,
                "msg" => "Wrong URL"
            );
        }
        $coloredPage = array();
        $files = array_diff(scandir('../public/uploads/' . $id . '/photo'), array('..', '.'));
        $x = 1;
        foreach ($files as $file) {
            $palette = Palette::fromFilename('../public/uploads/' . $id . '/photo/' . $file)->getMostUsedColors(5);
            unset($palette[0]);
            unset($palette[16777215]);
            if (count($palette) > 0)
                array_push($coloredPage, $x);
            $x++;
        }
        $page = count($files);
        $setting = Setting::where("id", 1)->first();
        $price = $type == 1 ? $page * $setting->bnw : ($page * $setting->bnw) + (($setting->colored - $setting->bnw) * count($coloredPage));
        $secret_key = env('MIDTRANS_SERVER_KEY');
        $textType = $type == 1 ? "B&W" : "COLORED";
        $data = array(
            "payment_type" => "gopay",
            "transaction_details" =>
            array(
                "order_id" => $id,
                "gross_amount" => $price
            ),
            "item_details" => array(
                array(
                    "id" => +$type,
                    "price" => $price,
                    "quantity" => 1,
                    "name" => "PRINT $page PAGE ($textType)"
                )
            ),
            "qris" => array(
                "acquier" => "gopay"
            ),
            "custom_expiry" => array(
                "expiry_duration" => 2,
                "unit" => "minute"
            )
        );
        $path = env("MIDTRANS_ENDPOINT");
        $client = new \GuzzleHttp\Client();
        $response = $client->request(
            'POST',
            $path . "/v2/charge",
            [
                "body" => json_encode($data),
                "headers" => [
                    "content-type" => "application/json",
                    "Authorization" => "Basic " . base64_encode($secret_key . ":")
                ]
            ],
        );
        $content = json_decode($response->getBody());
        if ($content->status_code == 201) {
            $qr = $content->actions[0]->url;
            $trxid = $content->transaction_id;
            Payment::create(
                [
                    "session_id" => $check->id,
                    "amount" => $price,
                    "qr" => $qr,
                    "simulate" => $content->actions[1]->url,
                    "trx_id" => $trxid,
                    "status" => $content->status_code,
                    "jenis" => +$type
                ]
            );
            $check->status = 3;
            $check->touch();
            $check->update();
            return array(
                "success" => true
            );
        }
        return array(
            "success" => false
        );
    }
}