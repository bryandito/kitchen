<?php

/**
 * Copyright 2016 LINE Corporation
 *
 * LINE Corporation licenses this file to you under the Apache License,
 * version 2.0 (the "License"); you may not use this file except in compliance
 * with the License. You may obtain a copy of the License at:
 *
 *   https://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS, WITHOUT
 * WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied. See the
 * License for the specific language governing permissions and limitations
 * under the License.
 */

namespace LINE\LINEBot\KitchenSink\EventHandler\MessageHandler;

use LINE\LINEBot;
use LINE\LINEBot\ImagemapActionBuilder\AreaBuilder;
use LINE\LINEBot\ImagemapActionBuilder\ImagemapMessageActionBuilder;
use LINE\LINEBot\ImagemapActionBuilder\ImagemapUriActionBuilder;
use LINE\LINEBot\TemplateActionBuilder\MessageTemplateActionBuilder;
use LINE\LINEBot\TemplateActionBuilder\PostbackTemplateActionBuilder;
use LINE\LINEBot\TemplateActionBuilder\UriTemplateActionBuilder;
use LINE\LINEBot\Event\MessageEvent\TextMessage;
use LINE\LINEBot\KitchenSink\EventHandler;
use LINE\LINEBot\KitchenSink\EventHandler\MessageHandler\Util\UrlBuilder;
use LINE\LINEBot\MessageBuilder\Imagemap\BaseSizeBuilder;
use LINE\LINEBot\MessageBuilder\ImagemapMessageBuilder;
use LINE\LINEBot\MessageBuilder\TemplateMessageBuilder;
use LINE\LINEBot\MessageBuilder\TemplateBuilder\ButtonTemplateBuilder;
use LINE\LINEBot\MessageBuilder\TemplateBuilder\CarouselColumnTemplateBuilder;
use LINE\LINEBot\MessageBuilder\TemplateBuilder\CarouselTemplateBuilder;
use LINE\LINEBot\MessageBuilder\TemplateBuilder\ConfirmTemplateBuilder;

class TextMessageHandler implements EventHandler
{
    /** @var LINEBot $bot */
    private $bot;
    /** @var \Monolog\Logger $logger */
    private $logger;
    /** @var \Slim\Http\Request $logger */
    private $req;
    /** @var TextMessage $textMessage */
    private $textMessage;

    /**
     * TextMessageHandler constructor.
     * @param $bot
     * @param $logger
     * @param \Slim\Http\Request $req
     * @param TextMessage $textMessage
     */
    public function __construct($bot, $logger, \Slim\Http\Request $req, TextMessage $textMessage)
    {
        $this->bot = $bot;
        $this->logger = $logger;
        $this->req = $req;
        $this->textMessage = $textMessage;
    }

