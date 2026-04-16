<?php

/**
 * Created by PhpStorm.
 * User: Marcus
 * Date: 2015-04-12
 * Time: 10:17
 */
class Plugin_helpers extends Plugin
{

    public $version = '0.1.0';
    public $name = array(
        'en' => 'Helpers',
    );
    public $description = array(
        'en' => 'Collection of different helpers'

    );




    /**
     * Return true if page id exists in calendar and the iten is in the future
     * @return bool
     */
    public function isPageInCalendar()
    {

        $page_id = $this->attribute('page_id', 0);
        $params = [
            'namespace' => 'streams',
            'stream' => 'calendar',
            'where' => 'date>now() AND page=' . $page_id
        ];


        return count(ci()->streams->entries->get_entries($params)['entries']) > 0;
    }

    public function calendarNav()
    {

        //Do not show on homepage
        if ($this->attribute('is_home', 0)) {
            return '';
        };


        $showIfIncalendar = $this->attribute('showifincalendar', 0);
        $page_id = $this->attribute('page_id', 0);
        $params = [
            'namespace' => 'streams',
            'stream' => 'calendar',
            'where' => 'date>now() AND page=' . $page_id
        ];
        $inCalendar = (count(ci()->streams->entries->get_entries($params)['entries']) > 0);

        return ($inCalendar == $showIfIncalendar) ? $this->content() : '';
    }

    public function message()
    {

        return  $this->template->message;
    }

    public function sponsors()
    {
        return $this->content();
    }

    /**
     * Date
     *
     * Displays the current date or formats a date that is passed to it.
     *
     * Usage:
     *
     *     {{ helper:date format="Y" }}
     *
     *
     *
     *     {{ helper:date format="Y" date="2015-01-01" }}
     *
     *
     *
     * @return string
     */

    public function date()
    {
        $this->load->helper('date');


        $format = $this->attribute('format');
        $timestamp = $this->attribute('timestamp', now());

        // $timestamp = ($timestamp ? (new DateTime($timestamp))->getTimestamp() : now());


        if ($format == 'monthname') {

            $month = [
                '01' => 'Jan',
                '02' => 'Feb',
                '03' => 'Mars',
                '04' => 'April',
                '05' => 'Maj',
                '06' => 'Juni',
                '07' => 'Juli',
                '08' => 'Aug',
                '09' => 'Sept',
                '10' => 'Okt',
                '11' => 'Nov',
                '12' => 'Dec'
            ];


            return $month[format_date($timestamp, 'm')] ?: '---';

        }


        return format_date($timestamp, $format);
    }


}
