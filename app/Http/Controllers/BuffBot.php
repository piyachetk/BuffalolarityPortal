<?php

namespace App\Http\Controllers;

use \Illuminate\Http\Request;
use \LINE\LINEBot;
use \LINE\LINEBot\HTTPClient\CurlHTTPClient;
use \LINE\LINEBot\MessageBuilder\TextMessageBuilder;
use \LINE\LINEBot\Constant\HTTPHeader;
use \LINE\LINEBot\Event\MessageEvent;
use \LINE\LINEBot\Event\MessageEvent\TextMessage;
use \LINE\LINEBot\Exception\InvalidEventRequestException;
use \LINE\LINEBot\Exception\InvalidSignatureException;
use Illuminate\Support\Facades\Log;

class BuffBot extends Controller
{
    protected function processMessage(Request $request){
        $signature = $request->header(HTTPHeader::LINE_SIGNATURE);
        if (empty($signature)) {
            abort(400);
        }

        $httpClient = new CurlHTTPClient('4nACR+eddtK+qgy7r8jX779s9vwyDsa3NKttV/ZyJS4UScLBklTJ67Fn+hA+K9gkEIxftlID070cOZKVF7xDgzA1CamEXAA/AhsA0sKhJz4OWpyn8FhJFYI9RDvsaml1rd41mu0r1HvYSAoPg+wEPQdB04t89/1O/w1cDnyilFU=');
        $bot = new LINEBot($httpClient, ['channelSecret' => '0d1623f60f2a8679726cf5d032564d11 ']);

        try {
            $body = $request->getContent();
            Log::info($body);
            $events = $bot->parseEventRequest($body, $signature[0]);

            Log::info(var_export($events));
            Log::info(count($events));

            foreach ($events as $event) {
                if (!($event instanceof MessageEvent)) {
                    Log::info('Non message event has come');
                    continue;
                }
                if (!($event instanceof TextMessage)) {
                    Log::info('Non text message has come');
                    continue;
                }
                $replyText = $event->getText();
                Log::info('Reply text: ' . $replyText);
                $resp = $bot->replyText($event->getReplyToken(), $replyText);
                Log::info($resp->getHTTPStatus() . ': ' . $resp->getRawBody());
            }

            return response('OK', 200);
        } catch (InvalidSignatureException $e) {
            return abort(400);
        } catch (InvalidEventRequestException $e) {
            return abort(400);
        }
    }
}