<?php
function advisor_reply(string $q): string
{
    $key = env('OPENAI_API_KEY', '');
    if ($key !== '') {
        $ai = advisor_openai($key, $q);
        if ($ai) return $ai;
    }
    return advisor_local($q);
}

function advisor_openai(string $key, string $q): ?string
{
    $payload = json_encode([
        'model' => 'gpt-4o-mini',
        'messages' => [
            ['role' => 'system', 'content' => 'Sen WEBmood professional web consultantisan. Qisqa, aniq, ozbek tilida maslahat ber. Sotuvchi ohangida emas, expert ohangida. 3-6 qisqa paragraf yoki bullet.'],
            ['role' => 'user', 'content' => $q],
        ],
        'max_tokens' => 400,
    ], JSON_UNESCAPED_UNICODE);
    $ctx = stream_context_create([
        'http' => [
            'method' => 'POST',
            'header' => "Content-Type: application/json\r\nAuthorization: Bearer {$key}\r\n",
            'content' => $payload,
            'timeout' => 20,
            'ignore_errors' => true,
        ],
        'ssl' => ['verify_peer' => false, 'verify_peer_name' => false],
    ]);
    $raw = @file_get_contents('https://api.openai.com/v1/chat/completions', false, $ctx);
    if (!$raw) return null;
    $j = json_decode($raw, true);
    $txt = $j['choices'][0]['message']['content'] ?? null;
    return $txt ? trim($txt) : null;
}

function advisor_local(string $q): string
{
    $t = function_exists('mb_strtolower') ? mb_strtolower($q) : strtolower($q);
    $tips = [];
    if (preg_match('/magazin|shop|savdo|e-?comm|tolov|to\'lov/', $t)) {
        $tips[] = "Internet-magazin uchun: katalog, savatcha, to'lov, buyurtma statusi va admin panel asosiy. Avval 15-30 ta mahsulot bilan MVP qiling.";
    }
    if (preg_match('/landing|reklama|lead|sotuv sahifa/', $t)) {
        $tips[] = "Landing: 1 ta aniq taklif, 1 ta CTA, ijtimoiy ishonch (raqam/case) va tez yuklanish. Ortiqcha menyu kerak emas.";
    }
    if (preg_match('/portfolio|brend|dizayn/', $t)) {
        $tips[] = "Portfolio sayt: 4-6 kuchli case, texnologiya, natija. Har case da muammo-yechim-natija tuzilishi ishlaydi.";
    }
    if (preg_match('/3d|three|webgl/', $t)) {
        $tips[] = "3D faqat birinchi ekranda yengil model. Mobil uchun fallback qiling, aks holda tezlik tushadi.";
    }
    if (preg_match('/bot|telegram/', $t)) {
        $tips[] = "Telegram bot + sayt juftligi yaxshi: bot lead yig'adi, sayt ishonch beradi. Formani botga dublikat qilmang, bitta oqim qiling.";
    }
    if (preg_match('/seo|google|qidiruv/', $t)) {
        $tips[] = "SEO: semantik HTML, tezlik, aniq title/description, shahar+xizmat kalit so'zlari. Avval 5 ta sahifa sifatli bo'lsin.";
    }
    if (preg_match('/byudjet|narx|pul|mln|sum/', $t)) {
        $tips[] = "Byudjetni 3 qismga bo'ling: dizayn 20%, dasturlash 60%, kontent/SEO 20%. Arzon start = keyin qimmat qayta yozish.";
    }
    if (!$tips) {
        $tips[] = "Avval maqsadni yozing: savdo, lead, brend yoki ichki tizim. Shunga qarab landing, korporativ yoki web-ilova tanlanadi.";
        $tips[] = "Minimal versiya: bosh sahifa, xizmatlar, ishlar, aloqa, Telegram CTA. Keyin admin va 3D qo'shiladi.";
        $tips[] = "Telefon + tezlik + aniq CTA bo'lmasa, chiroyli dizayn ham natija bermaydi.";
    }
    $tips[] = "Keyingi qadam: 3 ta raqobatchi sayt yuboring yoki /start orqali buyurtma qoldiring. WEBmood texnik reja tuzib beradi.";
    return "<b>WEBmood maslahat</b>\n\n- " . implode("\n- ", $tips);
}
