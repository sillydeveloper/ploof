<?
namespace core;

class Typograph
{
    static function to_human($value)
    {
        $lower = Typograph::to_lower($value, $uppercase_first_letter);
        return str_replace("_", " ", $lower);
    }
    
    static function to_lower($value)
    {
        if(strtolower($value) === $value)
            return $value;

        if (preg_match_all("/[A-Z][a-z]+/", ucfirst($value), $matches) > 1)
            return strtolower(implode("_", $matches[0]));

        return strtolower($value);
    }
    
    static function to_camel($value)
    {
        $value = str_replace("_", " ", $value);
        $value = ucwords($value);
        if (!$uppercase_first_letter)
        {
            $value = strtolower($value[0]);    
        }
        return str_replace(" ", "", $value);
    }
    
}
?>