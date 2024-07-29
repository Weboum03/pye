<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller as BaseController;

class Controller extends BaseController
{
    use AuthorizesRequests, ValidatesRequests;

    /**
     * success response method.
     *
     * @param $result
     * @param $message
     * @return JsonResponse
     */
    public function sendResponse($result, $message): JsonResponse
    {
        $response = [
            'success' => true,
            'message' => $message,
            'data'    => $result,

        ];

        return response()->json($response, 200);
    }


    /**
     * success response.
     *
     * @param $result
     * @param $message
     * @return JsonResponse
     */
    public function sendSuccess($message): JsonResponse
    {
        $response = [
            'success' => true,
            'message' => $message,
        ];

        return response()->json($response, 200);
    }



    /**
     * success response method.
     *
     * @param $result
     * @param $message
     * @return JsonResponse
     */
    public function sendResponseWithPagination($result, $message): JsonResponse
    {
        $response = [
            'success' => true,
            'message' => $message,
            'totalRecords' => $result->total(),
            'currentPage' => $result->currentPage(),
            'lastPage' => $result->lastPage(),
            'perPage' => $result->perPage(),
            'data' => $result->items()
        ];

        return response()->json($response, 200);
    }


        /**
     * success response method.
     *
     * @param $result
     * @param $message
     * @return JsonResponse
     */
    public function sendResponseWithDatatable($result, $message): JsonResponse
    {
        $response = [
            'success' => true,
            'message' => $message,
            'recordsTotal' => $result->total(),
            'recordsFiltered' => $result->total(),
            'data' => $result->items(),
            'draw' => request()->draw
        ];

        return response()->json($response, 200);
    }

    /**
     * return error response.
     *
     * @param $error
     * @param [] $errorMessages
     * @param int $code
     * @return JsonResponse
     */
    public function sendError($error, $errorMessages = [], int $code = 422): JsonResponse
    {
        $response = [
            'success' => false,
            'message' => $error,
        ];

        if (!empty($errorMessages)) {
            $response['errors'] = $errorMessages;
        }
        return response()->json($response, $code);
    }
}
