<?php

namespace App\Http\Controllers;

use App\Station;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class StationsController extends Controller
{

    /**
     * @param Request  $request
     * @param int|null $id
     *
     * @return JsonResponse
     */
    public function get(Request $request, int $id = null): JsonResponse
    {
        $station = $id === null ? Station::where('is_active', 1)->get() : Station::find($id);

        if ($station === null) {
            return response()->json(
                [
                    'success' => false,
                    'error'   => [
                        'message' => "Unrecognized station ID ({$id}).",
                        'code'    => 'invalid_station_id_error'
                    ]
                ],
                400
            );
        }

        return response()->json(
            [
                'success' => true,
                'data'    => $station->toArray()
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

        foreach (['name', 'latitude', 'longitude'] as $field) {
            if ($request[$field] === null) {
                $errors[] = $field;
            } else {
                $data[$field] = (string)$request[$field];
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


        return response()->json(
            [
                'success' => true,
                'data'    => Station::updateOrCreate(['id' => $id], $data)
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
        $station = Station::find($id);

        if ($station === null) {
            return response()->json(
                [
                    'success' => false,
                    'error'   => [
                        'message' => "Unrecognized station ID ({$id}).",
                        'code'    => 'invalid_station_id_error'
                    ]
                ],
                400
            );
        }

        $station->is_active = 0;
        $station->save();

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
    public function getMeasurements(Request $request, int $id = null): JsonResponse
    {
        $station = $id === null ? null : Station::find($id);

        if ($station === null) {
            return response()->json(
                [
                    'success' => false,
                    'error'   => [
                        'message' => "Unrecognized station ID ({$id}).",
                        'code'    => 'invalid_station_id_error'
                    ]
                ],
                400
            );
        }

        return (new MeasurementsController())->getByStation($request, $id);
    }
}
