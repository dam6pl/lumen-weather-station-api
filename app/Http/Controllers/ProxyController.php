<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class ProxyController extends Controller
{
    /**
     * @param Request  $request
     * @param int|null $id
     *
     * @return JsonResponse
     */
    public function resolvePost(Request $request, int $id = null)
    {
        $errors = $data = [];

        foreach (['endpoint'] as $field) {
            if ($request->input($field) === null) {
                $errors[] = $field;
            } else {
                $data[$field] = (string)$request->input($field);
            }
        }

        if (\count($errors) !== 0) {
            $args = \implode(', ', $errors);
            return response()->json(
                [
                    'success' => false,
                    'error'   => [
                        'message' => "Missing required arguments ({$args}).",
                        'code'    => 'missing_required_arguments_error'
                    ]
                ],
                400
            );
        }



        $req = Request::create($data['endpoint'], 'POST', $request->input());
        $req->headers->set('X-Token-Auth', $request->header('X-Token-Auth'));
        $res = app()->handle($req);


        return response()->json(
            \json_decode($res->getContent(), true),
            $res->getStatusCode()
        );
    }
}
