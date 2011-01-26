<?php
namespace core;

class Format 
{

    public function date($d)
    {
        if (!$d or strtotime($d) == 0) return "";
        return date("m/d/Y", strtotime($d));
    }

    public function date_sql($d)
    {
        if (!$d or strtotime($d) == 0) return "";
        return date("Y-m-d H:i", strtotime($d));
    }

    public function float($f)
    {
        if (!is_numeric($f)) return "";
        if (is_string($f) and strpos($f,'e')!==false)
        {
            $f = sprintf('%F', $f);
        }
        return $f;
    }


}

?>
