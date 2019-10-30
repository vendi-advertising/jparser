<?php

namespace TimWhitlock\JavaScript\JParser;

use TimWhitlock\JavaScript\JTokenizer\Lex;

abstract class LRParseTable
{
    protected $table;

    function __construct(array $table = null)
    {
        if (!is_null($table)) {
            $this->table = $table;
        }
    }

    function export()
    {
        return $this->table;
    }

    function lookup($state, $la)
    {
        if (!isset($this->table[$state][$la])) {
            return null;
        } else {
            return $this->table[$state][$la];
        }
    }

    function permitted($state, Grammar $Grammar)
    {
        if (!isset($this->table[$state])) {
            return [];
        }
        $all = array_keys($this->table[$state]);
        $a = [];
        foreach ($all as $t) {
            if ($Grammar->is_terminal($t)) {
                $a[] = $t;
            }
        }
        return $a;
    }

    function dump(Lex $Lex, Grammar $Grammar, $state = null)
    {
        $table = [];
        $heads = ['' => 0];
        foreach ($this->table as $i => $row) {
            if (!is_null($state) && $i !== $state) {
                continue;
            }
            $table[$i] = [];
            $table[$i][''] = "#$i";
            $heads[''] = max($heads[''], strlen($table[$i]['']));
            foreach ($row as $sym => $entry) {
                if (is_null($sym)) {
                    $sym = 'null';
                } else {
                    $sym = $Lex->name($sym);
                }
                if ($entry & 1) {
                    $str = " #$entry ";
                } else {
                    list($nt, $rhs) = $Grammar->get_rule($entry);
                    $str = ' ' . $Lex->name($nt) . ' -> ';
                    foreach ($rhs as $t) {
                        $str .= $Lex->name($t) . ' ';
                    }
                }
                $table[$i][$sym] = $str;
                if (!isset($heads[$sym])) {
                    $heads[$sym] = strlen($sym);
                }
                $heads[$sym] = max($heads[$sym], strlen($str));
            }
        }
        $a = [];
        $b = [];
        foreach ($heads as $sym => $len) {
            $b[] = str_repeat('-', $len);
            $a[] = str_pad($sym, $len, ' ', STR_PAD_BOTH);
        }
        echo '+', implode('+', $b), "+\n";
        echo '|', implode('|', $a), "|\n";
        foreach ($table as $i => $row) {
            $c = [];
            foreach ($heads as $sym => $len) {
                if (isset($table[$i][$sym])) {
                    $c[] = str_pad($row[$sym], $len, ' ', STR_PAD_BOTH);
                } else {
                    $c[] = str_repeat(' ', $len);
                }
            }
            echo '+', implode('+', $b), "+\n";
            echo '|', implode('|', $c), "|\n";
        }
        echo '+', implode('+', $b), "+\n";
    }

    function class_export($classname, array $commentData = [])
    {
        echo "/**\n * Auto-generated file containing class $classname";
        foreach ($commentData as $tag => $value) {
            echo "\n * @$tag $value";
        }
        echo "\n */\n", "\n", "import('PLUG.parsing.LR.LRParseTable');\n", "\n", "/**\n * Auto-generated LRParseTable subclass";
        unset ($commentData['author'], $commentData['version']);
        foreach ($commentData as $tag => $value) {
            echo "\n * @$tag $value";
        }
        echo "\n */\n", "class $classname extends LRParseTable {\n", "\n", "/** @var array */\n", "protected \$table = ", var_export($this->table, 1), ";\n\n", "}\n";
    }
}
