<?php

namespace App\Service;


class ArrayService
{
    function addElement(&$array, $element) {
        try {
            if (!in_array($element, $array)) {
                if (is_numeric($element)) {
                    $element = (int)$element;
                }
                array_push($array, $element);
            }
            return $array;
        } catch (\Exception $e) {
            trigger_error('Element was not added! '.$e->getMessage());
            return [];
        }
    }

    function moveElement(&$array, $a, $b) {
        $out = array_splice($array, $a, 1);
        array_splice($array, $b, 0, $out);
    }

    function deleteElementByKey(&$array, $a, $b) {
        array_splice($array, $a, $b);
    }

    function deleteElementByValue(&$array, $element)
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