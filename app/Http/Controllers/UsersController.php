<?php

namespace App\Http\Controllers;

use App\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Str;

class UsersController extends Controller
{
    /**
     * UsersController constructor.
     */
    public function __construct()
    {
        $this->middleware('authAdministrator');
    }


    /**
     * @param Request  $request
     * @param int|null $id
     *
     * @return JsonResponse
     */
    public function get(Request $request, int $id = null): JsonResponse
    {
        $user = $id === null ? 'all' : User::find($id)->where('is_active', 1);

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

        if ($user === 'all') {
            $paginate = $request['per_page'] ?? 15;
            $single = User::where('is_active', 1)
                ->orderBy('created_at', 'desc')
                ->paginate((int)$paginate)->toArray();

            $user = $single['data'];
            $paginationData = [
                'pagination' => [
                    'results'      => (int)$single['total'],
                    'per_page'     => (int)$single['per_page'],
                    'current_page' => (int)$single['current_page'],
                    'last_page'    => (int)$single['last_page']
                ]
            ];
        } else {
            $user = $user->first()->toArray();
        }

        return response()->json(
            \array_merge(
                [
                    'success' => true,
                    'data'    => $user
                ],
                $paginationData ?? []
            )
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
            if ($request->input($field) === null) {
                $errors[] = $field;
            } else {
                $data[$field] = (string)$request->input($field);
            }
        }

        foreach (['login', 'password'] as $field) {
            $data[$field] = (string)$request->input($field);
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
