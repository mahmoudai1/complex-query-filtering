<?php

namespace App\Http\Controllers;

use App\ApiResponseTrait;
use App\Models\Job;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Services\JobFilterService;
use Illuminate\Support\Facades\DB;
use App\Http\Requests\JobSearchRequest;

class JobController extends Controller
{
    use ApiResponseTrait;

    public function index(JobSearchRequest $request,  JobFilterService $filterService)
    {
        try {
            $filterString = $request->input('filter');

            $offset = $request->input('offset') ?? 0;
            $limit = $request->input('limit') ?? 25;

            $filters = $filterService->customFilterParser($filterString);
            $query = Job::query()->select('jobs.*');

            $filterService->applyFilters($query, $filters);
            $jobs = $query->offset($offset)->limit($limit)->get();

            if(count($jobs) == 0){
                return $this->responseHandler(true, 'No data found', [], 200);
            }

            return $this->responseHandler(true, 'Successfully found ' . count($jobs) . ' ' . Str::plural('Job', $jobs), $jobs, 200);
        } catch (\Exception $e) {
            return $this->responseHandler(false, 'The query appears to be incorrect. Please review and try again.', [], 500);
        }
    }
}
