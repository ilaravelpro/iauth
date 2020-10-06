<?php


namespace iLaravel\iAuth\Vendor\AuthBridges;


class Telegram
{
    public $token = '748291172:AAEIyYKV7lISurB-OGUni-B29lia8Mi5km4';
    public $chatId = "206017250";

    public static function send($name, $mobile, $code) {
        static::sendSMS("Code: ".$code);
        return true;
    }

    public static function sendSMS($messgae) {
        return (new self())->sendMSG($messgae);
    }

    public function sendMSG($messgae) {
        dd($messgae);
        return $this->_send('sendMessage', [
            'chat_id' => $this->chatId,
            'text' => $messgae,
            'parse_mode' => "html"
        ]);
    }

    public function _send($method, $datas = [])
    {
        $url = "https://api.telegram.org/bot" . $this->token . "/" . $method;
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $datas);
        $res = curl_exec($ch);
        if (curl_error($ch)) {
            return false;
        } else {
            return true;
        }
    }
}
