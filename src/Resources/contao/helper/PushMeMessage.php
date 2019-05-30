<?php

/*
 * This file is part of backend-hvb.
 *
 * (c) Dennis Esken - callme@projektorientiert.de
 *
 * @license NO LICENSE - So dont use it without permission (it could be expensive..)
 */

namespace Chuckki\ContaoHvzBundle;

class PushMeMessage
{
    public static function pushMe($msg, $topic = '', $url = '')
    {
        $curMsg = $topic.":\n".$msg."\n".$url;
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'https://pushme.projektorientiert.de/');
        curl_setopt($ch, CURLOPT_POSTFIELDS, [
            'Content-Type' => 'application/x-www-form-urlencoded',
            'key' => 2,
            'msg' => $curMsg,
            'url' => $url,
            'hash' => 'b7050601bca827a1c11264fb05f898e9',
        ]);
        curl_setopt($ch, CURLOPT_SAFE_UPLOAD, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FRESH_CONNECT, true);
        curl_exec($ch);
        curl_close($ch);
    }
}
