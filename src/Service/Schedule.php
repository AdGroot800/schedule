<?php

namespace App\Service;

use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Support\Collection;

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
        return $this->setPeriod()->generate();
    }

    /**
     * @return Collection
     */
    public function generate(): Collection
    {
        foreach ($this->period as $key => $date) {
            if ($this->checkTaskVacuum($date)) {
                $this->list->add([$date->format('d-M-Y'), 'Stofzuigen', date('H:i', mktime(0, 21))]);
            }
            if ($this->checkTaskWindow($date)) {
                $this->list->add([$date->format('d-M-Y'), 'Ramen lappen', date('H:i', mktime(0, 35))]);
            }
            if ($this->checkFridge($date)) {
                $this->list->add([$date->format('d-M-Y'), 'Koelkast schoonmaken', date('H:i', mktime(0, 51))]);
            }
        }
        return $this->list;
    }

    private function checkTaskVacuum($date): bool
    {
        if ($date->isWeekday() && in_array($date->format('l'), ['Tuesday', 'Thursday'])) {
            return true;
        }
        return false;
    }

    private function checkTaskWindow($date): bool
    {
        if ($this->isLastWeekOfMonth($date) && $date->format('l') == 'Friday') {
            return true;
        }
        return false;
    }

    private function isLastWeekOfMonth($date): bool
    {
        if (floor($date->daysInMonth / Carbon::DAYS_PER_WEEK) == $date->weekOfMonth) {
            return true;
        }
        return false;
    }

    private function checkFridge($date): bool
    {
        if ($this->checkTaskVacuum($date) && $this->isFirstWeekOfMonth($date) && $this->onceAWeekOnFirstDay($date)) {
            return true;
        }
        return false;
    }

    private function isFirstWeekOfMonth($date): bool
    {
        if ($date->weekOfMonth == 1) {
            return true;
        }
        return false;
    }

    private function onceAWeekOnFirstDay($date): bool
    {
        if ($date->format('d') > 3 || $date->format('d') == 3 && $date->dayOfWeek == 4) {
            return false;
        }
        return true;
    }

    /**
     * @return $this
     */
    private function setPeriod(): Schedule
    {
        $this->period = CarbonPeriod::create(Carbon::now()->startOfMonth(), Carbon::now()->addMonths(2)->endOfMonth());
        return $this;
    }
}