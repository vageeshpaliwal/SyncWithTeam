<?php

namespace App\Http\Controllers;

use App\Models\Holiday;
use App\Services\CurrentUserService;
use Illuminate\Http\Request;

class HolidayController extends Controller
{
    public function __construct(
        protected CurrentUserService $currentUserService
    ) {}

    public function index()
    {
        $org = $this->currentUserService->getOrganization();

        $holidays = Holiday::where('organization_id', $org->id)
            ->orderBy('date')
            ->get();

        return view('holidays.index', compact('holidays'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'date' => 'required|date',
            'description' => 'nullable|string|max:500',
            'is_optional' => 'nullable|boolean',
        ]);

        $org = $this->currentUserService->getOrganization();

        Holiday::create([
            'organization_id' => $org->id,
            'name' => $request->name,
            'date' => $request->date,
            'description' => $request->description,
            'is_optional' => $request->has('is_optional'),
        ]);

        return redirect()->route('holidays.index')->with('success', 'Holiday added successfully.');
    }

    public function destroy(Holiday $holiday)
    {
        $org = $this->currentUserService->getOrganization();

        if ($holiday->organization_id !== $org->id) {
            abort(404);
        }

        $holiday->delete();

        return redirect()->route('holidays.index')->with('success', 'Holiday deleted successfully.');
    }
}
