<?php

namespace TimWhitlock\JavaScript\JTokenizer;

class JTokenizer extends JTokenizerBase
{
    protected $regPunc = '/(?:\>\>\>\=|\>\>\>|\<\<\=|\>\>\=|\!\=\=|\=\=\=|&&|\<\<|\>\>|\|\||\*\=|\|\=|\^\=|&\=|%\=|-\=|\+\+|\+\=|--|\=\=|\>\=|\!\=|\<\=|;|,|\<|\>|\.|\]|\}|\(|\)|\[|\=|\:|\||&|-|\{|\^|\!|\?|\*|%|~|\+)/';

    function __construct($whitespace, $unicode)
    {
        parent::__construct($whitespace, $unicode);
        $this->Lex = Lex::get('JLex');
    }
}
