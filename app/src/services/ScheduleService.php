<?php

namespace App\services;

use App\Repository\ScheduleRepository;
use Symfony\Component\HttpClient\HttpClient;

class ScheduleService
{
    protected $apiKey;
    protected $url;

    /**
     * @param $startDate
     * @param $endDate
     * @return array
     * @throws \Exception
     */
    public function removeHolidays($startDate, $endDate, $holidays): array
    {
        $dates = [];
        $currDate = $startDate;
        while (date($currDate) <= date($endDate)) {
            if (false === in_array($currDate, $holidays)) {
                $dates[] = $currDate;
            }
            $currDateTime = new \DateTime($currDate);
            $currDateTime->add(new \DateInterval('P1D'));
            $currDate = (string)$currDateTime->format('Y-m-d');
        }
        return $dates;
    }

    /**
     * @param $startDate
     * @param $endDate
     * @return array
     * @throws \Exception
     */
    public function getHolidays($startDate, $endDate, $holidays): array
    {
        $dates = [];
        $currDate = $startDate;
        if (true === in_array($startDate, $holidays)) {
            $dates[] = $startDate;
        }
        //ищем праздники
        while (date($currDate) < date($endDate)) {
            $currDateTime = new \DateTime($currDate);
            $currDateTime->add(new \DateInterval('P1D'));
            $currDate = (string)$currDateTime->format('Y-m-d');
            if (true === in_array($currDate, $holidays)) {
                $dates[] = $currDate;
            }
        }
        return $dates;
    }

    /**
     * @param array $arr
     * @return array
     */
    public function krSort(array $arr): array
    {
        $krSortArr = [];
        foreach ($arr as $item) {
            $arr = json_decode(json_encode($item), true);
            krsort($arr);
            $krSortArr[] = $arr;
        }
        return $krSortArr;
    }

    /**
     * @param array $arr
     * @return array
     */
    public function sortInnerArrays(array $arr): array //метод сортировки пузырьком по значению ключа ['start']
    {
        $countElements = count($arr);
        $iterations = $countElements - 1;

        for ($i=0; $i < $countElements; $i++) {
            $changes = false;
            for ($j=0; $j < $iterations; $j++) {
                if (date_create_from_format('H:m', $arr[$j]['start']) > date_create_from_format('H:m', $arr[$j + 1]['start'])) {
                    $changes = true;
                    list($arr[$j], $arr[($j + 1)]) = array($arr[($j + 1)], $arr[$j]);
                }
            }
            $iterations--;
            if (!$changes) {
                return $arr;
            }
        }
        return $arr;
    }

    /**
     * @param string $date
     * @return bool
     */
    public function isHoliday(string $date, $holidays): bool
    {
        return in_array($date, $holidays);
    }
}