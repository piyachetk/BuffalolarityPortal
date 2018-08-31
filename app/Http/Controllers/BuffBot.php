<?php

namespace App\Http\Controllers;

use \App\Alias;
use \Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
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

                    $caption = null;

                    $imageLink = $this->getLatestInstagramImage($id, $caption);

                    if (is_null($imageLink) || empty($imageLink))
                    {
                        $bot->replyText($event->getReplyToken(), 'BuffBot ไม่สามารถหารูปล่าสุดได้ครับ');
                    }

                    $multiMessageBuilder = new LINEBot\MessageBuilder\MultiMessageBuilder();

                    $multiMessageBuilder->add(new ImageMessageBuilder($imageLink, $imageLink ?: ""));

                    if (!empty($caption) && !is_null($caption))
                        $multiMessageBuilder->add(new LINEBot\MessageBuilder\TextMessageBuilder("\"" . $caption . "\""));

                    $bot->replyMessage($event->getReplyToken(), $multiMessageBuilder);
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
                    $caption = null;

                    $videoLink = $this->getLatestInstagramVideo($id, $preview, $caption);

                    if (is_null($videoLink) || empty($videoLink))
                    {
                        $bot->replyText($event->getReplyToken(), 'BuffBot ไม่สามารถหาวีดีโอล่าสุดได้ครับ');
                    }

                    $multiMessageBuilder = new LINEBot\MessageBuilder\MultiMessageBuilder();

                    $multiMessageBuilder->add(new VideoMessageBuilder($videoLink, $preview ?: ""));

                    if (!empty($caption) && !is_null($caption))
                        $multiMessageBuilder->add(new LINEBot\MessageBuilder\TextMessageBuilder("\"" . $caption . "\""));

                    $bot->replyMessage($event->getReplyToken(), $multiMessageBuilder);
                }
                else if ($this->startsWith($command, 'ขอรูปในไอจีจากลิงค์') || $this->startsWith($command, 'ขอภาพในไอจีจากลิงค์'))
                {
                    $link = trim(substr($command, strlen('ขอรูปในไอจีจากลิงค์')));

                    $caption = null;

                    $imageLink = $this->getInstagramImageViaLink($link, $caption);

                    if (is_null($imageLink) || empty($imageLink))
                    {
                        $bot->replyText($event->getReplyToken(), 'BuffBot ไม่สามารถหารูปจากลิงค์ได้ครับ');
                    }

                    $multiMessageBuilder = new LINEBot\MessageBuilder\MultiMessageBuilder();

                    $multiMessageBuilder->add(new ImageMessageBuilder($imageLink, $imageLink ?: ""));

                    if (!empty($caption) && !is_null($caption))
                        $multiMessageBuilder->add(new LINEBot\MessageBuilder\TextMessageBuilder("\"" . $caption . "\""));

                    $bot->replyMessage($event->getReplyToken(), $multiMessageBuilder);
                }
                else if ($this->startsWith($command, 'ขอวีดีโอในไอจีจากลิงค์'))
                {
                    $link = trim(substr($command, strlen('ขอวีดีโอในไอจีจากลิงค์')));

                    $preview = null;
                    $caption = null;

                    $videoLink = $this->getInstagramVideoViaLink($link, $preview, $caption);

                    if (is_null($videoLink) || empty($videoLink))
                    {
                        $bot->replyText($event->getReplyToken(), 'BuffBot ไม่สามารถหาวีดีโอจากลิงค์ได้ครับ');
                    }

                    $multiMessageBuilder = new LINEBot\MessageBuilder\MultiMessageBuilder();

                    $multiMessageBuilder->add(new VideoMessageBuilder($videoLink, $preview ?:  ""));

                    if (!empty($caption) && !is_null($caption))
                        $multiMessageBuilder->add(new LINEBot\MessageBuilder\TextMessageBuilder("\"" . $caption . "\""));

                    $bot->replyMessage($event->getReplyToken(), $multiMessageBuilder);
                }
                else if($this->startsWith($command, 'ขอวาร์ปเด็ดๆ'))
                {
                    $warp = Alias::where('isWarp', true)->orderBy(DB::raw('RAND()'))->first();

                    $caption = null;

                    $imageLink = $this->getRandomInstagramImage($warp->id);

                    if (is_null($imageLink) || empty($imageLink))
                    {
                        $bot->replyText($event->getReplyToken(), 'BuffBot ไม่สามารถหาวาร์ปเด็ดๆได้ครับ');
                    }

                    $multiMessageBuilder = new LINEBot\MessageBuilder\MultiMessageBuilder();

                    $multiMessageBuilder->add(new ImageMessageBuilder($imageLink, $imageLink));

                    if (empty($caption) || is_null($caption))
                        $multiMessageBuilder->add(new LINEBot\MessageBuilder\TextMessageBuilder("IG: " . $warp));
                    else
                        $multiMessageBuilder->add(new LINEBot\MessageBuilder\TextMessageBuilder($id . ": \"" . $caption . "\""));

                    $bot->replyMessage($event->getReplyToken(), $multiMessageBuilder);
                }
                else if($this->startsWith($command, 'ใครสร้างนายขึ้นมา'))
                {
                    $bot->replyText($event->getReplyToken(),"ท่านฮ่องเต้ครับ\nWebsite: https://www.hongtae.net/");
                }
                else{
                    $bot->replyText($event->getReplyToken(), "BuffBot สับสนครับ " . $displayName . "\nตอนนี้ผมรับได้แค่คำสั่งพื้นฐานบางตัวนะครับ");
                }
            }
            else if($replyText === 'BuffBot!')
            {
                $bot->replyText($event->getReplyToken(), 'ครับผม BuffBot ยินดีรับใช้ครับ');
            }
            else if ($isGroup)
            {
                $bot->replyText($event->getReplyToken(), 'BuffBot สับสนครับ' . $displayName);
            }
        }

        return response('OK', 200);
    }

    public function getLatestInstagramImage($id, &$caption)
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

                $link = $media->getLink();
                $json_media_by_url = $instagram->getMediaByUrl($link);
                $caption = $json_media_by_url['caption']['text'];

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

    public function getLatestInstagramVideo($id, &$preview, &$caption)
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

                $caption = $json_media_by_url['caption']['text'];

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

    public function getInstagramImageViaLink($link, &$caption)
    {
        try {
            $instagram = new Instagram();
            $json_media_by_url = $instagram->getMediaByUrl($link);

            $highRes = $json_media_by_url['imageHighResolutionUrl'];
            $stdRes = $json_media_by_url['imageStandardResolutionUrl'];
            $lowRes = $json_media_by_url['imageLowResolutionUrl'];

            $caption = $json_media_by_url['caption']['text'];

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

    public function getInstagramVideoViaLink($link, &$preview, &$caption)
    {
        try {
            $instagram = new Instagram();
            $json_media_by_url = $instagram->getMediaByUrl($link);
            $highRes = $json_media_by_url['videoStandardResolutionUrl'];
            $stdRes = $json_media_by_url['videoLowResolutionUrl'];
            $lowRes = $json_media_by_url['videoLowBandwidthUrl'];

            $preview = $json_media_by_url['imageHighResolutionUrl'];

            $caption = $json_media_by_url['caption']['text'];

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

    public function getRandomInstagramImage($id)
    {
        try {
            $instagram = new Instagram();
            $medias = $instagram->getMedias($id, 1000);

            $randomIndex = rand(0, 999);

            while(empty($medias[$randomIndex]) || is_null($medias[$randomIndex]) || ($medias[$randomIndex]->getType() != 'image' && $medias[$randomIndex]->getType() != 'sidecar'))
            {
                $randomIndex = rand(0, $randomIndex);
            }

            $media = $medias[$randomIndex];

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
        catch(\Exception $exception)
        {
            //
        }

        return null;
    }
}
