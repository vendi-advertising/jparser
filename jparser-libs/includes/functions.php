<?php

use TimWhitlock\JavaScript\JTokenizer\JTokenizer;
use TimWhitlock\JavaScript\JTokenizer\Lex;

function j_token_get_all($src, $whitespace = true, $unicode = true)
{
    $Tokenizer = new JTokenizer($whitespace, $unicode);
    return $Tokenizer->get_all_tokens($src);
}

function j_token_name($t)
{
    $Lex = Lex::get('JLex');
    return $Lex->name($t);
}
