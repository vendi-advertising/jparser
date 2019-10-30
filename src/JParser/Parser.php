<?php

namespace TimWhitlock\JavaScript\JParser;

use Exception;
use TimWhitlock\JavaScript\JTokenizer\Lex;

abstract class Parser
{
    protected $Lex;
    protected $Grammar;
    protected $node_classes = [];
    protected $default_node_class = 'ParseNode';
    protected $input;
    protected $tok;
    protected $t;
    protected $badtoken;
    protected $recursion = false;

    protected function __construct(Lex $Lex, Grammar $Grammar)
    {
        $this->Lex = $Lex;
        $this->Grammar = $Grammar;
    }

//    Removed by CJH since it doesn't appear to be used and has static/instance issues
//    static function dump_tokens($tokens)
//    {
//        $line = 0;
//        foreach ($tokens as $token) {
//            list($t, $s, $l, $c) = $token;
//            if ($l !== $line) {
//                $line = $l;
//                echo "#$line :\n";
//            }
//            if ($s === $t) {
//                echo "   $s\n";
//            } else {
//                echo "  ", $this->token_name($t), " = $s\n";
//            }
//        }
//    }

    abstract function parse(array $input);

    function tokenize($src)
    {
        $tokens = [];
        $chars = preg_split('//', $src, -1, PREG_SPLIT_NO_EMPTY);
        $line = 1;
        $col = 1;
        foreach ($chars as $i => $chr) {
            switch ($chr) {
                case "\n":
                    $line++;
                    $col = 1;
                    break;
                default:
                    $tokens[] = [$chr, $chr, $line, $col++];
            }
        }
        return $tokens;
    }

    function register_node_class($s, $class)
    {
        if (!class_exists($class)) {
            $s = $this->token_name($s);
            throw new Exception("If you want to register class `$class' for symbol `$s' you should include it first");
        }
        $this->node_classes[$s] = $class;
    }

    function create_node($s)
    {
        if (isset($this->node_classes[$s])) {
            $class = $this->node_classes[$s];
        } else {
            $class = $this->default_node_class;
        }
        return new $class($s);
    }

    function print_token($t, $r = false)
    {
        if (is_scalar($t)) {
            $s = $this->token_name($t);
        } else if (is_array($t)) {
            if ($t[0] && $t[0] !== $t[1]) {
                $s = sprintf('%s="%s"', $this->token_name($t[0]), $t[1]);
            } else {
                $s = sprintf('"%s"', $t[1]);
            }
        } else {
            $s = 'ERROR';
        }
        return $r ? $s : print($s);
    }

    function implode_tokens(array $a)
    {
        $b = [];
        foreach ($a as $token) {
            $b[] = $this->token_name($token);
        }
        return implode(' ', $b);
    }

    protected function init(array $input)
    {
        if (empty($input)) {
            throw new Exception('Input stream is empty');
        }
        $this->input = $input;
        $this->input[] = P_EOF;
        $this->current_token();
        if (!isset($this->Lex) || !$this->Lex instanceof Lex) {
            throw new Exception('Parser does not know about Lex');
        }
        if (!isset($this->Grammar) || !$this->Grammar instanceof Grammar) {
            throw new Exception('Parser does not know about Grammar');
        }
    }

    protected function current_token()
    {
        if (!isset($this->tok)) {
            $this->tok = current($this->input);
            $this->t = self::token_to_symbol($this->tok);
        }
        return $this->tok;
    }

    static function token_to_symbol($t)
    {
        if (is_array($t)) {
            $t = $t[0];
        }
        return $t;
    }

    protected function fail($extra = '')
    {
        $tokenName = null;
        $tokenSymbol = null;
        $tokenLine = null;
        $tokenColumn = null;

        if ($extra && func_num_args() > 1) {
            $args = func_get_args();
            $extra = call_user_func_array('sprintf', $args);
        }
        if (is_null($this->badtoken)) {
            $tok = $this->tok;
        } else {
            $tok = $this->badtoken;
        }
        if (is_array($tok)) {
            $tokenName = $this->token_name($tok[0]);
            $tokenSymbol = $tok[0];
            if (isset($tok[2])) {
                $tokenLine = $tok[2];
            } else {
                $tokenLine = null;
            }
            if (isset($tok[3])) {
                $tokenColumn = $tok[3];
            } else {
                $tokenColumn = null;
            }
        } else if (is_scalar($tok)) {
            $tokenName = $this->token_name($tok);
            $tokenSymbol = $tok;
            $tokenLine = 0;
            $tokenColumn = 0;
        }
        throw new ParseError($extra, 0, $tokenLine, $tokenColumn, $tokenName, $tokenSymbol);
    }

    function token_name($t)
    {
        $t = self::token_to_symbol($t);
        if (!is_int($t)) {
            return $t;
        }
        return $this->Lex->name($t);
    }

    protected function current_input_symbol()
    {
        if (!isset($this->t)) {
            $this->current_token();
        }
        return $this->t;
    }

    protected function next_token()
    {
        $this->tok = next($this->input);
        $this->t = self::token_to_symbol($this->tok);
        return $this->tok;
    }

    protected function prev_token()
    {
        $this->tok = prev($this->input);
        $this->t = self::token_to_symbol($this->tok);
        return $this->tok;
    }
}
