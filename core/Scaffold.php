<?
namespace core;

class Scaffold
{
    private static $textbox_types= array("varchar", "datetime", "int");
    private static $textarea_types= array("text");
    
    static function build($object)
    {
        $ro= new \ReflectionObject($object);
        
        $html= "<table style='border:1px solid black; padding:5px'>\n";
        foreach($object->get_fields() as $key=>$value)
        {
            $html.= "  <tr style='border:1px solid black;'>\n";
            $html.= "      <td  >\n";
            $html.= "          ".$key."\n";
            $html.= "      </td>\n";
            $html.= "      <td>\n";
            
            if ($key == PRIMARY_KEY)
                $html.= "          ".$object->$key.self::hidden($key, $object->$key)."\n";
            elseif (in_array($object->get_field_type($key), self::$textbox_types))
                $html.= "          ".self::textbox($key, $object->$key)."\n";
            elseif (in_array($object->get_field_type($key), self::$textarea_types))    
                $html.= "          ".self::textarea($key, $object->$key)."\n";
            $html.= "      </td>\n";
            $html.= "  </tr>\n";
        }
        $html.=     "<tr><td colspan=2 align='right'><input type='submit' value='save' /></td>";
        $html.= "</table>";
        return $html;
    }
    
    static function hidden($key, $val)
    {
        return "<input type='hidden' name='data[".$key."]' value='".$val."' />";
    }
    
    static function textbox($key, $val)
    {
        return "<input type='text' name='data[".$key."]' value='".$val."' />";
    }
    
    static function textarea($key, $val)
    {
        return "<textarea name='data[".$key."]'  cols=80 rows=50>".$val."</textarea>";
    }
}

?>