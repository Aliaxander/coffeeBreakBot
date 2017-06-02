<?php
/**
 * Created by OxGroup.
 * User: Aliaxander
 * Date: 13.01.17
 * Time: 15:20
 *
 * @category  WebhookController
 * @package   OxApp\controllers
 * @author    Aliaxander
 * @license   http://www.gnu.org/copyleft/gpl.html GNU General Public License
 * @link      http://oxgroup.media/
 */

namespace OxApp\controllers;

use Ox\App;
use OxApp\helpers\Config;
use OxApp\models\Bots;
use OxApp\models\CoffeeUsers;
use OxApp\models\Requests;
use OxApp\models\Users;
use function Sodium\add;
use Telegram\Bot\Api;

/**
 * Class WebhookController
 *
 * @package OxApp\controllers
 */
class WebhookController extends App
{
    public function get()
    {
        
        $botId = 2;
        $lang = Config::$lang['ru'];
        $token = Bots::find(['id' => $botId])->rows[0]->api;
        $telegram = new Api($token);
        //print_r($telegram->setWebhook(['url'=>'https://coffe.ebot.biz/webhook']));
        //$telegram->removeWebhook();
        
        $message = $telegram->getWebhookUpdate();
        $chatId = $message->getMessage()->getChat()->getId();
        print_r($telegram->sendMessage([
            'chat_id' => $chatId,
            'text' => json_encode($message->getMessage()) . $message->getMessage()->getText()
        ]));
        
        
        // $message = $telegram->getUpdates();
        
        if ($message->getMessage()->getText() == '/result@CoffeeBreak_bot') {
            $find = CoffeeUsers::find();
            if ($find->count > 0) {
                $result = '';
                $people = [];
                foreach ($find->rows as $row) {
                    $result .= " " . $row->name . " - " . $row->type . "\n";
                    $people[] = $row->name;
                }
                
                print_r($telegram->sendMessage([
                    'chat_id' => $chatId,
                    'text' => $people[mt_rand(0, count($people) - 1)] . " buy coffee:\n" . $result,
                    'reply_markup' => $telegram->replyKeyboardHide(['selective' => true]),
                ]));
            }
            
        } elseif ($message->getMessage()->getReplyToMessage()->getFrom()->getUsername() == 'CoffeeBreak_bot' &&
            $message->getMessage()->getText() != '/start@CoffeeBreak_bot'
        ) {
        
//            print_r($telegram->sendMessage([
//                'chat_id' => $chatId,
//                'text' => "Ok"
//            ]));
            
            $name = $message->getMessage()->getFrom()->getFirstName() . ' ' . $message->getMessage()->getFrom()->getLastName();
            $find = CoffeeUsers::find(['name' => $name]);
            if ($find->count > 0) {
                CoffeeUsers::where(['id' => $find->rows[0]->id])->update(['type' => $message->getMessage()->getText()]);
//                print_r($telegram->sendMessage([
//                    'chat_id' => $chatId,
//                    'text' => "Update"
//                ]));
            } else {
                CoffeeUsers::add(['name' => $name, 'type' => $message->getMessage()->getText()]);
//                print_r($telegram->sendMessage([
//                    'chat_id' => $chatId,
//                    'text' => "Add"
//                ]));
            }
            
        } else {
            $keyboard = [];
            $i = 0;
            $i2 = 0;
            $menu[] = 'Latte';
            $menu[] = 'Cappuccino';
            $menu[] = 'Espresso';
            $menu[] = 'Americano';
            $menu[] = 'Tea';
            $menu[] = 'No, thanks';
            foreach ($menu as $row) {
                if ($i > 2) {
                    $i = 0;
                    $i2++;
                }
                $i++;
                $keyboard[$i2][] = $row;
            }
            
            $reply_markup = $telegram->replyKeyboardMarkup([
                'keyboard' => $keyboard,
                'resize_keyboard' => true,
                'one_time_keyboard' => true,
                //'selective' => true
            ]);
            
            $response = $telegram->sendMessage([
                'chat_id' => $chatId,
                'text' => 'Set coffee:',
                'reply_markup' => $reply_markup,
                //'reply_to_message_id' => $chatId
            ]);
        }
        //
        //        $text = $message->getMessage()->getText();
        //     $userData = $message->getMessage()->getFrom();
        //     if (preg_match("/\/start/", $text)) {
        //         $users = Users::find(['chatId' => $chatId]);
        //         if ($users->count === 0) {
        //             $params = explode(' ', $text);
        //             $params = explode('-', @$params[1]);
        //             Users::add([
        //                 'chatId' => $chatId,
        //                 'webId' => @$params[0],
        //                 'refId' => @$params[2],
        //                 'botId' => $botId,
        //                 'count' => 10,
        //                 'lang' => @$params[1],
        //                 'userData' => json_encode($userData),
        //             ]);
        //             $user = Users::find(['chatId' => $chatId])->rows[0];
        //         }
        //         print_r($telegram->sendMessage([
        //             'chat_id' => $chatId,
        //             'text' => "Set coffee:"
        //         ]));
        //     }
        
    }
    //
    //
    //    public function hideKey($replayTo)
    //    {
    //        $API_KEY = '296504384:AAEFESDASMwjNmneHcDmanAF9nNBO0GA44g';
    //        $telegram = new Api($API_KEY);
    //        $response = $telegram->sendMessage([
    //            'chat_id' => $this->chatId . '@',
    //            'text' => 'Ok.',
    //            'reply_markup' => $telegram->replyKeyboardHide(['selective' => true]),
    //            'reply_to_message_id' => $replayTo
    //        ]);
    //    }
    
    public function post()
    {
        $this->get();
    }
}
