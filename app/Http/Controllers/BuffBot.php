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
        'โม' => 'mobile',
        'เฌอปราง' => 'cherprang',
        'เฌอ' => 'cherprang',
        'มายด์' => 'mind',
        'มาย' => 'mind',
        'มิโอริ' => 'miori',
        'อร' => 'orn',
        'อรอุ๋ง' => 'orn',
        'อุ๋ง' => 'orn',
        'ก่อน' => 'korn',
        'ลุงก่อน' => 'korn',
        'คุณไข่' => 'kaimook',
        'ไข่มุก' => 'kaimook',
        'ปูเป้' => 'pupe',
        'ปู้ป' => 'pupe',
        'ปู๊ป' => 'pupe',
        'บอส' => 'pupe',
        'มิวสิค' => 'music',
        'คุณพระอาทิตย์' => 'music',
        'คุณหมีลิน' => 'namneung',
        'น้ำหนึ่ง' => 'namneung',
        'คุณหมี' => 'namneung',
        'เขียว' => 'kaew',
        'แก้ว' => 'kaew',
        'ครูแก้ว' => 'kaew',
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

            if (strpos($replyText, 'BuffBot! ') === 0)
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

                if (strpos($command, 'ขอรูปล่าสุดในไอจีของ') === 0 || strpos($command, 'ขอภาพล่าสุดในไอจีของ') === 0)
                {
                    $name = trim(substr($command, strlen('ขอรูปล่าสุดในไอจีของ')));

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
                else if($command === 'ใครสร้างนายขึ้นมา')
                {
                    $bot->replyText($event->getReplyToken(),"ท่านฮ่องเต้ครับ\nTwitter: piyachetkk\nWebsite: https://www.buffalolarity.com/");
                }
                else{
                    $bot->replyText($event->getReplyToken(), 'ตอนนี้ BuffBot เป็นแค่รุ่น Prototype นะครับ สามารถขอได้แค่รูปล่าสุดในไอจีครับ ' . $displayName);
                }
            }
            else if($replyText === 'BuffBot!')
            {
                $bot->replyText($event->getReplyToken(), 'ครับผม BuffBot ยินดีรับใช้ครับ');
            }
            else{
                //$bot->replyText($event->getReplyToken(), 'BuffBot สับสนครับ');
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