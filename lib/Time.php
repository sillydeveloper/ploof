<?

class Time
{

    public static function IntervalTimesForSelect($interval = 15, $military = false)
    {
        if ( 60 % interval != 0 )
        {
            return false;
        }

        $list = array();
        // Ensure we're beginning at midnight
        $original_timezone = date_default_timezone_get();
        date_default_timezone_set('UTC');
        $time = strtotime(date('m-j-Y'));
        $steps = (1440 / $interval) - 1; // (1440 = minutes in a day)
        for ($i = 0; $i <= $steps; $i++) {
            $list[] = ( $military ) ? date('H:i', $time) : date('g:i A', $time);
            $time += ($interval * 60);
        }
        date_default_timezone_set($original_timezone);
        return $list;
    }

}

?>
