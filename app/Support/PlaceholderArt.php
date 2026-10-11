<?php

namespace App\Support;

use Illuminate\Support\Facades\File;

class PlaceholderArt
{
    private const SKIES = [
        'day' => ['#9FD4E8', 600, 110, 42, '#FFF3C4', ''],
        'golden' => ['#F7C77E', 560, 250, 48, '#FFE7A8', '<rect width="800" height="500" fill="#FF9A3C" opacity=".16"/>'],
        'dusk' => ['#C97C93', 520, 300, 50, '#FFD1A0', '<rect width="800" height="500" fill="#2E2150" opacity=".32"/>'],
    ];

    private const ALT = [
        'savanna' => 'Illustration of a safari vehicle and an acacia tree on the savanna',
        'sea' => 'Illustration of a dhow boat and a dolphin on the sea',
        'beach' => 'Illustration of a white sand beach with palm trees',
        'hills' => 'Illustration of green hills at sunrise',
        'camp' => 'Illustration of a lit safari tent at dusk',
    ];

    private const SCENES = [
        'savanna' => '<path d="M0 300Q150 260 300 290T600 280T800 290V500H0Z" fill="#C79A3B"/>'
            . '<path d="M0 350Q200 320 400 345T800 335V500H0Z" fill="#A8741F"/>'
            . '<path d="M0 430Q250 400 500 425T800 415V500H0Z" fill="#7E5413"/>'
            . '<path d="M170 440L180 330M180 360L150 330M180 345L215 322" stroke="#2A1D0D" stroke-width="8" fill="none" stroke-linecap="round"/>'
            . '<ellipse cx="185" cy="315" rx="95" ry="22" fill="#2A1D0D"/><ellipse cx="135" cy="332" rx="48" ry="12" fill="#2A1D0D"/>'
            . '<g fill="#2A1D0D"><rect x="520" y="395" width="110" height="34" rx="6"/><rect x="540" y="365" width="64" height="32" rx="4"/><rect x="535" y="358" width="80" height="8" rx="2"/><circle cx="545" cy="432" r="14"/><circle cx="610" cy="432" r="14"/></g>',

        'sea' => '<rect y="280" width="800" height="220" fill="#2E8FA3"/>'
            . '<path d="M0 330q50-18 100 0t100 0t100 0t100 0t100 0t100 0t100 0t100 0M0 400q50-18 100 0t100 0t100 0t100 0t100 0t100 0t100 0t100 0M0 465q50-18 100 0t100 0t100 0t100 0t100 0t100 0t100 0t100 0" stroke="#8FD0DC" stroke-width="3" fill="none"/>'
            . '<path d="M470 292H690L660 322H500Z" fill="#3B2A14"/><path d="M580 292V210" stroke="#3B2A14" stroke-width="5"/><path d="M584 214L650 286H584Z" fill="#F7F1E3"/><path d="M576 230L530 286H576Z" fill="#EFE3C5"/>'
            . '<g fill="#1F4F63"><path d="M100 340C130 260 230 245 290 310 240 295 175 305 100 340Z"/><path d="M180 272L196 242L216 276Z"/><path d="M100 340L72 315L80 355Z"/></g>',

        'beach' => '<defs><g id="fr"><path d="M0 0Q-55-50-125-10Q-55-20 0 0Z"/><path d="M0 0Q65-50 135-15Q65-25 0 0Z"/><path d="M0 0Q-30-70-75-85Q-20-55 0 0Z"/><path d="M0 0Q40-75 90-85Q30-55 0 0Z"/><path d="M0 0Q0-50 10-95Q20-45 0 0Z"/></g></defs>'
            . '<rect y="260" width="800" height="90" fill="#1FA6B8"/><rect y="340" width="800" height="160" fill="#F0DDB0"/>'
            . '<path d="M0 346Q200 330 400 346T800 340V356H0Z" fill="#FFFFFF" opacity=".7"/>'
            . '<path d="M150 500Q165 400 215 300" stroke="#6B4A2B" stroke-width="14" fill="none" stroke-linecap="round"/><use href="#fr" transform="translate(215 300)" fill="#2F7D4F"/>'
            . '<path d="M650 500Q655 430 690 370" stroke="#6B4A2B" stroke-width="9" fill="none" stroke-linecap="round"/><use href="#fr" transform="translate(690 370) scale(.65)" fill="#2F7D4F"/>',

        'hills' => '<path d="M0 330Q120 250 260 310T520 290T800 320V500H0Z" fill="#8DB574"/>'
            . '<path d="M0 390Q160 320 330 380T640 360T800 380V500H0Z" fill="#5E8B5A"/>'
            . '<path d="M0 450Q200 410 420 445T800 435V500H0Z" fill="#3F6B3A"/>',

        'camp' => '<path d="M0 340Q200 290 420 330T800 320V500H0Z" fill="#35603A"/>'
            . '<path d="M0 430Q250 395 520 425T800 410V500H0Z" fill="#244526"/>'
            . '<path d="M250 305L400 215L550 305Z" fill="#B59B73"/><rect x="275" y="305" width="250" height="105" fill="#8C7650"/>'
            . '<rect x="265" y="410" width="270" height="10" fill="#5A4630"/><path d="M275 305V410M525 305V410" stroke="#5A4630" stroke-width="6"/>',
    ];

    public static function path(string $scene, string $variant): string
    {
        $relative = "images/placeholders/{$scene}-{$variant}.svg";
        $file = public_path($relative);

        if (! File::exists($file)) {
            File::ensureDirectoryExists(dirname($file));
            File::put($file, self::svg($scene, $variant));
        }

        return '/' . $relative;
    }

    public static function alt(string $scene): string
    {
        return self::ALT[$scene];
    }

    private static function svg(string $scene, string $variant): string
    {
        [$sky, $x, $y, $r, $sun, $tint] = self::SKIES[$variant];

        $glow = $scene === 'camp'
            ? '<circle cx="400" cy="372" r="110" fill="#FFC56B" opacity=".2"/><rect x="360" y="330" width="80" height="80" fill="#FFC56B"/>'
            : '';

        return '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 800 500" preserveAspectRatio="xMidYMid slice">'
            . '<rect width="800" height="500" fill="' . $sky . '"/>'
            . '<circle cx="' . $x . '" cy="' . $y . '" r="' . $r . '" fill="' . $sun . '"/>'
            . self::SCENES[$scene] . $tint . $glow . '</svg>';
    }
}
