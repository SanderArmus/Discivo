<?php

namespace App\Http\Controllers\Admin;

use App\Models\Disc;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class AdminDiscsController
{
    public function index(Request $request): Response
    {
        $user = $request->user();
        if ($user === null || $user->role !== 'admin') {
            abort(403);
        }

        $validated = $request->validate([
            'q' => ['nullable', 'string', 'max:100'],
            'status' => ['nullable', 'string', Rule::in(['lost', 'found'])],
            'active' => ['nullable', 'string', Rule::in(['1', '0'])],
            'lifecycle' => ['nullable', 'string', Rule::in(['confirmed', 'handed_over'])],
            'sort' => ['nullable', 'string', Rule::in(['created_at', 'occurred_at', 'id'])],
            'dir' => ['nullable', 'string', Rule::in(['asc', 'desc'])],
        ]);

        $q = $validated['q'] ?? null;
        $status = $validated['status'] ?? null;
        $active = $validated['active'] ?? null;
        $lifecycle = $validated['lifecycle'] ?? null;
        $sort = $validated['sort'] ?? 'created_at';
        $dir = $validated['dir'] ?? 'desc';

        $query = Disc::query()
            ->with(['user', 'colors'])
            ->when($q, function ($query) use ($q) {
                $term = '%'.trim($q).'%';
                $query->where(function ($q2) use ($term) {
                    $q2->where('manufacturer', 'like', $term)
                        ->orWhere('model_name', 'like', $term)
                        ->orWhere('plastic_type', 'like', $term)
                        ->orWhere('back_text', 'like', $term)
                        ->orWhereHas('user', function ($uq) use ($term) {
                            $uq->where('name', 'like', $term)
                                ->orWhere('username', 'like', $term)
                                ->orWhere('email', 'like', $term);
                        });
                });
            })
            ->when($status, fn ($q2) => $q2->where('status', $status))
            ->when($active !== null, fn ($q2) => $q2->where('active', (bool) ((int) $active)))
            ->when($lifecycle, fn ($q2) => $q2->where('match_lifecycle', $lifecycle))
            ->orderBy($sort, $dir);

        $discs = $query->paginate(25)->withQueryString();

        return Inertia::render('Admin/Discs', [
            'filters' => [
                'q' => $q,
                'status' => $status,
                'active' => $active,
                'lifecycle' => $lifecycle,
                'sort' => $sort,
                'dir' => $dir,
            ],
            'discs' => $discs->through(fn (Disc $disc) => [
                'id' => $disc->id,
                'status' => $disc->status,
                'occurredAt' => $disc->occurred_at?->format('Y-m-d H:i:s'),
                'manufacturer' => $disc->manufacturer,
                'modelName' => $disc->model_name,
                'plasticType' => $disc->plastic_type,
                'backText' => $disc->back_text,
                'conditionEstimate' => $disc->condition_estimate,
                'active' => (bool) $disc->active,
                'matchLifecycle' => $disc->match_lifecycle,
                'colors' => $disc->colors->pluck('name')->values(),
                'owner' => [
                    'id' => $disc->user?->id,
                    'name' => $disc->user?->name,
                    'username' => $disc->user?->username,
                    'email' => $disc->user?->email,
                ],
                'createdAt' => $disc->created_at?->format('Y-m-d H:i:s'),
            ]),
        ]);
    }

    public function update(Request $request, Disc $disc): RedirectResponse
    {
        $user = $request->user();
        if ($user === null || $user->role !== 'admin') {
            abort(403);
        }

        $validated = $request->validate([
            'status' => ['nullable', 'string', Rule::in(['lost', 'found'])],
            'active' => ['nullable', 'boolean'],
            'matchLifecycle' => ['nullable', 'string', Rule::in(['confirmed', 'handed_over'])],
        ]);

        if (array_key_exists('status', $validated)) {
            $disc->status = $validated['status'];
        }

        if (array_key_exists('active', $validated)) {
            $disc->active = (bool) $validated['active'];
        }

        if (array_key_exists('matchLifecycle', $validated)) {
            $disc->match_lifecycle = $validated['matchLifecycle'];
        }

        $disc->save();

        return redirect()->back();
    }
}
