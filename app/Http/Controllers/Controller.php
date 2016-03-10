<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    /**
     * @var int
     */
    protected $statusCode = 200;
    
     /**
     * @return int
     */
    public function getStatusCode()
    {
        return $this->statusCode;
    }

    /**
     * @param int $statusCode
     *
     * @return $this
     */
    public function setStatusCode($statusCode)
    {
        $this->statusCode = $statusCode;

        return $this;
    }

    /**
     * Generic reponse
     *
     * @param $data
     * @param array $headers
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function respond($data, $headers = [])
    {
        return response()->json($data, $this->getStatusCode(), $headers);
    }

    /**
     * Generic error response with error message
     *
     * @param string $message
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function respondWithError($message)
    {
        return $this->respond([
            'error' => [
                'message'     => $message,
                'status_code' => $this->getStatusCode()
            ]
        ]);
    }
}
