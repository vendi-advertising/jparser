<?php

namespace TimWhitlock\JavaScript\JParser;

use Exception;

abstract class Grammar
{
    protected $goal;
    protected $i = 0;
    protected $rules = [];
    protected $excludela = [];
    protected $ts = [P_EPSILON => P_EPSILON, P_EOF => P_EOF];
    protected $nts = [];
    protected $firsts;
    protected $follows;
    protected $uindex;
    protected $ntindex;

    function excluded_terminals($nt)
    {
        if (isset($this->excludela[$nt])) {
            return $this->excludela[$nt];
        } else {
            return [];
        }
    }

    function exclude_terminal($nt, $t)
    {
        $this->excludela[$nt][] = $t;
    }

    function resolve_reduce_conflict($r1, $r2, $la)
    {
        $rule1 = $this->get_rule($r1);
        $rule2 = $this->get_rule($r2);
        $len1 = count($rule1);
        $len2 = count($rule2);
        if ($len2 > $len1) {
            return 1;
        } else if ($len1 > $len2) {
            return -1;
        } else {
            return 0;
        }
    }

    function get_rule($i)
    {
        return $this->rules[$i];
    }

    function resolve_shift_conflict(LRStateSet $toState, $r, $la)
    {
        return 0;
    }

    function get_rules($nt)
    {
        $rules = [];
        if (isset($this->ntindex[$nt])) {
            foreach ($this->ntindex[$nt] as $i) {
                $rules[$i] = $this->rules[$i];
            }
        }
        return $rules;
    }

    function non_terminal($s)
    {
        is_array($s) and $s = $s[0];
        return isset($this->nts[$s]);
    }

    function export()
    {
        return $this->rules;
    }

    function dump(Lex $Lex)
    {
        $t = max(2, strlen((string)$this->i));
        foreach ($this->rules as $i => $rule) {
            $lhs = $Lex->name($rule[0]);
            $rhs = $Lex->implode(' ', $rule[1]);
            $i = str_pad($i, $t, ' ', STR_PAD_LEFT);
            echo "$i: $lhs -> $rhs \n";
        }
        if ($this->excludela) {
            echo "Special rules:\n";
            foreach ($this->excludela as $nt => $a) {
                echo ' ', $Lex->name($nt), ' ~{ ', $Lex->implode(' ', $a), " }\n";
            }
        }
    }

    function first_set(array $sequence)
    {
        return $this->make_set($this->firsts, $sequence, 'FIRST');
    }

    protected function make_set(array $sets, array $sequence, $type)
    {
        $set = [];
        $s = reset($sequence);
        do {
            $derives_e = false;
            if ($s === P_EPSILON) {
                $derives_e = true;
            } else if ($this->is_terminal($s)) {
                $set[$s] = $s;
            } else if (!isset($sets[$s])) {
                throw new Exception("No $type($s)");
                break;
            } else {
                foreach ($sets[$s] as $t) {
                    if ($t === P_EPSILON) {
                        $derives_e = true;
                    } else {
                        $set[$t] = $t;
                    }
                }
            }
        } while ($derives_e && ($s = next($sequence)) && $s !== P_EOF);
        return $set;
    }

    function is_terminal($s)
    {
        is_array($s) and $s = $s[0];
        return isset($this->ts[$s]);
    }

    function follow_set($s)
    {
        if (!isset($this->follows[$s])) {
            $type = $this->is_terminal($s) ? 'terminal' : 'non-terminal';
            trigger_error("No follow set for $type $s", E_USER_WARNING);
            return [];
        }
        return $this->follows[$s];
    }
}
