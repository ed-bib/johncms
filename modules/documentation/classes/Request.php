<?php
/**
 * Created by PhpStorm.
 * User: Эдуард Бибик
 * Date: 25.11.2019
 * Time: 1:35
 */

namespace Doc;

class Request
{
    public const ROLE = 6;
    private $request;

    private function __construct()
    {
        $this->request['post'] = $_POST;
        $this->request['get'] = $_SERVER['REQUEST_URI'];
    }

    /**
     * @return Request
     */
    public static function make(): Request
    {
        return new Request;
    }

    /**
     * @return string
     */
    public function getData(): string
    {
        return explode('?', $this->request['get'])[1] ?? '';
    }

    /**
     * @return array
     */
    public function postData(): array
    {
        return $this->request['post'];
    }
}

