<?php

namespace TimWhitlock\JavaScript\JParser;

abstract class JNodeBaseTransparentAlwaysFalse extends JNodeBase
{
    final function is_transparent(JNodeBase $Parent)
    {
        return false;
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
