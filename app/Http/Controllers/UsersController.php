<?php

namespace App\Http\Controllers;

use App\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Str;

class UsersController extends Controller
{
    /**
     * @param Request  $request
     * @param int|null $id
     *
     * @return JsonResponse
     */
    public function get(Request $request, int $id = null): JsonResponse
    {
        $user = $id === null ? User::where('is_active', 1)->get() : User::find($id);

        if ($user === null) {
            return response()->json(
                [
                    'success' => false,
                    'error'   => [
                        'message' => "Unrecognized user ID ({$id}).",
                        'code'    => 'invalid_user_id_error'
                    ]
                ],
                400
            );
        }

        return response()->json(
            [
                'success' => true,
                'data'    => $user->toArray()
            ]
        );
    }

    /**
     * @param Request  $request
     * @param int|null $id
     *
     * @return JsonResponse
     */
    public function create(Request $request, int $id = null): JsonResponse
    {
        $errors = $data = [];

        foreach (['name'] as $field) {
            if ($request[$field] === null) {
                $errors[] = $field;
            } else {
                $data[$field] = (string)$request[$field];
            }
        }

        foreach (['login', 'password'] as $field) {
            $data[$field] = (string)$request[$field];
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

        return response()->json(
            [
                'success' => true,
                'data'    => User::updateOrCreate(['id' => $id], $data)
            ],
            201
        );
    }

    /**
     * @param Request  $request
     * @param int|null $id
     *
     * @return JsonResponse
     */
    public function delete(Request $request, int $id = null): JsonResponse
    {
        $user = User::find($id);

        if ($user === null) {
            return response()->json(
                [
                    'success' => false,
                    'error'   => [
                        'message' => "Unrecognized user ID ({$id}).",
                        'code'    => 'invalid_user_id_error'
                    ]
                ],
                400
            );
        }

        $user->is_active = 0;
        $user->save();

        return response()->json(
            [
                'success' => true
            ]
        );
    }

    /**
     * @param Request  $request
     * @param int|null $id
     *
     * @return JsonResponse
     */
    public function getToken(Request $request, int $id): JsonResponse
    {
        $user = User::find($id);

        if ($user === null) {
            return response()->json(
                [
                    'success' => false,
                    'error'   => [
                        'message' => "Unrecognized user ID ({$id}).",
                        'code'    => 'invalid_user_id_error'
                    ]
                ],
                400
            );
        }

        $user->token = Str::random(80);
        $user->save();

        return response()->json(
            [
                'success' => true,
                'data'    => \array_merge(
                    $user->toArray(),
                    [
                        'token' => $user->token
                    ]
                )
            ]
        );
    }
}
