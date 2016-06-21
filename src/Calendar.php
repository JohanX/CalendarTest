<?php
namespace Calendar;

use DateTimeInterface;

class Calendar implements CalendarInterface
{
    /**
     * @var DateTimeInterface
     */
    private $datetime;

    /**
     * @param DateTimeInterface $datetime
     */
    public function __construct(DateTimeInterface $datetime)
    {
        $this->datetime = $datetime;
    }

    /**
     * Get the day
     *
     * @return int
     */
    public function getDay():int
    {
        return (int)$this->getDayOfMonth($this->datetime);
    }

    /**
     * Get the weekday (1-7, 1 = Monday)
     *
     * @return int
     */
    public function getWeekDay():int
    {
        return (int)$this->datetime->format('N');
    }

    /**
     * Get the first weekday of this month (1-7, 1 = Monday)
     *
     * @return int
     */
    public function getFirstWeekDay():int
    {
        return (int)$this->cloneDatetime()->modify('first day of this month')->format('N');
    }

    /**
     * Get the first week of this month (18th March => 9 because March starts on week 9)
     *
     * @return int
     */
    public function getFirstWeek():int
    {
        return $this->getWeek($this->cloneDatetime()->modify('first day of this month'));
    }

    /**
     * Get the number of days in this month
     *
     * @return int
     */
    public function getNumberOfDaysInThisMonth():int
    {
        return (int)$this->datetime->format('t');
    }

    /**
     * Get the number of days in the previous month
     *
     * @return int
     */
    public function getNumberOfDaysInPreviousMonth():int
    {
        return (int)$this->cloneDatetime()->modify('first day of this month')->modify('-1 day')->format('t');
    }

    /**
     * Get the calendar array
     *
     * @return array
     */
    public function getCalendar():array
    {

        $highlight = $this->getWeek($this->datetime) !== $this->getFirstWeek();

        // setting new DateTime object to Monday of the first week in current month
        $datetime = $this->cloneDatetime()->modify('first day of this month');

        // Ugly workaround for PHP bug that causes "this week" to return next week on Sundays
        $week = ((int)$datetime->format('w') === 0) ? 'last' : 'this';
        $datetime->modify('Monday ' . $week . ' week');

        $lastDayOfLastWeek = $this->cloneDatetime()->modify('last day of this month');

        // Ugly workaround for PHP bug that causes "this week" to return next week on Sundays
        if ((int)$lastDayOfLastWeek->format('w') !== 0) {
            $lastDayOfLastWeek->modify('Sunday this week');
        }

        $previousWeek = $this->getWeek($this->cloneDatetime()->modify('-1 week'));
        $calendar = [];

        while ($datetime <= $lastDayOfLastWeek) {
            $thisWeek = $this->getWeek($datetime);
            if (empty($calendar[$thisWeek])) {
                $calendar[$thisWeek] = [];
            }
            // only highlighting the previous week if the given date is not in the first week of the month
            $calendar[$thisWeek][$this->getDayOfMonth($datetime)] = ($highlight && $this->getWeek($datetime) == $previousWeek);
            $datetime->modify('+1 day');
        }
        return $calendar;
    }

    /**
     * Cloning Datetime object to prevent modifying class instance
     *
     * @return DateTimeInterface
     */
    private function cloneDatetime():DateTimeInterface
    {
        return clone $this->datetime;
    }

    /**
     * Get week number of specified datetime
     *
     * @param DateTimeInterface $datetime
     * @return int
     */
    private function getWeek(DateTimeInterface $datetime):int
    {
        return (int)$datetime->format('W');
    }

    /**
     * Get day of month for specified datetime
     *
     * @param DateTimeInterface $datetime
     * @return int
     */
    private function getDayOfMonth(DateTimeInterface $datetime):int
    {
        return (int)$datetime->format('j');
    }
}
