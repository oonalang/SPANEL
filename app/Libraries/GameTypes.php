<?php

namespace App\Libraries;

/**
 * The license game types in one place. The key generator, key editor and
 * loader catalog form all read this list, so every dropdown offers the same
 * games and keys_code.game always holds a known code.
 */
final class GameTypes
{
    /** stored code => label shown in the panel. Add a line here to add a game. */
    public const BASE = [
        'CODMGR'   => 'CODM GARENA',
        'CODMFREE' => 'CODM FREE',
        'CODM'     => 'CODM GLOBAL',
        'PBS'      => 'BLOODSTRIKE',
        'MLBB'     => 'MOBILE LEGENDS',
        'FL84'     => 'FARLIGHT 84',
        '8BP'      => 'EASY VICTORY',
    ];

    /**
     * BASE plus any extra codes already in keys_code, so a stored value never
     * disappears from a dropdown.
     *
     * @param list<string|array{game?:string}> $extra
     *
     * @return array<string,string>
     */
    public static function all(array $extra = []): array
    {
        $list = self::BASE;

        foreach ($extra as $item) {
            $code = trim((string) (is_array($item) ? ($item['game'] ?? '') : $item));

            if ($code !== '' && ! isset($list[$code]) && preg_match('/^[A-Za-z0-9_-]{1,32}$/D', $code)) {
                $list[$code] = $code;
            }
        }

        return $list;
    }

    /** Ready-made <option> tags. The selected code is always included. */
    public static function options(?string $selected = null, array $extra = [], bool $placeholder = false): string
    {
        $selected = trim((string) $selected);
        $list     = self::all($extra);

        if ($selected !== '' && ! isset($list[$selected])) {
            $list[$selected] = $selected;
        }

        $html = '';

        if ($placeholder) {
            $html .= '<option value="" disabled' . ($selected === '' ? ' selected' : '') . '>Select game</option>';
        }

        foreach ($list as $code => $label) {
            $code  = (string) $code;
            $text  = $label === $code ? $code : $label . ' (' . $code . ')';
            $html .= '<option value="' . esc($code, 'attr') . '"' . ($selected === $code ? ' selected' : '') . '>' . esc($text) . '</option>';
        }

        return $html;
    }
}
