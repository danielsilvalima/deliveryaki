<?php

namespace App\Helpers;

use Symfony\Component\HttpFoundation\Response;

class ResponseHelper
{
  /**
     * Resposta de erro genérica.
     *
     * @param string $message
     * @param int $status
     * @param array $header
     * @return \Illuminate\Http\JsonResponse
     */
    public static function error(string $message, int $status = Response::HTTP_INTERNAL_SERVER_ERROR, array $header = [])
    {
        return response()->json(
            ['message' => $message],
            $status,
            $header
        );
    }

    /**
     * Resposta para recurso não encontrado.
     *
     * @param string $message
     * @param array $header
     * @return \Illuminate\Http\JsonResponse
     */
    public static function notFound(string $message, array $header = [])
    {
        return self::error($message, Response::HTTP_NOT_FOUND, $header);
    }

    /**
     * Resposta de sucesso genérica.
     *
     * @param string|array $data
     * @param int $status
     * @param array $header
     * @return \Illuminate\Http\JsonResponse
     */
    public static function success($data, int $status = Response::HTTP_OK, array $header = [])
    {
        return response()->json(
            is_array($data) ? $data : ['message' => $data],
            $status,
            $header
        );
    }
}
