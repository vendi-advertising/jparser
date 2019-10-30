<?php

namespace TimWhitlock\JavaScript\JParser;

class LRParseNode extends ParseNode
{
    protected $s;

    function state($s = null)
    {
        $i = $this->s;
        if (!is_null($s)) {
            $this->s = $s;
        }
        return $i;
    }

    function free_memory()
    {
        foreach ($this->children as $Child) {
            $Child->free_memory();
        }
        unset($this->s);
    }
}
