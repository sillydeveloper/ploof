<?php
namespace core;

class Encrypt extends Ploof 
{
    /**
     *  Encrypts the password.  First, we create a salt based on the user's information.
     *  We then shorten this salt to be the same length as the original password.  We take
     *  this shortened salt, combine it with the password, and hash it.  We then shorten this
     *  new hashed password, and combine it with the shortened salt, giving us a hash length
     *  equivalent to the hash length determined by the encryption technique.
     *
     *  @param string $content           String to be encrypted.
     *  @param int $user_id              The user ID of the user whose password we are encrypting         
     *  @param string $username          The pre-stripped username of the user whose corresponding password we are encrypting
     *  @param string $date_registered   The user's registration date in string format
     *  @param $enc_technique            The encryption technique to be used.
     *  @access public
     *  @return string
     */
    public function password($content, $user_id, $username, $date_registered, $enc_technique='sha256') {
        if ( !is_int($user_id) ) 
        {
            return false; 
        }
        
        if ( trim($date_registered) === '' || substr($date_registered, 0, 10) === '0000-00-00' )
        {
            return false; 
        }

        // Avoid DST problems by appending GMT
        $date_registered .= ' GMT';
        $salt_time = strtotime($date_registered);

        // Create different salts based on registration date
        switch ($salt_time % 4) 
        {
            case 0:
                $salt = substr($username, 0, 2) . substr($username, -2) . $user_id . substr($username, 2, -2);
                break;
            case 1:
                $salt = substr($username, -2) . substr($username, 2, -2) . $user_id . substr($username, 0, 2);
                break;
            case 2:
                $salt = substr($username, 3, -3) . $user_id . substr($username, -3) . substr($username, 0, 3);
                break;
            case 3:
                $salt = substr($username, 0, 3) . substr($username, 3, -3) . $user_id . substr($username, -3);
                break;
        }

        $salt = hash($enc_technique, $salt);
        $hash_length = strlen($salt);
        $password_length = strlen($content); 
        
        // Shorten the salt based on the length of the password
        $salt = substr($salt, 0, $password_length);
        
        // Get used chars (this will ensure that the salt + salted password length is the same length as a regular hash)
        $used_chars = ($hash_length - $password_length) * -1; 
        
        // Hash the salt and password, and then combine our original hashed salt with our new salted password
        switch ($salt_time % 2) 
        {
            case 0: 
                $salted_password = hash($enc_technique, $salt . $content);
                $final_result = $salt . substr($salted_password, $used_chars);
                break;
            case 1:
                $salted_password = hash($enc_technique, $content . $salt);
                $final_result = substr($salted_password, $used_chars) . $salt;
                break;
        }
        
        return $final_result;
    }

    public function random($length)
    {
         $rndstring = '';
         $list = array_merge(range('a', 'z'), range(0, 9));
         for ($i=0; $i<$length; $i++)
         {
             $b = rand(0, count($list) - 1);
             $rndstring .= $list[$b];
         }
         return $rndstring;
    }
    
}

?>
