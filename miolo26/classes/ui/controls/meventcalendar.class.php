<?php

/**
 * MEventCalendar Class
 * Display tasks on calendar format
 *
 * @author Daniel Hartmann [daniel@solis.coop.br]
 *
 * \b Maintainers: \n
 * Armando Taffarel Neto [taffarel@solis.coop.br]
 * Daniel Hartmann [daniel@solis.coop.br]
 *
 * @since
 * Creation date 2011/03/09
 *
 * \b Organization: \n
 * SOLIS - Cooperativa de Solu��es Livres \n
 *
 * \b Copyright: \n
 * Copyright (c) 2011 SOLIS - Cooperativa de Solu��es Livres \n
 *
 * \b License: \n
 * Licensed under GPLv2 (for further details read the COPYING file or http://www.gnu.org/licenses/gpl.html)
 *
 */

class MEventCalendar extends MDiv
{
    /**
     * Constants used to set the special days and/or the first day of the week
     */
    const SUNDAY = 1;
    const MONDAY = 2;
    const TUESDAY = 3;
    const WEDNESDAY = 4;
    const THURSDAY = 5;
    const FRIDAY = 6;
    const SATURDAY = 7;

    /**
     * @var array The calendar events
     */
    private $events;

    /**
     * @var array The calendar configuration array
     */
    private $options;

    /**
     * @var array Links between calendar dates
     */
    private $linkDates;

    /**
     * @var integer Initial date on format YYYYMM
     */
    private $initialDate;
    
    public $js;

    /**
     * The MEventCalendar constructor
     *
     * @param string $name The component id
     * @param string $title The calendar title
     * @param integer $firstMonth The first month to be displayed on YYYYMM format
     * @param integer $lastMonth The last month to be displayed on YYYYMM format
     * @param integer $firstDayOfWeek The first day of week. Use the day constants of this class.
     * @param boolean $linkNewWindow Whether the event link should be opened on a new window
     * @param integer $specialDay The day which will have a different appearence. Default is Sunday. Use the day constants of this class.
     * @param integer $initialDate Initial date format YYYYMM
     */
    public function __construct($name, $title, $firstMonth=NULL, $lastMonth=NULL, $firstDayOfWeek=NULL, $linkNewWindow=false, $specialDay=self::SUNDAY, $initialDate=NULL)
    {
        parent::__construct($name);

        $this->page->addScript('calendar/m_eventcalendar.js');
        $this->page->addStyle('m_eventcalendar.css');

        $this->events = array();
        $this->options = array();

        $this->options['tableClass'] = 'm-event-calendar';
        $this->options['title'] = $title;
        $this->options['firstMonth'] = $firstMonth;
        $this->options['lastMonth'] = $lastMonth;
        $this->options['firstDayOfWeek'] = $firstDayOfWeek;
        $this->options['linkNewWindow'] = $linkNewWindow;
        $this->options['specialDay'] = $specialDay;

        $this->initialDate = $initialDate;
    }

    /**
     * Add an event on the given day
     *
     * @param integer $date Event date on YYYYMMDD format
     * @param string $description Event description
     * @param string $link Event link
     * @param string $image Relative path of an image to be displayed at the event description
     * @param int $imageWidth Image width in pixels
     * @param int $imageHeight Image height in pixels
     */
    public function defineEvent($date, $description, $link=NULL, $image=NULL, $imageWidth=NULL, $imageHeight=NULL)
    {
        $event = array();
        $event['eventDate'] = $date;
        $event['eventDescription'] = $description;
        $event['eventLink'] = $link;
        $event['eventImage'] = $imageHeight;
        $event['eventImageWidth'] = $imageWidth;
        $event['eventImageHeight'] = $imageHeight;

        $this->events[] = $event;
    }

    /**
     * @param integer $initialDate Set the initial date on format YYYYMM
     */
    public function setInitialDate($initialDate)
    {
        $this->initialDate = $initialDate;
    }

    /**
     * @return integer Get the initial date on format YYYYMM
     */
    public function getInitialDate()
    {
        return $this->initialDate;
    }

