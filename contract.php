<?php
function contract_text(): string {
    return <<<'HTML'
✨━━━━━━━━━━━━━━━━━━━━✨
📜 <b>WEBmood HAMKORLIK SHARTNOMASI</b>
✨━━━━━━━━━━━━━━━━━━━━✨

👋 Hurmatli mijoz!
Siz <b>WEBmood</b> bilan ishlashni boshlayapsiz.

📌 <b>1. LOYIHA</b> — buyurtma bo‘yicha sayt / bot / dizayn
💰 <b>2. TO‘LOV</b> — 50% oldindan + 50% topshirishda
⏱ <b>3. MUDDAT</b> — loyihaga qarab
🔁 <b>4. TUZATISH</b> — 5 marta bepul
🔒 <b>5. HUQUQ</b> — to‘liq to‘lovdan keyin loyiha sizniki
🤝 <b>6. MAJBURIYAT</b> — WEBmood sifatli ish; mijoz material beradi
❌ <b>7. BEKOR</b> — mijoz bekor qilsa oldindan 25% qaytadi
📞 <b>8. ALOQA</b> — +998 91 708 09 92

✅ <b>Da</b> — roziman
❌ <b>Net</b> — rad etaman
HTML;
}

function contract_short(): string {
    return <<<'HTML'
📜 <b>WEBmood shartnoma</b>

📌 Loyiha — buyurtma bo‘yicha
💰 To‘lov — 50% + 50%
🔁 Tuzatish — 5 marta bepul
❌ Bekor — oldindan 25% qaytadi
📞 +998 91 708 09 92

Rozimisiz? 👇
HTML;
}

function contract_kb(): string {
    return json_encode([
        'inline_keyboard' => [[
            ['text' => '✅ Da — roziman', 'callback_data' => 'contract_yes'],
            ['text' => '❌ Net', 'callback_data' => 'contract_no'],
        ]],
    ], JSON_UNESCAPED_UNICODE);
}
