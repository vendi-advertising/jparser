<?php

namespace TimWhitlock\JavaScript\JParser;

class JNodeBase extends LRParseNode
{
    function push(ParseNode $Node, $recursion = true)
    {
        if ($Node->is_transparent($this)) {
            parent::push_thru($Node);
            $Node->destroy();
            return $this->length;
        }
        return parent::push($Node, $recursion);
    }

    function is_transparent(JNodeBase $Parent)
    {
        return false;
    }

    function obfuscate(array &$names)
    {
        foreach ($this->children as $Node) {
            if ($Node->is_terminal()) {
                if ($Node->is_symbol(J_IDENTIFIER)) {
                    $Node->obfuscate($names);
                }
            } else if (!$Node->is_symbol(J_FUNC_DECL) && !$Node->is_symbol(J_FUNC_EXPR)) {
                $Node->obfuscate($names);
            }
        }
    }

    function format_lines(&$line, array &$lines)
    {
        if ($this->is_terminal()) {
            switch ($this->t) {
                case ';':
                    $line = rtrim($line, ' ');
                    self::format_newline($this->t, $line, $lines);
                    break;
                case ',':
                    $line = rtrim($line, ' ');
                    $line .= ', ';
                    break;
                default:
                    $line .= $this->__toString() . ' ';
            }
        } else {
            foreach ($this->children as $Node) {
                $line .= $Node->format_lines($line, $lines);
            }
        }
    }

    protected static function format_newline($str, &$line, array &$lines)
    {
        $line .= $str;
        $lines[] = $line;
        $line = '';
    }

    function __toString()
    {
        if ($this->is_terminal()) {
            return parent::__toString();
        }
        $src = '';
        foreach ($this->children as $i => $Child) {
            if ($Child->is_terminal()) {
                $s = (string)$Child->value;
                switch ($Child->t) {
                    case J_FUNCTION:
                    case J_CONTINUE:
                    case J_BREAK;
                        $identFollows = isset($this->children[$i + 1]) && $this->children[$i + 1]->is_symbol(J_IDENTIFIER);
                        $identFollows and $s .= ' ';
                        break;
                    case J_VAR:
                    case J_DO:
                    case J_ELSE:
                    case J_RETURN:
                    case J_CASE:
                    case J_THROW:
                    case J_NEW:
                    case J_DELETE:
                    case J_VOID:
                    case J_TYPEOF:
                        $s .= ' ';
                        break;
                    case J_IN:
                    case J_INSTANCEOF:
                        $s = ' ' . $s . ' ';
                        break;
                }
            } else {
                $s = $Child->__toString();
            }
            $src .= $s;
        }
        return $src;
    }
}
