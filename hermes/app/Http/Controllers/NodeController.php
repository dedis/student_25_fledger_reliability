<?php

namespace App\Http\Controllers;

use App\Data\SimulationSnapshotData;
use App\Jobs\NodeUpdateJob;
use App\Models\Experiment;
use App\Models\Node;
use Gate;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class NodeController extends Controller
{
    use AuthorizesRequests;

    public function store(Request $request, Experiment $experiment)
    {
        Gate::authorize('create nodes');

        $data = $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $node = $experiment->nodes()->create($data);

        return response()->json(['id' => $node->id], Response::HTTP_CREATED);
    }

    public function update(Request $request, Node $node)
    {
        Gate::authorize('update nodes');

        $data = SimulationSnapshotData::validateAndCreate($request);
        $job = new NodeUpdateJob($node, $data, now());
        dispatch($job);

        $targetPageIds = collect($node->target_pages)->pluck('id')->toArray();
        $response = [
            'target_page_ids' => $targetPageIds,
            'job_id' => $job->job?->getJobId(),
        ];

        return response()->json($response, Response::HTTP_OK);
    }

    public function setTargetPages(Request $request, Node $node)
    {
        Gate::authorize('update nodes');

        $data = $request->validate([
            'stored_targets' => 'array|max:1024',
            'stored_targets.*' => 'string|max:255',
        ]);

        if (isset($data['stored_targets'])) {
            $node->stored_targets = $data['stored_targets'];
            $node->save();
        }

        $targetPageIds = collect($node->target_pages)->pluck('id')->toArray();

        return response()->json(['target_page_ids' => $targetPageIds], Response::HTTP_OK);
    }
}