    public function handle()
    {
        $text = $this->textMessage->getText();
        $replyToken = $this->textMessage->getReplyToken();
        $this->logger->info("Got text message from $replyToken: $text");

        switch ($text) {
            case 'profile':
                $userId = $this->textMessage->getUserId();
                $this->sendProfile($replyToken, $userId);
                break;
            case 'bye':
                if ($this->textMessage->isRoomEvent()) {
                    $this->bot->replyText($replyToken, 'Leaving room');
                    $this->bot->leaveRoom($this->textMessage->getRoomId());
                    break;
                }
                if ($this->textMessage->isGroupEvent()) {
                    $this->bot->replyText($replyToken, 'Leaving group');
                    $this->bot->leaveGroup($this->textMessage->getGroupId());
                    break;
                }
                $this->bot->replyText($replyToken, 'Bot cannot leave from 1:1 chat');
                break;
            case 'acara':
                $this->bot->replyMessage(
                    $replyToken,
                    new TemplateMessageBuilder(
                        'Confirm alt text',
                        new ConfirmTemplateBuilder('kak,besok tanggal 1 Mei Sisi mengundang kakak ikut acara di Monas.. Kakka bisa datang?', [
                            new MessageTemplateActionBuilder('Bisa', 'Oke'),
                            new MessageTemplateActionBuilder('Maaf Belom Bisa', 'Blm bisa datang'),
                        ])
                    )
                );
                break;
            case 'sos':
                $imageUrl = UrlBuilder::buildUrl($this->req, ['static', 'buttons', '1040.jpg']);
                $buttonTemplateBuilder = new ButtonTemplateBuilder(
                    'Si Emak',
                    'Kenapa nak?',
                    'https://scontent.fcgk10-1.fna.fbcdn.net/v/t1.0-9/27459760_100433814113288_4986703225466090903_n.jpg?oh=7449557fb110f8f7f1d95f196e738f1b&oe=5B24959E',
                    [
                        new UriTemplateActionBuilder('Telephone Penting', 'http://www.organisasi.org/1970/01/nomor-telepon-penting-dan-nomer-telepon-darurat-nasional-di-indonesia.html#.Wnla8-dlPIU'),
                        new UriTemplateActionBuilder('Polisi Terdekat', 'https://line.me'),
                        new MessageTemplateActionBuilder('Menu Help','sos= Untuk keadaan darurat, menu= untuk mengetahui lebih dalam tentang sisi, profile=untuk mengetahui nama asli teman sisi di Line, acara= untuk mengkonfirmasi kikutsertaan dalam acara yang diadakan oleh sisi'),
                    ]
                );
                $templateMessage = new TemplateMessageBuilder('Button alt text', $buttonTemplateBuilder);
                $this->bot->replyMessage($replyToken, $templateMessage);
                break;
            case 'bagi':
                $imageUrl = UrlBuilder::buildUrl($this->req, ['static', 'buttons', '1040.jpg']);
                $buttonTemplateBuilder = new ButtonTemplateBuilder(
                    'Sharing ria,kak...Yuk ke tempat ngumpul para petani kota se-Jabodetabek! Bakal ada workship, bazar, dan bincang2 edukasi kak setiap bulannya. Ikuti https://www.instagram.com/pasarpetanikota/ atau hubungi Whatsapp di 087841568322 ',
                    'https://scontent.fcgk10-1.fna.fbcdn.net/v/t1.0-9/27459760_100433814113288_4986703225466090903_n.jpg?oh=7449557fb110f8f7f1d95f196e738f1b&oe=5B24959E',
                    [
                        new UriTemplateActionBuilder('Agenda', 'https://www.instagram.com/pasarpetanikota/'),
                        new UriTemplateActionBuilder('Omongin aja!', 'https://www.instagram.com/sidebotline/'),
                    ]
                );
                $templateMessage = new TemplateMessageBuilder('Button alt text', $buttonTemplateBuilder);
                $this->bot->replyMessage($replyToken, $templateMessage);
                break;
            case 'menu':
                $imageUrl1 ='https://scontent.fcgk10-1.fna.fbcdn.net/v/t1.0-9/27655508_100433134113356_2707088780596371726_n.jpg?oh=5546a9b9bfb0027f5c751988aa166895&oe=5B1C65A8';
                $imageUrl2 ='https://scontent.fcgk10-1.fna.fbcdn.net/v/t1.0-9/27657884_100433577446645_4074727246332329759_n.jpg?oh=6829c476aed4640477ab98f205caeb42&oe=5AD97A74';
                $imageUrl3 ='https://scontent.fcgk10-1.fna.fbcdn.net/v/t1.0-9/27459379_100432780780058_1793506443162679450_n.jpg?oh=032bc4690a5e4ea692ad6f496e6d12a0&oe=5B115746';                
                $imageUrl4 ='https://scontent.fcgk10-1.fna.fbcdn.net/v/t1.0-9/27750093_100321317457871_5272130745536382177_n.jpg?oh=d56098310228d1df0791618bcbd4a70a&oe=5B143F88';
                $carouselTemplateBuilder = new CarouselTemplateBuilder([
                    new CarouselColumnTemplateBuilder('Kekerasan Perempuan di Tempat Kerja', 'Angka dan Infografik', $imageUrl1, [
                        new UriTemplateActionBuilder('Next', 'https://www.youtube.com/watch?v=wX54cYgqr1g'),
                    ]),
                    new CarouselColumnTemplateBuilder('SOP Bagi Buruh.Apakabar?', 'Berita', $imageUrl2, [
                        new UriTemplateActionBuilder('Next', 'https://www.rappler.com/indonesia/131392-catatan-kelam-buruh-perempuan-2016'),
                    ]),
                    new CarouselColumnTemplateBuilder('Pemenuhan Hak-Hak Buruh Perempuan', 'Fenomena & Tantangan', $imageUrl3, [
                        new UriTemplateActionBuilder('Next', 'http://www.konde.co/2018/01/berjuang-untuk-ruang-laktasi-di-tempat.html'),
                    ]),
                    new CarouselColumnTemplateBuilder('Mengakhiri Kekerasan Perempuan', 'Tips', $imageUrl4, [
                        new UriTemplateActionBuilder('Next', 'https://kumparan.com/@kumparannews/membekali-diri-menghadapi-pelecehan-seksual'),
                    ]),
                    
                ]);
                $templateMessage = new TemplateMessageBuilder('Button alt text', $carouselTemplateBuilder);
                $this->bot->replyMessage($replyToken, $templateMessage);
                break;
            case 'imagemap':
                $richMessageUrl = UrlBuilder::buildUrl($this->req, ['static', 'rich']);
                $imagemapMessageBuilder = new ImagemapMessageBuilder(
                    $richMessageUrl,
                    'This is alt text',
                    new BaseSizeBuilder(1040, 1040),
                    [
                        new ImagemapUriActionBuilder(
                            'https://store.line.me/family/manga/en',
                            new AreaBuilder(0, 0, 520, 520)
                        ),
                        new ImagemapUriActionBuilder(
                            'https://store.line.me/family/music/en',
                            new AreaBuilder(520, 0, 520, 520)
                        ),
                        new ImagemapUriActionBuilder(
                            'https://store.line.me/family/play/en',
                            new AreaBuilder(0, 520, 520, 520)
                        ),
                        new ImagemapMessageActionBuilder(
                            'URANAI!',
                            new AreaBuilder(520, 520, 520, 520)
                        )
                    ]
                );
                $this->bot->replyMessage($replyToken, $imagemapMessageBuilder);
                break;
            default:
                $this->echoBack($replyToken, $text);
                break;
        }
    }

    /**
     * @param string $replyToken
     * @param string $text
     */
    private function echoBack($replyToken, $text)
    {
        $this->logger->info("Returns echo message $replyToken: $text");
        $this->bot->replyText($replyToken, $text);
    }

    private function sendProfile($replyToken, $userId)
    {
        if (!isset($userId)) {
            $this->bot->replyText($replyToken, "Bot can't use profile API without user ID");
            return;
        }

        $response = $this->bot->getProfile($userId);
        if (!$response->isSucceeded()) {
            $this->bot->replyText($replyToken, $response->getRawBody());
            return;
        }

        $profile = $response->getJSONDecodedBody();
        $this->bot->replyText(
            $replyToken,
            'Display name: ' . $profile['displayName'],
            'Status message: ' . $profile['statusMessage']
        );
    }
}
