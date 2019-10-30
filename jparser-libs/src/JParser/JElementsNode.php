<?php

namespace TimWhitlock\JavaScript\JParser;

class JElementsNode extends JNodeBase
{
    function obfuscate(array &$names)
    {
        $Funcs = $this->get_nodes_by_symbol(J_FUNC_DECL, 1);
        foreach ($Funcs as $Func) {
            $Identifier = $Func->get_child(1);
            if ($Identifier instanceof JIdentifierNode) {
                $Identifier->__obfuscate($names);
            }
        }
        parent::obfuscate($names);
        foreach ($Funcs as $i => $Func) {
            $scope = $names;
            $Func->obfuscate($scope);
        }
        foreach ($this->get_nodes_by_symbol(J_FUNC_EXPR) as $Func) {
            $scope = $names;
            $Func->obfuscate($scope);
        }
    }
}
