<?php

namespace TimWhitlock\JavaScript\JParser;

class ParseNode
{
    private static $reg = [];
    private static $i = 0;
    var $depth = 0;
    protected $children = [];
    protected $length = 0;
    protected $t;
    protected $l;
    protected $c;
    protected $value;
    protected $recursion;
    private $idx;
    private $pidx;
    private $p;

    function __construct($t)
    {
        $this->t = $t;
        $this->idx = self::$i++;
        self::$reg[$this->idx] = $this;
    }

    static function get($idx)
    {
        return self::$reg[$idx];
    }

    static function destroy_all()
    {
        self::$reg = [];
        self::$i = 0;
    }

    function abs_pop()
    {
        $Node = $this->abs_end();
        if (is_null($Node->pidx)) {
            return false;
        }
        return $Node->get_parent()->pop();
    }

    function abs_end()
    {
        $Node = $this;
        while ($Child = end($Node->children)) {
            $Node = $Child;
        }
        return $Node;
    }

    function get_parent()
    {
        if (!is_null($this->pidx) && isset(self::$reg[$this->pidx])) {
            return self::$reg[$this->pidx];
        } else {
            return null;
        }
    }

    function scalar_symbol($s = null)
    {
        $t = $this->t;
        if (!is_null($s)) {
            $this->t = $s;
        }
        return $t;
    }

    function terminate($tok)
    {
        if (is_scalar($tok)) {
            if (is_null($this->t)) {
                $this->t = $tok;
            }
            $this->value = $tok;
        } else if (is_array($tok)) {
            $this->t = $tok[0];
            $this->value = $tok[1];
            if (isset($tok[2])) {
                $this->l = $tok[2];
                if (isset($tok[3])) {
                    $this->c = $tok[3];
                }
            }
        }
        while ($Child = $this->pop()) {
            $Child->destroy();
        }
    }

    function pop()
    {
        if (!$this->length) {
            return null;
        }
        if (--$this->length <= 0) {
            $this->length = 0;
            $this->p = null;
        } else {
            $this->p = 0;
        }
        $Node = array_pop($this->children);
        $Node->pidx = null;
        $Node->depth = 0;
        return $Node;
    }

    function get_child($i)
    {
        return isset($this->children[$i]) ? $this->children[$i] : null;
    }

    function get_line_num()
    {
        if (!isset($this->l)) {
            if (isset($this->children[0])) {
                $this->l = $this->children[0]->get_line_num();
            } else {
                $this->l = 0;
            }
        }
        return $this->l;
    }

    function get_col_num()
    {
        if (!isset($this->c)) {
            if (isset($this->children[0])) {
                $this->c = $this->children[0]->get_col_num();
            } else {
                $this->c = 0;
            }
        }
        return $this->c;
    }

    function push(ParseNode $Node, $recursion = true)
    {
        if ($Node->pidx) {
            trigger_error("Node $Node->idx already has parent $Node->pidx", E_USER_WARNING);
        }
        if ($this->t === $Node->t && $Node->length) {
            if (isset($Node->recursion)) {
                $recursion = $Node->recursion;
            }
            if (!$recursion) {
                $this->push_thru($Node);
                $Node->destroy();
                return $this->length;
            }
        }
        $Node->pidx = $this->idx;
        $Node->depth = $this->depth + 1;
        $this->p = 0;
        return $this->length = array_push($this->children, $Node);
    }

    function push_thru(ParseNode $Node)
    {
        foreach ($Node->children as $Child) {
            $Node->remove($Child);
            $this->push($Child);
        }
        return $this->length;
    }

    function remove(ParseNode $Node)
    {
        foreach ($this->children as $i => $Child) {
            if ($Child->idx === $Node->idx) {
                return $this->remove_at($i);
            }
        }
    }

    function remove_at($i)
    {
        $Child = $this->children[$i];
        $Child->pidx = null;
        $Child->depth = 0;
        array_splice($this->children, $i, 1);
        if (!--$this->length) {
            $this->p = null;
        } else {
            $this->p = 0;
        }
        return $Child;
    }

    function destroy()
    {
        while ($Child = $this->pop()) {
            $Child->destroy();
        }
        $Parent = $this->get_parent() and $Parent->remove($this);
        unset(self::$reg[$this->idx]);
        $this->idx = null;
        return $Parent;
    }

