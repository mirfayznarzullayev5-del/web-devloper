<?php
require_once __DIR__ . '/telegram.php';
require_once __DIR__ . '/authlib.php';
require_once __DIR__ . '/users.php';
require_once __DIR__ . '/advisor.php';

function bot_session_path($chatId): string
{
    $dir = __DIR__ . '/storage/sessions';
    if (!is_dir($dir)) mkdir($dir, 0755, true);
    return $dir . '/' . preg_replace('/[^0-9\-]/', '', (string)$chatId) . '.json';
}
function bot_load($chatId): array
{
    $file = bot_session_path($chatId);
    if (!is_file($file)) return ['step' => 'idle', 'data' => []];
    $json = json_decode((string)file_get_contents($file), true);
    return is_array($json) ? $json : ['step' => 'idle', 'data' => []];
}
function bot_save($chatId, array $state): void
{
    file_put_contents(bot_session_path($chatId), json_encode($state, JSON_UNESCAPED_UNICODE));
}
function bot_kb(array $rows): array
{
    return [
        'keyboard' => array_map(fn($row) => array_map(fn($t) => ['text' => $t], $row), $rows),
        'resize_keyboard' => true,
        'one_time_keyboard' => true,
    ];
}
function bot_send($chatId, $text, $markup = null): void
{
    $params = [
        'chat_id' => $chatId,
        'text' => $text,
        'parse_mode' => 'HTML',
    ];
    if ($markup) $params['reply_markup'] = is_string($markup) ? $markup : json_encode($markup, JSON_UNESCAPED_UNICODE);
    telegram_api('sendMessage', $params);
}

require_once __DIR__ . '/contract.php';
require_once __DIR__ . '/chatlib.php';
require_once __DIR__ . '/users.php';

