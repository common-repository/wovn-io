<?php
namespace Wovnio\Wovnwp\WpClasses\WovnOptions;

class RequestHandlerOptionWrapper
{
    const RH_FILE_GET_CONTENTS = 1;
    const RH_CURL = 2;
    const RH_ALL = 3; // self::RH_FILE_GET_CONTENTS | self::RH_CURL

    private $value;

    public function __construct($value)
    {
        $this->value = intval($value);
    }

    public function use_any()
    {
        return $this->value === self::RH_ALL;
    }

    public function use_curl()
    {
        return $this->value & self::RH_CURL;
    }

    public function use_file_get_contents()
    {
        return $this->value & self::RH_FILE_GET_CONTENTS;
    }
}
