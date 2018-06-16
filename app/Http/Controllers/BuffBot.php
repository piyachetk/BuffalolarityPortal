<?php

namespace App\Http\Controllers;

use \App\Alias;
use \Illuminate\Http\Request;
use \LINE\LINEBot;
use \LINE\LINEBot\HTTPClient\CurlHTTPClient;
use \LINE\LINEBot\Event\MessageEvent;
use \LINE\LINEBot\Event\MessageEvent\TextMessage;
use \LINE\LINEBot\Exception\InvalidEventRequestException;
use \LINE\LINEBot\Exception\InvalidSignatureException;
use Illuminate\Support\Facades\Log;
use LINE\LINEBot\MessageBuilder\ImageMessageBuilder;
use LINE\LINEBot\MessageBuilder\VideoMessageBuilder;
use Mockery\Exception;

class BuffBot extends Controller
{
    protected function processMessage(Request $request){
        $signature = $request->header('X-Line-Signature');
        if (!isset($signature) || is_null($signature)) {
            abort(400);
        }

        $httpClient = new CurlHTTPClient(env('LINE_CHANNEL_TOKEN'));
        $bot = new LINEBot($httpClient, ['channelSecret' => env('LINE_CHANNEL_SECRET')]);

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

            $id = $event->getUserId();
            $userProfile = $bot->getProfile($id);

            if ($userProfile->isSucceeded()) {
                $profile = $userProfile->getJSONDecodedBody();
                $displayName = $profile['displayName'];
            }
            else{
                $displayName = 'ท่านผู้ใช้';
            }

            if ($event->isUserEvent())
            {
                try {
                    $response = file_get_contents('https://chatbot.buffalolarity.com/get?msg=' . $replyText);

                    if (is_null($response) || empty($response)) {
                        $bot->replyText($event->getReplyToken(), 'BuffBot กำลังปรับปรุงระบบอยู่ครับ สามารถติดต่อท่านฮ่องเต้เพื่อสอบถามข้อมูลเพิ่มเติมได้');
                    }

                    $bot->replyText($event->getReplyToken(), $response);
                }
                catch(Exception $ex){
                    $bot->replyText($event->getReplyToken(), 'BuffBot กำลังปรับปรุงระบบอยู่ครับ สามารถติดต่อท่านฮ่องเต้เพื่อสอบถามข้อมูลเพิ่มเติมได้ครับ');
                }
            }
            else
            {
                $bot->replyText($event->getReplyToken(), 'BuffBot ยังไม่เปิดบริการให้ใช้ในแชทกลุ่มเพื่อป้องกันการนำ BuffBot ไปใช้ในการก่อความรำคาญครับ');
            }
        }

        return response('OK', 200);
    }
}
