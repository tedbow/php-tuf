<?php


namespace Tuf\Exception;


use TheSeer\Tokenizer\Exception;

trait ExceptionTrait
{

    protected static function throwWrappedExcpetion(\Exception $exception): void
    {
        throw new Exception($exception->getMessage(), $exception->getCode(), $exception);
    }
}