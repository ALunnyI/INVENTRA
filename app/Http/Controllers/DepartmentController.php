<?php

namespace App\Http\Controllers;

use App\Http\Requests\DepartmentRequest;
use App\Models\Department;
use App\Models\Loan;
use Illuminate\Http\Request;

class DepartmentController extends Controller
{
    public function index(Request $request)
    {
        $departments = Department::withCount('users')
            ->when($request->search, fn ($q, $s) => $q->where('name', 'like', "%{$s}%"))
            ->latest()
            ->paginate(10)
            ->withQueryString();

        $loanStats = Loan::active()
            ->join('users', 'users.id', '=', 'loans.user_id')
            ->selectRaw(
                'users.department_id, COUNT(*) as active_total, '
                . 'SUM(CASE WHEN loans.due_date < ? THEN 1 ELSE 0 END) as overdue_total',
                [today()->toDateString()]
            )
            ->groupBy('users.department_id')
            ->get()
            ->keyBy('department_id');

        return view('departments.index', compact('departments', 'loanStats'));
    }

    public function show(Department $department)
    {
        $users = $department->users()
            ->with('activeLoans.asset')
            ->withCount([
                'activeLoans',
                'activeLoans as overdue_loans_count' => fn ($q) => $q->whereDate('due_date', '<', today()),
            ])
            ->orderByDesc('overdue_loans_count') // yang terlambat tampil paling atas
            ->orderBy('name')
            ->get();

        $stats = [
            'members'   => $users->count(),
            'borrowers' => $users->where('active_loans_count', '>', 0)->count(),
            'borrowed'  => $users->sum('active_loans_count'),
            'overdue'   => $users->sum('overdue_loans_count'),
        ];

        return view('departments.show', compact('department', 'users', 'stats'));
    }

    public function create()
    {
        return view('departments.create');
    }

    public function store(DepartmentRequest $request)
    {
        Department::create($request->validated());

        return redirect()->route('departments.index')
            ->with('success', 'Department berhasil ditambahkan.');
    }

    public function edit(Department $department)
    {
        return view('departments.edit', compact('department'));
    }

    public function update(DepartmentRequest $request, Department $department)
    {
        $department->update($request->validated());

        return redirect()->route('departments.index')
            ->with('success', 'Department berhasil diperbarui.');
    }

    public function destroy(Department $department)
    {
        if ($department->users()->exists()) {
            return back()->with('error', 'Department masih aktif, tidak bisa dihapus.');
        }

        $department->delete();

        return redirect()->route('departments.index')
            ->with('success', 'Department berhasil dihapus.');
    }
}
