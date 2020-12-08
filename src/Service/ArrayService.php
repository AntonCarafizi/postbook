<?php

namespace App\Service;


use Doctrine\DBAL\Driver\PDO\Exception;

class ArrayService
{
    function addElement($array, $element) {
        try {
            if (!in_array($element, $array)) {
                array_push($array, $element);
            }
            return $array;
        } catch (\Exception $e) {
            trigger_error('Element was not added! '.$e->getMessage());
            return [];
        }
    }

    function removeElement($array, $element)
    {
        try {
            if (in_array($element, $array)) {
                foreach (array_keys($array, $element) as $key) {
                    unset($array[$key]);
                }
            }
            return $array;
        } catch (\Exception $e) {
            trigger_error('Element was not removed! '.$e->getMessage());
            return [];
        }

    }
}