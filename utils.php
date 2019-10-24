<?php

/**
 * Convert TTF to Type42 via shell exec of gs ttf2pscid2.ps using JSON options
 * @param array $args associative array of option names and properly typed values
 * @param string $out optional path for -sOutputFile= or -o
 * @return string the output from the gs command
 */
function ttf2pscid2($args, $out = '') {
    $o = $out ? "-o '$out'" : '-dBATCH -dNOPAUSE';
    $json = json_encode($args, JSON_HEX_APOS | JSON_HEX_QUOT); // escape ' " for clarity
    $arg = ps_escape_string($json, true, true); // postscript string token
    $arg = escapeshellarg($arg); // shell arg
    $cmd = "gs -q -dNODISPLAY $o -- ttf2pscid2.ps $arg";
    exec($cmd, $a, $r);
    $o = implode("\n", $a);
    if ($r || $args['info'] && $out && (!is_file($out) || !filesize($out)))
        trigger_error("gs failed: $cmd $r\n$o");
    return $o;
}

/**
 * Convert PHP string to postscript string escaping special postscript characters
 * @param string $str string to convert
 * @param boolean $as_token true to wrap in parenthesis as a postscript literal text string token
 * @param boolean $esc_quotes true to escape double-quotes removed by gsargs.c:arg_next()
 * @return string the escaped postscript string
 */
function ps_escape_string($str, $as_token = false, $esc_quotes = false) {
    $s = '';
    foreach (str_split($str) as $c) {
        if (($i = strpos("\n\r\t\10\f\\()", $c)) !== false)
            $c = '\\' . 'nrtbf\\()'[$i]; // postscript escapes, see PLRM 3.2.2 Literals
        elseif (($o = ord($c)) < 0x20 || $o >= 0x7F)
            $c = sprintf('\\%03o', $o); // control & non-ASCII
        elseif ($esc_quotes && $c == '"')
            $c = '\\042'; // octal escape " (for gs v9 parameters)
        $s .= $c;
    }
    return $as_token ? "($s)" : $s;
}

/* e.g.
$ttf = 'Marlborough.ttf';
$subset = 'Olá mundo';
$args = ['ttf' => $ttf, 'subset' => $subset, 'comments' => true, 'info' => false, 'compress' => false];
echo ttf2pscid2($args, 'info.txt') . PHP_EOL;
*/

