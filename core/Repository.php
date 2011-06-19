<?
// Copyright (c) 2010, ploof development team
// All rights reserved.
// 
// Redistribution and use in source and binary forms, with or without modification, are permitted provided 
// that the following conditions are met:
// 
// Redistributions of source code must retain the above copyright notice, this list of conditions and the 
// following disclaimer. 
// Redistributions in binary form must reproduce the above copyright notice, this list of 
// conditions and the following disclaimer in the documentation and/or other materials provided with the distribution.
// The names of its contributors may not be used to endorse or promote products derived from this software without 
// specific prior written permission.
// THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, 
// INCLUDING, BUT NOT LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE 
// ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT HOLDER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT, 
// INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE 
// GOODS OR SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY OF 
// LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY 
// OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.

namespace core;

class Repository implements PluginInterfaceDB, PluginInterfaceCache
{
    private $db;
    private $cache;
    
    public function __construct(PluginInterfaceDB $db=null, PluginInterfaceCache $cache=null)
    {
        $this->db= $db;
        $this->cache= $cache;
    }
    
    public function set_database(PluginInterfaceDB $db)
    {
        $this->db= $db;
    }
    
    public function set_cache(PluginInterfaceCache $cache)
    {
        $this->cache= $cache;
    }
    
    public function get_database()
    {
        return $this->db;
    }
    
    public function get_cache()
    {
        return $this->cache;
    }
    
    //-------------------------------------------------
    // by contract to PluginInterfaceCache
    //-------------------------------------------------
    public function add_to_cache($key, $value, $expiration=0, $server_key='')
    {
        return $this->cache->add_to_cache($key, $value, $expiration, $server_key);    
    }
    
    public function delete_from_cache($key, $time=0, $server_key='')
    {
        return $this->cache->delete_from_cache($key, $time, $server_key);
    }
    
    public function flush_cache($delay=0)
    {
        return $this->cache->flush_cache($delay);
    }
    
    public function get_from_cache($key, $cache_callback=null, &$cas_token=null, $server_key='')
    {
        return $this->cache->get_from_cache($key, $cache_callback, $cas_token, $server_key);
    }
    
    public function replace_in_cache($key, $value, $expiration=0, $server_key='')
    {
        return $this->cache->replace_in_cache($key, $value, $expiration, $server_key);
    }
    
    public function set_in_cache($key, $value, $expiration=0, $server_key='')
    {
        return $this->cache->set_in_cache($key, $value, $expiration, $server_key);
    }
    
    
    //-------------------------------------------------
    // by contract to PluginInterfaceDB:
    //-------------------------------------------------
    public function load_row($table, $id)
    {
        return $this->db->load_row($table, $id);
    }

    public function find_rows($table, $where_array)
    {
        return $this->db->find_rows($table, $where_array);
    }

    public function show_tables()
    {
        return $this->db->show_tables();
    }

    public function get_table_columns($table)
    {
        return $this->db->get_table_columns($table);
    }

    public function is_numeric_datatype($field_type)
    {
        return $this->db->is_numeric_datatype($field_type);
    }

    public function query($sql)
    {
        return $this->db->query($sql);
    }

    public function delete_row($table, $id)
    {
        $this->db->delete_row($table, $id);
    }

    public function store_row($table, $data)
    {
        return $this->db->store_row($table, $data);
    }

    public function is_date_datatype($field_type)
    {
        return $this->db->is_date_datatype($field_type);
    }
    
    
}

?>