function process_update(array $update): void
{
    $cb = $update['callback_query'] ?? null;
    if ($cb) {
        $chatId = (string)($cb['message']['chat']['id'] ?? '');
        $dataCb = (string)($cb['data'] ?? '');
        $username = (string)($cb['from']['username'] ?? '');
        if ($username) { require_once __DIR__.'/users.php'; tgmap_set($username, $chatId); }
        require_once __DIR__ . '/users.php';

        if ($dataCb === 'contract_yes') {
            telegram_api('answerCallbackQuery', ['callback_query_id'=>$cb['id'], 'text'=>'Rahmat!']);
            bot_send($chatId, contract_text());
            bot_send($chatId, "🎉 Shartnoma qabul qilindi!\n\n🛒 Davom etish: <b>Buyurtma berish</b> yoki /start");
            telegram_notify_admin([
                'name' => $cb['from']['first_name'] ?? 'Mijoz',
                'phone' => '-',
                'telegram' => $username ? '@'.$username : '-',
                'type' => 'SHARTNOMA DA',
                'budget' => '-',
                'description' => 'Mijoz shartnomaga ROZI',
            ]);
            return;
        }
        if ($dataCb === 'contract_no') {
            telegram_api('answerCallbackQuery', ['callback_query_id'=>$cb['id'], 'text'=>'Tushunarli']);
            bot_send($chatId, "😔 Tushunarli. Qayta o'ylasangiz — /start\nBiz doimo yordamga tayyormiz 💜");
            telegram_notify_admin([
                'name' => $cb['from']['first_name'] ?? 'Mijoz',
                'phone' => '-',
                'telegram' => $username ? '@'.$username : '-',
                'type' => 'SHARTNOMA NET',
                'budget' => '-',
                'description' => 'Mijoz shartnomani RAD etdi',
            ]);
            return;
        }
        if (strpos($dataCb, 'accept_') === 0) {
            $uid = substr($dataCb, 7);
            $all = users_all();
            foreach ($all['users'] as &$uu) {
                if (($uu['id']??'')===$uid) {
                    $uu['pending'] = 0;
                    $uu['tg_chat'] = $chatId;
                    $uu['username'] = $username ?: ($uu['username']??'');
                }
            }
            users_save($all);
            telegram_api('answerCallbackQuery', ['callback_query_id'=>$cb['id'], 'text'=>'Принято']);
            $url = env('SITE_URL', 'http://localhost:8000') . '/cabinet.php';
            bot_send($chatId, "Qabul qilindi. Saytga o'ting:\n".$url);
            return;
        }
        if (strpos($dataCb, 'allow_') === 0) {
            $token = substr($dataCb, 6);
            $all = users_all();
            if (!empty($all['tokens'][$token]) && empty($all['tokens'][$token]['ok'])) {
                $all['tokens'][$token]['ok'] = true;
                $all['tokens'][$token]['chat'] = $chatId;
                $all['users'][] = ['via'=>'bot','chat'=>$chatId,'at'=>date('c')];
                users_save($all);
                $sf = __DIR__ . '/storage/stats.json';
                $st = is_file($sf) ? json_decode((string)file_get_contents($sf), true) : [];
                if (!is_array($st)) $st = [];
                $st['bots'] = (int)($st['bots'] ?? 0);
                $st['bot_web_regs'] = (int)($st['bot_web_regs'] ?? 0) + 1;
                file_put_contents($sf, json_encode($st, JSON_UNESCAPED_UNICODE));
                telegram_notify_admin([
                    'name' => $cb['from']['first_name'] ?? 'Mijoz',
                    'phone' => '-',
                    'telegram' => !empty($cb['from']['username']) ? '@'.$cb['from']['username'] : '-',
                    'type' => 'BOT ROYXAT',
                    'budget' => '-',
                    'description' => "Saytdan kelgan mijoz bot orqali ruxsat berdi",
                ]);
                telegram_api('answerCallbackQuery', ['callback_query_id'=>$cb['id'], 'text'=>'Ruxsat berildi']);
                bot_send($chatId, "Ruxsat berildi. Saytga qayting — ro'yxat tasdiqlandi.");
            }
        }
        return;
    }
    $msg = $update['message'] ?? null;
    if (!$msg) return;
    $chatId = (string)($msg['chat']['id'] ?? '');
    if ($chatId === '') return;
    $text = trim((string)($msg['text'] ?? ''));
    if (!empty($msg['contact'])) {
        $phoneC = $msg['contact']['phone_number'] ?? '';
        $all = users_all();
        foreach ($all['users'] as &$uu) {
            if (($uu['tg_chat']??'')===$chatId || ($uu['id']??'')===($_SESSION['x']??'')) {
                if ($phoneC) $uu['phone'] = $phoneC;
                $uu['tg_id'] = (string)($msg['from']['id'] ?? '');
                $uu['username'] = $username ?: ($uu['username']??'');
            }
        }
        # also match latest pending without tg
        foreach ($all['users'] as &$uu) {
            if (!empty($uu['pending']) && empty($uu['tg_chat'])) {
                $uu['tg_chat']=$chatId; $uu['tg_id']=(string)($msg['from']['id']??'');
                if ($phoneC) $uu['phone']=$phoneC;
                if ($username) $uu['username']=$username;
            }
        }
        users_save($all);
        $kb = json_encode(['inline_keyboard'=>[[['text'=>'Saytga ruxsat','callback_data'=>'accept_last']]]]);
        bot_send($chatId, "Telefon olindi. Endi <b>Saytga ruxsat</b> ni bosing.", $kb);
        return;
    }
    $name = trim((string)($msg['from']['first_name'] ?? 'mijoz'));
    $username = (string)($msg['from']['username'] ?? '');
    if ($username) { require_once __DIR__.'/users.php'; tgmap_set($username, $chatId); }

    if ($text === '/cancel') {
        bot_save($chatId, ['step' => 'idle', 'data' => []]);
        bot_send($chatId, "Buyurtma bekor qilindi.\nQayta boshlash: /start");
        return;
    }

    if ($text === '/start' || strpos($text, '/start ') === 0 || $text === 'Yangi buyurtma') {

        $sf = __DIR__ . '/storage/stats.json';
        $st = is_file($sf) ? json_decode((string)file_get_contents($sf), true) : [];
        if (!is_array($st)) $st = [];
        $ids = $st['bot_ids'] ?? [];
        if ($chatId && !in_array((string)$chatId, $ids, true)) {
            $ids[] = (string)$chatId;
            $st['bot_ids'] = $ids;
            $st['bots'] = count($ids);
            $st['visits'] = (int)($st['visits'] ?? 0);
            if (!is_dir(__DIR__ . '/storage')) mkdir(__DIR__ . '/storage', 0755, true);
            file_put_contents($sf, json_encode($st, JSON_UNESCAPED_UNICODE));
        }

        $logo = __DIR__ . '/assets/img/webmood-logo.png';
        if (!is_file($logo)) $logo = __DIR__ . '/webmood-logo.png';
        telegram_send_photo($chatId, $logo, "WEBmood  |  BUILD. CREATE. INNOVATE.");

        $payload = '';
        if (strpos($text, '/start ') === 0) $payload = trim(substr($text, 7));

        if (strpos($payload ?? '', 'bind_') === 0 || strpos($payload ?? '', 'reg_') === 0) {
            $uid = trim(str_replace(['bind_','reg_'], '', $payload));
            $all = users_all();
            foreach ($all['users'] as &$uu) {
                if (($uu['id']??'')===$uid) {
                    $uu['tg_chat'] = $chatId;
                    $uu['tg_id'] = (string)($msg['from']['id'] ?? '');
                    $uu['username'] = $username ?: ($uu['username']??'');
                }
            }
            users_save($all);
            $ckb = json_encode(['keyboard'=>[[['text'=>'Telefonni ulashish','request_contact'=>true]]],'resize_keyboard'=>true,'one_time_keyboard'=>true]);
            bot_send($chatId, "Salom. Username va ID olindi.\nTelefonni ulashing, keyin saytga ruxsat berasiz.", $ckb);
            $kb = json_encode(['inline_keyboard'=>[[['text'=>'Saytga ruxsat','callback_data'=>'accept_'.$uid]]]]);
            bot_send($chatId, "Tayyor bo'lsangiz <b>Saytga ruxsat</b> ni bosing.", $kb);
            return;
        }
        if (strpos($payload, 'web_') === 0) {
            $token = substr($payload, 4);
            require_once __DIR__ . '/users.php';
            $all = users_all();
            if (isset($all['tokens'][$token])) {
                $kb = json_encode(['inline_keyboard'=>[[['text'=>'Ruxsat','callback_data'=>'allow_'.$token]]]]);
                bot_send($chatId, "Saytdan keldingiz. Ro'yxatni tasdiqlash uchun <b>Ruxsat</b> ni bosing.", $kb);
                return;
            }
        }
        bot_send(
            $chatId,
            "<b>Assalomu alaykum, {$name}!</b>\n\n" .
            "WEBmood — professional web studio.\n" .
            "Sayt, magazin, portfolio, 3D va botlarni sifatli qilamiz.\n\n" .
            "Davom etish uchun pastdagi tugmani tanlang.",
            bot_kb([['Buyurtma berish'], ['Shartnoma'], ['Maslahat olish'], ['Portfolio']])
        );
        bot_save($chatId, [
            'step' => 'menu',
            'data' => ['from' => $name, 'telegram_auto' => $username ? '@'.$username : ''],
        ]);
        return;
    }

    $state = bot_load($chatId);
    $step = $state['step'] ?? 'idle';
    $data = $state['data'] ?? [];

    // Telegram -> web chat sync (mijoz yozsa saytga tushadi)
    global $TELEGRAM;
    $adminId = (string)($TELEGRAM['adminChatId'] ?? '');
    if ($text !== '' && $text[0] !== '/' && !in_array($text, ['Buyurtma berish','Shartnoma','Maslahat olish','Portfolio','Yangi buyurtma'], true)) {
        if ((string)$chatId !== $adminId) {
            // faqat menu/idle/advice yoki oddiy yozishmalar
            if (in_array($step, ['idle','menu','advice'], true) || $step === 'idle') {
                $webUid = chat_uid_by_tg($chatId);
                chat_append($webUid, 'user', $text, ['src'=>'telegram','tg_chat'=>$chatId,'name'=>$name,'username'=>$username]);
                // adminga qisqa xabar
                if ($adminId) {
                    bot_send($adminId, "💬 Yangi xabar (Telegram)\nFrom: ".($username?('@'.$username):$name)."\nUID: {$webUid}\n\n".$text);
                }
            }
        }
    }


    if ($text === 'Shartnoma' || $text === '/shartnoma') {
        bot_send($chatId, contract_short(), contract_kb());
        return;
    }
    if ($step === 'idle') {
        bot_send($chatId, "Boshlash uchun /start yozing.");
        return;
    }

    if ($text === '/stats') {
        global $TELEGRAM, $SITE;
        if ((string)$chatId === (string)($TELEGRAM['adminChatId'] ?? '')) {
            $sf = __DIR__ . '/storage/stats.json';
            $st = is_file($sf) ? json_decode((string)file_get_contents($sf), true) : [];
            $bots = (int)($st['bots'] ?? count($st['bot_ids'] ?? []));
            $web = (int)($st['visits'] ?? 0);
            $orders = (int)($st['orders'] ?? 0);
            $advice = (int)($st['advice'] ?? 0);
            bot_send($chatId, "<b>WEBmood statistika</b>\nBot: {$bots}\nWeb: {$web}\nBuyurtma: {$orders}\nMaslahat: {$advice}");
        }
        return;
    }

    if ($step === 'menu') {
        global $SITE;
        if ($text === 'Portfolio') {
            $url = $SITE['url'] ?? 'http://localhost:8000';
            bot_send($chatId, "<b>Portfolio</b>\nIshlarimiz shu yerda:\n{$url}\n\nBo'lim: Selected Work\nQaytish: /start");
            return;
        }
        if ($text === 'Maslahat olish') {
            bot_save($chatId, ['step' => 'advice', 'data' => $data]);
            bot_send($chatId, "<b>WEBmood AI maslahatchi</b>\nLoyihangizni yozing: tur, maqsad, byudjet, muddat.\nChiqlish: /cancel");
            return;
        }
        $data['intent'] = $text;
        bot_save($chatId, ['step' => 'phone', 'data' => $data]);
        bot_send($chatId, "<b>1 / 8</b> Telefon raqamingizni yozing.\nMasalan: <code>+998 91 708 09 92</code>");
        return;
    }
    if ($step === 'advice') {
        $reply = advisor_reply($text);
        bot_send($chatId, $reply . "\n\nYana savol yozing yoki /start");
        $data['advice'] = $text;
        $data['name'] = $data['from'] ?? $name;
        $data['telegram'] = $data['telegram_auto'] ?? '';
        $data['type'] = 'Maslahat';
        $data['phone'] = $data['phone'] ?? '-';
        $data['budget'] = '-';
        $data['description'] = "MASLAHAT SAVOLI:\n" . $text . "\n\nJAVOB YUBORILDI.";
        telegram_notify_admin($data);
        add_order([
            'name'=>$data['name']??'','phone'=>$data['phone']??'','type'=>$data['type']??'',
            'budget'=>$data['budget']??'','description'=>$data['description']??'','source'=>'telegram',
            'user_id'=>'tg'.$chatId,'tg_chat'=>$chatId,'username'=>$data['telegram']??($username?('@'.$username):'')
        ]);
        $sf = __DIR__ . '/storage/stats.json';
        $st = is_file($sf) ? json_decode((string)file_get_contents($sf), true) : [];
        if (!is_array($st)) $st = [];
        $st['advice'] = (int)($st['advice'] ?? 0) + 1;
        file_put_contents($sf, json_encode($st, JSON_UNESCAPED_UNICODE));
        return;
    }
    if ($step === 'phone') {
        $data['phone'] = $text;
        bot_save($chatId, ['step' => 'username', 'data' => $data]);
        $hint = !empty($data['telegram_auto']) ? "\nSizniki: <code>".$data['telegram_auto']."</code>" : '';
        bot_send($chatId, "<b>2 / 8</b> Telegram username yozing.\nMasalan: <code>@webmood</code>".$hint);
        return;
    }
    if ($step === 'username') {
        $data['telegram'] = $text;
        bot_save($chatId, ['step' => 'type', 'data' => $data]);
        bot_send($chatId, "<b>3 / 8</b> Qanday loyiha kerak?", bot_kb([
            ['Landing sahifa', 'Internet-magazin'],
            ['Portfolio', 'Korporativ sayt'],
            ['Web ilova', '3D sayt'],
            ['Telegram bot', 'Boshqa'],
        ]));
        return;
    }
    if ($step === 'type') {
        $data['type'] = $text;
        bot_save($chatId, ['step' => 'goal', 'data' => $data]);
        bot_send($chatId, "<b>4 / 8</b> Loyiha nima uchun kerak?");
        return;
    }
    if ($step === 'goal') {
        $data['goal'] = $text;
        bot_save($chatId, ['step' => 'pages', 'data' => $data]);
        bot_send($chatId, "<b>5 / 8</b> Qanday sahifalar / funksiyalar kerak?");
        return;
    }
    if ($step === 'pages') {
        $data['pages'] = $text;
        bot_save($chatId, ['step' => 'design', 'data' => $data]);
        bot_send($chatId, "<b>6 / 8</b> Dizayn qanday bolsin?", bot_kb([
            ['Minimal'], ['Premium / dark'], ['Rangli'], ['Hali bilmayman']
        ]));
        return;
    }
    if ($step === 'design') {
        $data['design'] = $text;
        bot_save($chatId, ['step' => 'deadline', 'data' => $data]);
        bot_send($chatId, "<b>7 / 8</b> Muddat qachon?");
        return;
    }
    if ($step === 'deadline') {
        $data['deadline'] = $text;
        bot_save($chatId, ['step' => 'budget', 'data' => $data]);
        bot_send($chatId, "<b>8 / 8</b> Byudjet?", bot_kb([
            ["2-5 mln som"], ["5-10 mln som"], ["10+ mln som"], ['Kelishamiz']
        ]));
        return;
    }
    if ($step === 'budget') {
        $data['budget'] = $text;
        bot_save($chatId, ['step' => 'extra', 'data' => $data]);
        bot_send($chatId, "Qoshimcha istak bormi? Yoksa <b>Yoq</b> deb yozing.");
        return;
    }
    if ($step === 'extra') {
        $data['extra'] = $text;
        $data['name'] = $data['from'] ?? 'Mijoz';
        $data['description'] =
            "Maqsad: ".($data['goal'] ?? '-')."\n".
            "Sahifalar: ".($data['pages'] ?? '-')."\n".
            "Dizayn: ".($data['design'] ?? '-')."\n".
            "Muddat: ".($data['deadline'] ?? '-')."\n".
            "Qoshimcha: ".($data['extra'] ?? '-');
        telegram_notify_admin($data);
        add_order([
            'name'=>$data['name']??'','phone'=>$data['phone']??'','type'=>$data['type']??'',
            'budget'=>$data['budget']??'','description'=>$data['description']??'','source'=>'telegram',
            'user_id'=>'tg'.$chatId,'tg_chat'=>$chatId,'username'=>$data['telegram']??($username?('@'.$username):'')
        ]);
        bot_save($chatId, ['step' => 'idle', 'data' => []]);
        bot_send($chatId, "<b>Rahmat!</b> So'rov qabul qilindi.\nTez orada boglanamiz.");
        bot_send($chatId, contract_short(), contract_kb());
    }
}
