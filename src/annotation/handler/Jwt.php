<?php
declare (strict_types = 1);

namespace paa\annotation\handler;

use Doctrine\Common\Annotations\Annotation;
use think\annotation\handler\Handler;

final class Jwt extends Handler
{

    public function func(\ReflectionMethod $refMethod, Annotation $annotation, \think\route\RuleItem &$rule)
    {
        if ($this->isCurrentMethod($refMethod,$rule)){
            \paa\common\authorize\Jwt::check();
        }
    }

    
}
