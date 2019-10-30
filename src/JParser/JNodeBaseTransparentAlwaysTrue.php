<?php

namespace TimWhitlock\JavaScript\JParser;

class JNodeBaseTransparentAlwaysTrue
{
    final function is_transparent(JNodeBase $Parent)
    {
        return true;
    }

    final function __toString()
    {
        return parent::__toString();
    }

    final function evaluate()
    {
        return parent::evaluate();
    }
}
