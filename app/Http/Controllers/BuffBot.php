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

class BuffBot extends Controller
{
    private function httpPost($url, $data)
    {
        $curl = curl_init($url);
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query($data));
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        $response = curl_exec($curl);
        curl_close($curl);
        Log::info(var_export($response, true));
        return $response;
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
                $bot->replyText($event->getReplyToken(), "มีคีย์บอร์ดก็พิมตัวหนังสือสิครับ ส่งเชี่ยอะไรมาเนี่ย");
                continue;
            }
            $replyText = $event->getText();
            $mid = $event->getUserId();
            $userProfile = $bot->getProfile($mid);

            if ($userProfile->isSucceeded()) {
                $profile = $userProfile->getJSONDecodedBody();
                $displayName = $profile['displayName'];
            }
            else{
                $displayName = 'ท่านผู้ใช้';
            }

            $postData = [
                'convo_id' => $mid,
                'say' => $replyText,
                'format' => 'html',
                'name' => $displayName
            ];

            $res = $this->httpPost('/buffbot/chatbot/conversation_start.php', $postData);

            if (isset($res) && !is_null($res)){
                $jsonDec = json_decode($res, true);
                $bot->replyText($event->getReplyToken(), $jsonDec['botsay']);
            }
            else{
                $bot->replyText($event->getReplyToken(), 'BuffBot กำลังสับสน รอสักครู่');
            }
        }

        return response('OK', 200);
    }
}