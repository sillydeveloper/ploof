<?
namespace core;

class Coder
{
    static function random($length)
    {
         $rndstring = '';
         $list=array_merge(range('a','z'),range(0,9));
         for ($i=0; $i<$length; $i++)
         {
             $b = rand(0, count($list) - 1);
             $rndstring .= $list[$b];
         }
         return $rndstring;
    }
}
?>