    function end()
    {
        if (!$this->length) {
            return false;
        }
        $Child = end($this->children);
        $this->p = key($this->children);
        return $Child;
    }

    function current()
    {
        if (!$this->length) {
            return false;
        }
        return $this->children[$this->p];
    }

    function prev()
    {
        if (!$this->length) {
            return false;
        }
        $p = $this->p - 1;
        if (!isset($this->children[$p])) {
            $this->p = 0;
            return false;
        }
        $this->p = $p;
        return $this->children[$p];
    }

    function key()
    {
        return $this->p;
    }

    function is_symbol($t)
    {
        return $this->t === $t;
    }

    function has_children()
    {
        return $this->length !== 0;
    }

    function get_nodes_by_symbol($t, $dmax = null, array $blocklist = null, $d = 0)
    {
        $a = [];
        if (!is_null($dmax) && $d > $dmax) {
            return $a;
        }
        if ($this->t === $t) {
            $a[] = $this;
        }
        if ($d && $blocklist && in_array($this->t, $blocklist, true)) {
            return $a;
        }
        $d++;
        foreach ($this->children as $Child) {
            $a = array_merge($a, $Child->get_nodes_by_symbol($t, $dmax, $blocklist, $d));
        }
        return $a;
    }

    function evaluate()
    {
        if ($this->is_terminal()) {
            return $this->value;
        }
        if ($this->t === P_EPSILON) {
            return null;
        }
        $values = [];
        $Child = $this->reset();
        do {
            $value = $Child->evaluate();
            if (!is_null($value)) {
                $values[] = $value;
            }
        } while ($Child = $this->next());
        return $values;
    }

    function is_terminal()
    {
        return !is_null($this->value);
    }

    function reset()
    {
        if (!$this->length) {
            return false;
        }
        $this->p = 0;
        return $this->children[0];
    }

    function next()
    {
        if (!$this->length) {
            return false;
        }
        $p = $this->p + 1;
        if (!isset($this->children[$p])) {
            $this->p = 0;
            return false;
        }
        $this->p = $p;
        return $this->children[$p];
    }

    function __toString()
    {
        if ($this->is_terminal()) {
            return (string)$this->value;
        }
        $s = '';
        foreach ($this->children as $Child) {
            $s .= $Child->__toString();
        }
        return $s;
    }

    function export()
    {
        if ($this->is_terminal()) {
            if ($this->t === $this->value) {
                return $this->value;
            }
            return [$this->t, $this->value];
        }
        $a = [];
        foreach ($this->children as $Child) {
            $a[] = [$this->t, $Child->export()];
        }
        return $a;
    }

    function resolve_recursion()
    {
        $copy = $this->children;
        foreach ($copy as $i => $Child) {
            $Child->resolve_recursion();
            if ($Child->t === $this->t) {
                $this->splice($Child, $Child->children);
            }
        }
    }

    function splice(ParseNode $Node, array $nodes)
    {
        foreach ($this->children as $i => $Child) {
            if ($Child->idx === $Node->idx) {
                return $this->splice_at($i, $nodes, 1);
            }
        }
    }

    function splice_at($i, array $nodes, $len = 0)
    {
        $Child = $this->children[$i];
        $Child->pidx = null;
        $Child->depth = 0;
        array_splice($this->children, $i, $len, $nodes);
        foreach ($nodes as $Node) {
            $Node->pidx = $this->idx;
        }
        $this->length = count($this->children);
        $this->p = 0;
        return $Child;
    }

    function dump(Lex $Lex, $tab = '')
    {
        $tag = $Lex->name($this->t);
        if ($this->is_terminal()) {
            if ($this->value && $this->value !== $this->t) {
                echo $tab, '<', $tag, ">\n   ", $tab, htmlspecialchars($this->value), "\n", $tab, '</', $tag, ">\n";
            } else {
                echo $tab, htmlspecialchars($this->value), "\n";
            }
        } else if (!$this->length) {
            echo $tab, '<', $tag, " />\n";
        } else {
            echo $tab, '<', $tag, ">\n";
            foreach ($this->children as $Child) {
                $Child->dump($Lex, "   " . $tab);
            }
            echo $tab, '</', $tag, ">\n";
        }
    }
}
