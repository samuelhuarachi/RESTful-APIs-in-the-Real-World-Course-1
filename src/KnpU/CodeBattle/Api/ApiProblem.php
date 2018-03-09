<?php

namespace KnpU\CodeBattle\Api;

class ApiProblem
{

    const TYPE_VALIDATION_ERROR = "validation_error";

    private static $titles = [
        self::TYPE_VALIDATION_ERROR => "There was a validation error",
    ];
    
    private $statusCode;

    private $type;

    private $title;

    private $extraData = array();

    /**
     * ApiProblem constructor.
     * @param $statusCode
     * @param $type
     * @param $title
     */
    public function __construct($statusCode, $type)
    {
        $this->statusCode = $statusCode;
        $this->type = $type;

        if(!isset(self::$titles[$type])) {
            throw new \Exception(sprintf(
                'No title for type "%s". Did yoiu make it up?',
                $type
            ));
        }

        $this->title = self::$titles[$type];
    }

    public function toArray()
    {
        return array_merge(
            $this->extraData,
            array(
                'status' => $this->statusCode,
                'type' => $this->type,
                'title' => $this->title
            )
        );
    }

    public function set($name, $value)
    {
        $this->extraData[$name] = $value;
    }

    public function getStatusCode()
    {
        return $this->statusCode;
    }

}