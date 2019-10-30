<?php

namespace TimWhitlock\JavaScript\JParser;

abstract class JParserBase extends LRParser
{
    private $newline = false;
    private $asitoken;

    static function parse_string($src, $unicode = true, $parser = __CLASS__, $lexer = 'JTokenizer')
    {
        $Tokenizer = new $lexer(false, $unicode);
        $tokens = $Tokenizer->get_all_tokens($src);
        unset($src);
        $Parser = new $parser;
        return $Parser->parse($tokens);
    }

    protected function current_token()
    {
        $t = parent::current_token();
        if ($t[0] === J_LINE_TERMINATOR) {
            $t = $this->next_token();
            $this->newline = true;
        }
        return $t;
    }

    protected function next_token()
    {
        $s = $this->t;
        $this->newline = false;
        do {
            $tok = parent::next_token();
            if (!$tok) {
                parent::fail('Failed to get next token');
            }
        } while ($tok[0] === J_LINE_TERMINATOR && $this->newline = true);
        if ($this->newline) {
            switch ($s) {
                case J_CONTINUE:
                case J_BREAK:
                case J_RETURN:
                    $this->insert_semicolon();
                    return $this->tok;
                case J_THROW:
                    return parent::fail('No line terminator after %s', $this->token_name($s));
            }
            if ($s !== ';' && ($tok[0] === '++' || $tok[0] === '--')) {
                $this->insert_semicolon();
                return $this->tok;
            }
        }
        return $tok;
    }

    private function insert_semicolon($failtext = null)
    {
        if (isset($failtext) && isset($this->badtoken)) {
            if (isset($this->asitoken) && $this->asitoken === $this->badtoken) {
                return parent::fail($failtext);
            }
            $this->asitoken = $this->badtoken;
        }
        $prevtok = $this->prev_token();
        if (!$prevtok) {
            parent::fail($failtext);
        }
        $this->tok = [';', ';', $prevtok[2], 0];
        $this->t = ';';
        $this->newline = false;
        return true;
    }

    protected function prev_token()
    {
        do {
            $t = parent::prev_token();
        } while ($t[0] === J_LINE_TERMINATOR);
        return $t;
    }

    protected function fail($extra = '')
    {
        if ($extra && func_num_args() > 1) {
            $args = func_get_args();
            $extra = call_user_func_array('sprintf', $args);
        }
        do {
            if ($this->t == ';' && $this->tok[3] === 0) {
                $this->badtoken = $this->asitoken;
            }
            if ($this->newline) {
                return $this->insert_semicolon($extra);
            }
            if ($this->t === '}') {
                return $this->insert_semicolon($extra);
            }
            if ($this->t === P_EOF) {
                return $this->insert_semicolon($extra);
            }
        } while (false);
        return parent::fail($extra);
    }
}
