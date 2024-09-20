<?php

function successResponse($data, $message = null, $status = 200)
{
    return response()->json([
        'success' => true,
        'message' => $message,
        'data' => $data,
    ], $status);
}


function errorResponse($message, $status = 400)
{
    return response()->json([
        'success' => false,
        'message' => $message,
    ], $status);
}