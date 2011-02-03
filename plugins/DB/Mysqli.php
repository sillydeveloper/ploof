<?

class Mysqli implements core\PluginInterfaceDB
{
    function delete_row($table, $id)
    {
        $sql= 'delete from '.classname_only(static::classname()).' where '.PRIMARY_KEY.'="'.$this->fields[PRIMARY_KEY].'"';
        $this->debug(5, "Deleting- ".$sql);
        DB::query($sql);
    }
    
    function store_row($table, $data)
    {
        $existing= ($this->fields[PRIMARY_KEY] != null);
        
        if (array_key_exists("created_on", $this->fields) and !$this->fields["created_on"])
            $this->fields["created_on"]= date("Y-m-d H:i:s", time());
        
        if (array_key_exists("updated_on", $this->fields))
            $this->fields["updated_on"]= date("Y-m-d H:i:s", time());
        
        $field_query= array();

        // set it up:
        if ($existing)
        {
            $sql= "update ".classname_only(static::classname())." set ";
            
            $field_query= array();
            foreach($this->field_types as $k=>$v)
            {
                $v= $this->fields[$k];
                if ($k != PRIMARY_KEY)
                {
                    if (!is_object($v) and !is_array($v) and ($v === null or strlen($v) == 0)) 
                        $v= "NULL";
             
                    if ($k == PRIMARY_KEY) continue;
                
                    if (array_search($k, $this->no_cache) !== false)
                    {
                        // if it's in a no-cache state, then don't update it.
                    }
                    elseif ($this->is_belongs_to($k) and is_object($v))
                    {
                        // remember: it's a joiner object.
                    }
                    elseif ($this->is_numeric($k) or $v === "NULL" )
                    {
                        $field_query[]= $k."=".$this->sanitize($k, $v)."";
                    }
                    elseif ($this->is_foreign($k))
                    {
                        // if it's otherwise foreign, ignore it.
                    }
                    else
                        $field_query[]= $k."='".$this->sanitize($k, $v)."'";         
                }           
            }
            $sql.= implode(",", $field_query)." where ".PRIMARY_KEY."='".$this->fields[PRIMARY_KEY]."'";
        }
        else
        {   
            unset($this->fields[PRIMARY_KEY]);
            $field_query= array();				
            foreach($this->field_types as $k=>$v)
            {
                if ($k != PRIMARY_KEY)
                {
                    $v= $this->fields[$k];
                    
                    if ($v === null or strlen($v) == 0) { $v= "NULL";  }
                    
                    if (array_search($k, $this->no_cache) !== false)
                    {
                        // if it's in a no-cache state, then don't update it.
                        continue;   
                    }
                    elseif ($this->is_numeric($k) or $v === "NULL" )
                        $field_query[$k]= $this->sanitize($k, $v);
                    elseif ($this->is_belongs_to($k) and is_object($v))
                    {
                        $field_query[$k]= $this->sanitize($k, $v->id);
                    }
                    elseif ($this->is_foreign($k))
                    {
                        continue;
                    }
                    else
                        $field_query[$k]= '"'.$this->sanitize($k, $v).'"';
                }
            }
            
            $sql= 'insert into '.classname_only(static::classname()).'('.PRIMARY_KEY.', '.implode(',', array_keys($field_query)).') values(null, '.implode(',',array_values($field_query)).');';
        }
        
        $this->debug(5, $sql);
        
        // TODO update children
        DB::query($sql);

        if (!$existing)
        {
			$id= DB::insert_id();
			
            $this->fields[PRIMARY_KEY]= $id;
            $this->debug(5, "Set pk to ".$this->fields[PRIMARY_KEY]);
        }

        // think of $additional as a trigger.
        // this allows you to use a primary key that is not named like the others;
        //  to use it, override store() like:
        //      function store() { parent::store(array(to=>from)); }
        // this is not recommended for long term use due to indexing and other possible problems,
        //  but can be used to migrate from an old table system.
        if ($additional)
        {
            $sql= "update ".classname_only(static::classname())." set ";
            $field_array= array();
            foreach($additional as $from=>$to)
            {
                $field_array[]= $to."=".$from;
            }
            $sql.= implode(", ", $field_array)." where ".PRIMARY_KEY."=".$this->fields[PRIMARY_KEY];
            $this->debug(5, "Performing additional trigger: ".$sql);
            DB::query($sql);
        }
    }
}

?>