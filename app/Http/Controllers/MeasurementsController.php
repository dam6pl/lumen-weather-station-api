<?php

namespace App\Http\Controllers;

use App\Measurement;
use App\Station;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MeasurementsController extends Controller
{
    /**
     * MeasurementsController constructor.
     */
    public function __construct()
    {
        $this->middleware('auth', ['only' => ['create']]);
        $this->middleware('authAdministrator', ['only' => ['delete']]);
    }

    /**
     * @param Request  $request
     * @param int|null $id
     *
     * @return JsonResponse
     */
    public function get(Request $request, int $id = null): JsonResponse
    {
        $measurement = $id === null ? 'all' : Measurement::where('id', $id)->where('is_active', 1);

        if ($measurement === null) {
            return response()->json(
                [
                    'success' => false,
                    'error'   => [
                        'message' => "Unrecognized measurement ID ({$id}).",
                        'code'    => 'invalid_measurement_id_error'
                    ]
                ],
                400
            );
        }

        if ($measurement === 'all') {
            $paginate = $request['per_page'] ?? 15;
            $single = $this->getMeasurementsStats($request)->paginate($paginate)->toArray();

            $measurement = $single['data'];
            $paginationData = [
                'pagination' => [
                    'results'      => (int)$single['total'],
                    'per_page'     => (int)$single['per_page'],
                    'current_page' => (int)$single['current_page'],
                    'last_page'    => (int)$single['last_page']
                ]
            ];
        } else {
            $results = $measurement->get()->toArray();

            if (!isset($results[0])) {
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

            $measurement = $results[0];
        }

        return response()->json(
            \array_merge(
                [
                    'success' => true,
                    'data'    => $measurement
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

        foreach (['station_id'] as $field) {
            if ($request->input($field) === null) {
                $errors[] = $field;
            } else {
                $data[$field] = $request->input($field);
            }
        }

        foreach (['temperature', 'pressure', 'humidity', 'illuminance'] as $field) {
            $data[$field] = $request->input($field) ?: null;
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

        if (Station::find($data['station_id']) === null) {
            return response()->json(
                [
                    'success' => false,
                    'error'   => [
                        'message' => "Unrecognized station ID ({$data['station_id']}).",
                        'code'    => 'incorrect_measurement_id_error'
                    ]
                ],
                400
            );
        }

        return response()->json(
            [
                'success' => true,
                'data'    => Measurement::updateOrCreate(['id' => $id], $data)
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
        $measurement = Measurement::find($id);

        if ($measurement === null) {
            return response()->json(
                [
                    'success' => false,
                    'error'   => [
                        'message' => "Unrecognized measurement ID ({$id}).",
                        'code'    => 'invalid_measurement_id_error'
                    ]
                ],
                400
            );
        }

        $measurement->is_active = 0;
        $measurement->save();

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
    public function getByStation(Request $request, int $id): JsonResponse
    {
        $paginate = $request['per_page'] ?? 15;
        $single = $this->getMeasurementsStats($request)
            ->where('station_id', $id)
            ->paginate($paginate)
            ->toArray();

        $measurement = $single['data'];
        $paginationData = [
            'pagination' => [
                'results'      => (int)$single['total'],
                'per_page'     => (int)$single['per_page'],
                'current_page' => (int)$single['current_page'],
                'last_page'    => (int)$single['last_page']
            ]
        ];

        return response()->json(
            \array_merge(
                [
                    'success' => true,
                    'data'    => $measurement
                ],
                $paginationData ?? []
            )
        );
    }

    /**
     * @param Request $request
     *
     * @return Builder
     */
    private function getMeasurementsStats(Request $request): Builder
    {
        $fromDate = $request['from'] ?? '1970-01-01 00:00:00';
        $toDate = $request['to'] ?? date('Y-m-d H:i:s');
        $interval = $request['interval'] ?? null;

        switch ($interval) {
            case 'hourly':
                $results = Measurement::select(
                    DB::raw('DATE_FORMAT(created_at, "%Y-%m-%d %H:59:59") as created_at, ROUND(AVG(temperature), 2) as temperature, ROUND(AVG(pressure), 2) as pressure, ROUND(AVG(humidity), 2) as humidity, ROUND(AVG(illuminance), 2) as illuminance')
                )
                    ->where('created_at', '>=', date('Y-m-d H:i:s', \strtotime($fromDate)))
                    ->where('created_at', '<=', date('Y-m-d H:i:s', \strtotime($toDate)))
                    ->groupBy(DB::raw('HOUR(created_at), DAY(created_at), MONTH(created_at), YEAR(created_at)'));
                break;
            case 'daily':
                $results = Measurement::select(
                    DB::raw('DATE_FORMAT(created_at, "%Y-%m-%d 23:59:59") as created_at, ROUND(AVG(temperature), 2) as temperature, ROUND(AVG(pressure), 2) as pressure, ROUND(AVG(humidity), 2) as humidity, ROUND(AVG(illuminance), 2) as illuminance')
                )
                    ->where('created_at', '>=', date('Y-m-d H:i:s', \strtotime($fromDate)))
                    ->where('created_at', '<=', date('Y-m-d H:i:s', \strtotime($toDate)))
                    ->groupBy(DB::raw('DAY(created_at), MONTH(created_at), YEAR(created_at)'));
                break;
            case 'monthly':
                $results = Measurement::select(
                    DB::raw('DATE_FORMAT(LAST_DAY(created_at), "%Y-%m-%d 23:59:59") as created_at, ROUND(AVG(temperature), 2) as temperature, ROUND(AVG(pressure), 2) as pressure, ROUND(AVG(humidity), 2) as humidity, ROUND(AVG(illuminance), 2) as illuminance')
                )
                    ->where('created_at', '>=', date('Y-m-d H:i:s', \strtotime($fromDate)))
                    ->where('created_at', '<=', date('Y-m-d H:i:s', \strtotime($toDate)))
                    ->groupBy(DB::raw('MONTH(created_at), MONTH(created_at), YEAR(created_at)'));
                break;
            default:
                $results = Measurement::where('created_at', '>=', date('Y-m-d H:i:s', \strtotime($fromDate)))
                    ->where('created_at', '<=', date('Y-m-d H:i:s', \strtotime($toDate)));
        }

        return $results->orderBy('created_at', 'desc');
    }
}