    /**
     * @param array $events Set the calendar events
     */
    public function setEvents($events)
    {
        $this->events = $events;
    }

    /**
     * @return array Return the calendar events
     */
    public function getEvents()
    {
        return $this->events;
    }

    /**
     * @param string $title Set the calendar title
     */
    public function setTitle($title)
    {
        $this->options['title'] = $title;
    }

    /**
     * @param integer $firstMonth Set the first month to be displayed on YYYYMM format
     */
    public function setFirstMonth($firstMonth)
    {
        $this->options['firstMonth'] = $firstMonth;
    }

    /**
     * @param integer $lastMonth Set the last month to be displayed on YYYYMM format
     */
    public function setLastMonth($lastMonth)
    {
        $this->options['lastMonth'] = $lastMonth;
    }

    /**
     * @param integer $firstDayOfWeek Set the first day of week. Use the day constants of this class.
     */
    public function setFirstDayOfWeek($firstDayOfWeek)
    {
        $this->options['firstDayOfWeek'] = $firstDayOfWeek;
    }

    /**
     * @param boolean $linkNewWindow Set whether the event link should be opened on a new window
     */
    public function setLinkNewWindow($linkNewWindow)
    {
        $this->options['linkNewWindow'] = $linkNewWindow;
    }

    /**
     * @param integer $specialDay Set the day which will have a different appearence
     */
    public function setSpecialDay($specialDay)
    {
        $this->options['specialDay'] = $specialDay;
    }

    /**
     * @param array $specialDays Set the days which will have a different appearence
     */
    public function setSpecialDays($specialDays)
    {
        $this->options['specialDays'] = $specialDays;
    }

    /**
     * @param array $months Set the month names. The array must have the 12 months
     */
    public function setMonths($months)
    {
        $this->options['months'] = $months;
    }

    /**
     * @param array $weekdays Set the weekday names. The array must have the 7 weekdays
     */
    public function setWeekdays($weekdays)
    {
        $this->options['weekdays'] = $weekdays;
    }

    /**
     * Set to a value to an option
     * The options can be found here: http://mysite.verizon.net/kilsen/calendar/tutorial.html
     *
     * @param string $option
     * @param mixed $value The value can be an integer, string, boolean or array
     */
    public function setOption($option, $value)
    {
        $this->options[$option] = $value;
    }

    /**
     * Define a link to the given day
     *
     * @param integer $date Date on format YYYYMMDD
     * @param string $link Absolute URL
     */
    public function defineDateLink($date, $link)
    {
        $this->linkDates[$date] = array(
            'linkedDate' => $date,
            'dateLink' => $link
        );
    }

    /**
     * Remove the link from a date
     *
     * @param integer $date Date on format YYYYMMDD
     */
    public function removeDateLink($date)
    {
        unset($this->linkDates[$date]);
    }

    /**
     * @param boolean $dateLinkNewWindow Set whether the date link should be opened on a new window
     */
    public function setDateLinkNewWindow($dateLinkNewWindow)
    {
        $this->options['dateLinkNewWindow'] = $dateLinkNewWindow;
    }

    /**
     * @return string The generated component to display
     */
    public function generate()
    {
        // Instantiate the calendar with the configured options
        $options = json_encode($this->options);
        $js = "mEventCalendar_$this->name = new JEC('$this->name', $options);\n\r\n\r";
        
        // Set events        
        foreach($this->events as $e)
        {
            $js .= "mEventCalendar_$this->name.defineEvent('{$e['eventDate']}', '{$e['eventDescription']}', '{$e['eventLink']}', '{$e['image']}', '{$e['imageWidth']}', '{$e['imageHeight']}');\n\r\n\r";
        }

        // Set date links
        $linkDates = json_encode($this->linkDates);
        $js .= "mEventCalendar_$this->name.linkDates($linkDates);\n\r\n\r";

        // Show calendar
        $js .= "mEventCalendar_$this->name.showCalendar($this->initialDate);\n\r\n\r";

        $this->page->addJsCode($js);
        
        return parent::generate();
    }
}

?>