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
use \InstagramScraper\Instagram;
use LINE\LINEBot\MessageBuilder\ImageMessageBuilder;
use LINE\LINEBot\MessageBuilder\VideoMessageBuilder;

class BuffBot extends Controller
{
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

            $isGroup = $event->isGroupEvent();

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

                if ($this->startsWith($command, 'ขอรูปล่าสุดในไอจีของ') || $this->startsWith($command, 'ขอภาพล่าสุดในไอจีของ'))
                {
                    $name = trim(substr($command, strlen('ขอรูปล่าสุดในไอจีของ')));

                    $id = $this->getInstagramIdFromAlias($name);

                    if ($id == '')
                    {
                        $id = $name;
                    }

                    $imageLink = $this->getLatestInstagramImage($id);

                    if (is_null($imageLink) || empty($imageLink))
                    {
                        $bot->replyText($event->getReplyToken(), 'BuffBot ไม่สามารถหารูปล่าสุดได้ครับ');
                    }

                    $imageMessageBuilder = new ImageMessageBuilder($imageLink, $imageLink);

                    $bot->replyMessage($event->getReplyToken(), $imageMessageBuilder);
                }
                else if ($this->startsWith($command, 'ขอวีดีโอล่าสุดในไอจีของ'))
                {
                    $name = trim(substr($command, strlen('ขอวีดีโอล่าสุดในไอจีของ')));

                    $id = $this->getInstagramIdFromAlias($name);

                    if ($id == '')
                    {
                        $id = $name;
                    }

                    $preview = null;

                    $videoLink = $this->getLatestInstagramVideo($id, $preview);

                    if (is_null($videoLink) || empty($videoLink))
                    {
                        $bot->replyText($event->getReplyToken(), 'BuffBot ไม่สามารถหาวีดีโอล่าสุดได้ครับ');
                    }

                    $videoMessageBuilder = new VideoMessageBuilder($videoLink, $preview ?: '');

                    $bot->replyMessage($event->getReplyToken(), $videoMessageBuilder);
                }
                else if ($this->startsWith($command, 'ขอรูปในไอจีจากลิงค์') || $this->startsWith($command, 'ขอภาพในไอจีจากลิงค์'))
                {
                    $link = trim(substr($command, strlen('ขอรูปในไอจีจากลิงค์')));

                    $imageLink = $this->getInstagramImageViaLink($link);

                    if (is_null($imageLink) || empty($imageLink))
                    {
                        $bot->replyText($event->getReplyToken(), 'BuffBot ไม่สามารถหารูปจากลิงค์ได้ครับ');
                    }

                    $imageMessageBuilder = new ImageMessageBuilder($imageLink, $imageLink);

                    $bot->replyMessage($event->getReplyToken(), $imageMessageBuilder);
                }
                else if ($this->startsWith($command, 'ขอวีดีโอในไอจีจากลิงค์'))
                {
                    $link = trim(substr($command, strlen('ขอวีดีโอในไอจีจากลิงค์')));

                    $preview = null;

                    $videoLink = $this->getInstagramVideoViaLink($link, $preview);

                    if (is_null($videoLink) || empty($videoLink))
                    {
                        $bot->replyText($event->getReplyToken(), 'BuffBot ไม่สามารถหาวีดีโอจากลิงค์ได้ครับ');
                    }

                    $videoMessageBuilder = new VideoMessageBuilder($videoLink, $preview ?: '');

                    $bot->replyMessage($event->getReplyToken(), $videoMessageBuilder);
                }
                else if($this->startsWith($command, 'ใครสร้างนายขึ้นมา'))
                {
                    $bot->replyText($event->getReplyToken(),"ท่านฮ่องเต้ครับ\nTwitter: piyachetkk\nWebsite: https://www.buffalolarity.com/");
                }
                else{
                    $bot->replyText($event->getReplyToken(), "BuffBot สับสนครับ" . $displayName);
                }
            }
            else if($replyText === 'BuffBot!')
            {
                $bot->replyText($event->getReplyToken(), 'ครับผม BuffBot ยินดีรับใช้ครับ');
            }
            else if ($isGroup)
            {
                $bot->replyText($event->getReplyToken(), 'BuffBot สับสนครับ');
            }
        }

        return response('OK', 200);
    }

    public function getLatestInstagramImage($id)
    {
        try {
            $instagram = new Instagram();
            $medias = $instagram->getMedias($id, 30);

            foreach ($medias as $media) {
                if ($media->getType() != 'image' && $media->getType() != 'sidecar') {
                    continue;
                }

                $highRes = $media->getImageHighResolutionUrl();
                $stdRes = $media->getImageStandardResolutionUrl();
                $lowRes = $media->getImageLowResolutionUrl();

                if (!empty($highRes) && !is_null($highRes))
                {
                    return $highRes;
                }
                else if (!empty($stdRes) && !is_null($stdRes))
                {
                    return $stdRes;
                }
                else if (!empty($lowRes) && !is_null($lowRes))
                {
                    return $lowRes;
                }
            }
        }
        catch(\Exception $exception)
        {
            //
        }

        return null;
    }

    public function getLatestInstagramVideo($id, &$preview)
    {
        try {
            $instagram = new Instagram();
            $medias = $instagram->getMedias($id, 30);

            foreach ($medias as $media) {
                if ($media->getType() != 'video') {
                    continue;
                }

                $link = $media->getLink();
                $json_media_by_url = $instagram->getMediaByUrl($link);
                $highRes = $json_media_by_url['videoStandardResolutionUrl'];
                $stdRes = $json_media_by_url['videoLowResolutionUrl'];
                $lowRes = $json_media_by_url['videoLowBandwidthUrl'];

                $preview = $media->getImageHighResolutionUrl();

                if (!empty($highRes) && !is_null($highRes))
                {
                    return $highRes;
                }
                else if (!empty($stdRes) && !is_null($stdRes))
                {
                    return $stdRes;
                }
                else if (!empty($lowRes) && !is_null($lowRes))
                {
                    return $lowRes;
                }
            }
        }
        catch(\Exception $exception)
        {
            //
        }

        return null;
    }

    public function getInstagramImageViaLink($link)
    {
        try {
            $instagram = new Instagram();
            $json_media_by_url = $instagram->getMediaByUrl($link);

            $highRes = $json_media_by_url['imageHighResolutionUrl'];
            $stdRes = $json_media_by_url['imageStandardResolutionUrl'];
            $lowRes = $json_media_by_url['imageLowResolutionUrl'];

            if (!empty($highRes) && !is_null($highRes))
            {
                return $highRes;
            }
            else if (!empty($stdRes) && !is_null($stdRes))
            {
                return $stdRes;
            }
            else if (!empty($lowRes) && !is_null($lowRes))
            {
                return $lowRes;
            }
        }
        catch(\Exception $exception)
        {
            //
        }

        return null;
    }

    public function getInstagramVideoViaLink($link, &$preview)
    {
        try {
            $instagram = new Instagram();
            $json_media_by_url = $instagram->getMediaByUrl($link);
            $highRes = $json_media_by_url['videoStandardResolutionUrl'];
            $stdRes = $json_media_by_url['videoLowResolutionUrl'];
            $lowRes = $json_media_by_url['videoLowBandwidthUrl'];

            $preview = $json_media_by_url['imageHighResolutionUrl'];

            if (!empty($highRes) && !is_null($highRes))
            {
                return $highRes;
            }
            else if (!empty($stdRes) && !is_null($stdRes))
            {
                return $stdRes;
            }
            else if (!empty($lowRes) && !is_null($lowRes))
            {
                return $lowRes;
            }
        }
        catch(\Exception $exception)
        {
            //
        }

        return null;
    }

    private function startsWith($command, $match)
    {
        return strpos($command, $match) === 0;
    }

    public function getInstagramIdFromAlias($name)
    {
        foreach(Alias::all() as $alias)
        {
            if ($alias->alias == $name)
            {
                return $alias->id;
            }
        }

        return '';
    }
}