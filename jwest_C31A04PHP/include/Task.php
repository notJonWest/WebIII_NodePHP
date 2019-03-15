<?php
declare(strict_types=1);
require("./include/MultiSort.php");
class Task extends MultiSort implements JsonSerializable
{
    private $id;
    private $title;
    private $description;
    private $dateCreated;
    private $dateModified;
    private $status;

    private const nextNumFile = "./List/nextTaskId.dat";

    public const STATUSES = [
        "all",
        "todo",
        "indev",
        "intest",
        "complete"
    ];

    public function __construct($data)
    {
        $this->fill($data);
        if (!isset($this->id))
            $this->id = self::getNextId(true);
    }

    public function fill($info)
    {
        foreach ($info as $key => $val)
            $this->{$key} = $val;
    }

    public function getDateCreated()
    {
        return $this->dateCreated;
    }
    public function getDateModified()
    {
        return $this->dateModified;
    }
    public function getId()
    {
        return $this->id;
    }
    public function getTitle()
    {
        return $this->title;
    }
    public function getDescription()
    {
        return $this->description;
    }
    public function getStatus()
    {
        return $this->status;
    }

    public function setDateCreated($c)
    {
        $this->dateCreated = $c;
    }
    public function setDateModified($m)
    {
        $this->dateModified = $m;
    }
    public function setId($id)
    {
        $this->id = $id;
    }
    public function setTitle($t)
    {
        $this->title = $t;
    }
    public function setDescription($d)
    {
        $this->description = $d;
    }
    public function setStatus($s)
    {
        $this->status = $s;
    }

    static public function getNextId(bool $increment = false)
    {
        if (!file_exists(self::nextNumFile))
            file_put_contents(self::nextNumFile, 0);
        $nextId = (int) file_get_contents(self::nextNumFile);
        if ($increment)
            file_put_contents(self::nextNumFile, $nextId + 1);
        return $nextId;
    }

    static public function numToStatus($s)
    {
        switch ($s)
        {
            case 0:
                return "All";
            case 1:
                return "To Do";
            case 2:
                return "In Development";
            case 3:
                return "Testing";
            case 4:
                return "Complete";
            default:
                return "Unknown";
        }
    }

    public function jsonSerialize()
    {
        return [
            "id" => $this->id,
            "title" => $this->title,
            "description" => $this->description,
            "dateCreated" => $this->dateCreated,
            "dateModified" => $this->dateModified,
            "status" => $this->status
        ];
    }

}