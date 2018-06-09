<?php

namespace App\Http\Controllers;

use \Illuminate\Http\Request;
use \LINE\LINEBot;
use \LINE\LINEBot\HTTPClient\CurlHTTPClient;
use \LINE\LINEBot\Event\MessageEvent;
use \LINE\LINEBot\Event\MessageEvent\TextMessage;
use \LINE\LINEBot\Exception\InvalidEventRequestException;
use \LINE\LINEBot\Exception\InvalidSignatureException;
use Illuminate\Support\Facades\Log;
use \InstagramScraper\Instagram;

class BuffBot extends Controller
{
    public static $ids = [
        'โมบายล์' => 'mobile',
        'โมไบล์' => 'mobile',
        'โมบาย' => 'mobile',
        'โมไบ' => 'mobile',
        'โม' => 'mobile',
        'เณอปราง' => 'cherprang',
        'เณอ' => 'cherprang',
        'มายด์' => 'mind',
        'มาย' => 'mind'
    ];

    private function httpPost($url, $data)
    {
        $options = array(
            'http' => array(
                'header'  => "Content-type: application/x-www-form-urlencoded\r\n",
                'method'  => 'POST',
                'content' => http_build_query($data)
            )
        );
        $context  = stream_context_create($options);
        $result = file_get_contents($url, false, $context);

        Log::info($result);
        return $result;
    }

    protected function processMessage(Request $request){
        $signature = $request->header('X-Line-Signature');
        if (!isset($signature) || is_null($signature)) {
            abort(400);
        }

        $httpClient = new CurlHTTPClient('4nACR+eddtK+qgy7r8jX779s9vwyDsa3NKttV/ZyJS4UScLBklTJ67Fn+hA+K9gkEIxftlID070cOZKVF7xDgzA1CamEXAA/AhsA0sKhJz4OWpyn8FhJFYI9RDvsaml1rd41mu0r1HvYSAoPg+wEPQdB04t89/1O/w1cDnyilFU=');
        $bot = new LINEBot($httpClient, ['channelSecret' => '0d1623f60f2a8679726cf5d032564d11']);

        try {
            $events = $bot->parseEventRequest($request->getContent(), $signature);
        } catch (InvalidSignatureException $e) {
            return abort(400);
        } catch (InvalidEventRequestException $e) {
            return abort(400);
        }

        foreach ($events as $event) {

            if (!($event instanceof MessageEvent) || !($event instanceof TextMessage)) {
                //$bot->replyText($event->getReplyToken(), "มีคีย์บอร์ดก็พิมตัวหนังสือสิครับ ส่งเชี่ยอะไรมาเนี่ย");
                continue;
            }

            $replyText = $event->getText();

            if (substr($replyText,0,9) === "BuffBot! ")
            {
                $id = $event->getUserId();
                $userProfile = $bot->getProfile($id);

                if ($userProfile->isSucceeded()) {
                    $profile = $userProfile->getJSONDecodedBody();
                    $displayName = $profile['displayName'];
                }
                else{
                    $displayName = 'ท่านผู้ใช้';
                }

                $command = substr($replyText, 9);

                if (substr($command, 0, '20') === 'ขอรูปล่าสุดในไอจีของ')
                {
                    $name = trim(substr($command, 20));

                    $id = $this->getBNKInstagramIdFromName($name);

                    if ($id == '')
                    {
                        $id = $name;
                    }

                    $imageLink = $this->getLatestInstagramPhoto($id);

                    if (is_null($imageLink))
                    {
                        $bot->replyText($event->getReplyToken(), 'BuffBot ไม่สามารถหารูปล่าสุดได้ครับ');
                    }

                    $imageMessageBuilder = new \LINE\LINEBot\MessageBuilder\ImageMessageBuilder($imageLink, $imageLink);

                    $bot->replyMessage($event->getReplyToken(), $imageMessageBuilder);
                }
                else{
                    $bot->replyText($event->getReplyToken(), 'BuffBot สับสนครับ ' . $displayName . ' ตอนนี้ BuffBot เป็นแค่รุ่น Prototype นะครับ สามารถขอได้แค่รูปล่าสุดในไอจีครับ');
                }
            }
        }

        return response('OK', 200);
    }

    public function getLatestInstagramPhoto($id)
    {
        try {
            $instagram = new Instagram();
            $medias = $instagram->getMedias($id, 10);

            foreach ($medias as $media) {
                if ($media->getType() != "image") {
                    continue;
                }

                return $media->getImageHighResolutionUrl();
            }
        }
        catch(\Exception $exception)
        {
            //
        }

        return null;
    }

    private function getBNKInstagramIdFromName($name)
    {
        foreach(self::$ids as $key => $value)
        {
            if ($name == $key)
            {
                return $value . '.bnk48official';
            }
        }

        return '';
    }
}