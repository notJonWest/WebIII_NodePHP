<?php
declare(strict_types=1);

abstract class MultiSort
{
    static public function sort(...$flds)
    {
        return function($a, $b) use ($flds)
        {
            if (sizeof($flds) == 0)
                $flds = get_class_methods(get_called_class())[0];
            return self::order_by($a, $b, ...$flds);
        };
    }

    static protected function order_by(Task $a, Task $b, ...$gets): int
    {
        $sortArr = array_shift($gets);
        $getFld = null;
        $direction = 1;
        if (is_array($sortArr))
        {
            $getFld = self::convertToGetter($sortArr[0]);
            if (sizeof($sortArr) > 1)
                switch (strtolower($sortArr[1]))
                {
                    case "asc":
                    case "ascend":
                    case "ascending":
                    case "1":
                    case "up":
                    case "heaven":
                    case "high":
                        $direction = 1;
                        break;
                    case "desc":
                    case "descend":
                    case "descending":
                    case "-1":
                    case "down":
                    case "hell":
                    case "low":
                        $direction = -1;
                        break;
                    default:
                        throw new InvalidArgumentException("Invalid direction argument.");
                }
        }
        else
        {
            $getFld = self::convertToGetter($sortArr);
        }

        $result = strcasecmp((string) $a->$getFld(), (string) $b->$getFld()) * $direction;
        if ($result == 0)
            if (sizeof($gets) > 0)
                return self::order_by($a, $b, ...$gets);
            else
                return 0;
        else
            return $result;
    }

    static protected function convertToGetters(...$flds)
    {
        foreach ($flds as $key=>$fld)
            $flds[$key] = self::convertToGetter($fld);
        return $flds;
    }

    /*
     * @throws InvalidArgumentException
     */
    static protected function convertToGetter($fld)
    {
        $getter = "get".ucfirst($fld);
        if (in_array($getter, get_class_methods(get_called_class())))
            return $getter;
        else
            throw new InvalidArgumentException("Invalid Argument. $fld is not a accessible field in ".get_called_class());
    }
}