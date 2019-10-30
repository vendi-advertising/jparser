<?php

namespace TimWhitlock\JavaScript\JParser;

use Exception;

abstract class LRParser extends Parser
{
    /* @var LRParseTable */
    protected $Table;

    protected $stack = [];
    protected $Tree;
    protected $default_node_class = 'LRParseNode';

    /**
     * @param array $input
     *
     * @return mixed
     * @throws ParseError
     * @throws Exception
     */
    function parse(array $input)
    {
        parent::init($input);
        $state = 1;
        while (true) {
            $n = $this->Table->lookup($state, $this->t);
            if (is_null($n)) {
                $this->badtoken = $this->tok;
                $expected = $this->Table->permitted($state, $this->Grammar);
                if (!empty($expected)) {
                    $expected = 'expecting "' . $this->Lex->implode('", or "', $expected) . '"';
                } else {
                    $expected = 'no terminals permitted';
                }
                if (isset($this->stack[0])) {
                    $Node = end($this->stack);
                    //TODO: just fail?
                    if ($this->fail('after "%s" %s in state #%u', $this->Lex->name($Node->scalar_symbol()), $expected, $state)) {
                        continue;
                    } else {
                        break;
                    }
                    //TODO: just fail?
                } else if ($this->fail('%s in state #%u', $expected, $state)) {
                    continue;
                } else {
                    break;
                }
            }
            if ($n & 1) {
                if ($this->t === P_EOF) {
                    $len = count($this->stack);
                    if ($len !== 1) {
                        //TODO: just fail?
                        if ($this->fail('premature EOF stack has %u elements', $len)) {
                            continue;
                        } else {
                            break;
                        }
                    }
                    return $this->stack[0];
                }
                $state = $this->shift($state, $n);
            } else {
                $state = $this->reduce($state, $n);
            }
        }
    }

    private function shift($oldstate, $newstate)
    {
        $Node = $this->create_node($this->t);
        $Node->state($oldstate);
        if ($this->Grammar->is_terminal($this->t)) {
            $Node->terminate($this->tok);
        }
        $this->stack[] = $Node;
        $this->next_token();
        return $newstate;
    }

    private function reduce($state, $ruleid)
    {
        list($nt, $rhs) = $this->Grammar->get_rule($ruleid);
        if (!isset($rhs[1]) && $rhs[0] === P_EPSILON) {
            $Node = $this->create_node($nt);
            $Node->state($state);
            $this->stack[] = $Node;
            return $this->Table->lookup($state, $nt);
        }
        $len = count($rhs);
        $nodes = array_splice($this->stack, -$len);
        $Node = $this->create_node($nt);

        /* @var ParseNode $n */
        $n = $nodes[0];
        $oldstate = $n->state();

        foreach ($nodes as $i => $childNode) {
            $Node->push($childNode, $this->recursion);
        }
        $newstate = $this->Table->lookup($oldstate, $nt);
        if (is_null($newstate)) {
            $this->fail('no action permitted for (%u,%s)', $oldstate, $this->token_name($nt));
        }
        $Node->state($oldstate);
        $this->stack[] = $Node;
        return $newstate;
    }
}
