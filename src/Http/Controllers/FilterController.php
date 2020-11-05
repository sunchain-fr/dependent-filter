<?php

namespace AwesomeNova\Http\Controllers;

use Laravel\Nova\Http\Requests\NovaRequest;

class FilterController
{
    /**
     * @param \Laravel\Nova\Http\Requests\NovaRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function options(NovaRequest $request)
    {
        // fait la liste de tous les filters sur la page.
//        availableFilters --> donne les 4 filters
        $filter = $request->newResource()->availableFilters($request)->first(function ($filter) use ($request) {
            return $filter->key() === $request->query('filter');
            //operation_id / meter_id
            //meter_id / meter_id
        });

        if (!$filter) abort(404);

        return response()->json(
        // $request->query('filters') == operation_id: 1
            //filter = MeterFilter
            $filter->getOptions($request, json_decode(base64_decode($request->query('filters')), true))
        );
    }
}
