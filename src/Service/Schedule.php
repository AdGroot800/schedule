<?php
namespace App\Service;

use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Support\Collection;
use League\Csv\Writer;

class Schedule
{
    private $list;
    private $period;

    /**
     * Schedule constructor.
     */
    public function __construct()
    {
        $this->list = new Collection();
        return $this->setPeriod()
            ->schedule()
            ->generate();
    }

    /**
     * @return $this
     */
    public function generate()
    {
        $writer = Writer::createFromPath('file.csv', 'w+');
        $writer->insertAll($this->list->toArray());
        return $this;
    }

    /**
     * @return $this
     */
    public function setPeriod()
    {
        $this->period = CarbonPeriod::create(Carbon::now()->startOfMonth(), Carbon::now()->addMonth(3)->endOfMonth());
        return $this;
    }

    /**
     * @return $this
     */
    private function schedule()
    {
        foreach($this->period as $key => $date) {
            if($this->checkTaskVacuum($date))
            {
                $this->list->add([$date->format('d-M-Y'), 'Stofzuigen', date('H:i', mktime(0, 21))]);
            }
            if($this->checkTaskWindow($date)) {
                $this->list->add([$date->format('d-M-Y'), 'Ramen lappen', date('H:i', mktime(0, 35))]);
            }
            if($this->checkFridge($date))
            {
                $this->list->add([$date->format('d-M-Y'), 'Koelkast schoonmaken', date('H:i', mktime(0, 51))]);
            }
        }
        return $this;
    }


    private function isLastWeekOfMonth($date)
    {
        if(floor($date->daysInMonth / Carbon::DAYS_PER_WEEK) == $date->weekOfMonth) {
            return true;
        }
        return false;
    }

    private function isFirstWeekOfMonth($date) {
        if($date->weekOfMonth == 1) {
            return true;
        }
        return false;
    }

    private function checkTaskVacuum($date)
    {
        if($date->isWeekday() && in_array($date->format('l'), [ 'Tuesday', 'Thursday'])) {
            return true;
        }
        return false;
    }

    private function checkTaskWindow($date)
    {
        if($this->isLastWeekOfMonth($date) && $date->format('l') == 'Friday')
        {
            return true;
        }
        return false;
    }

    private function checkFridge($date)
    {
        if($this->checkTaskVacuum($date) && $this->isFirstWeekOfMonth($date)) {
            return true;
        }
        return false;
    }
}