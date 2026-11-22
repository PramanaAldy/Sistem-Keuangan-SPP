<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AcademicYear;
use App\Models\SchoolClass;
use App\Models\Student;
use App\Models\SppSetting;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Pagination\LengthAwarePaginator;

class SiblingCheckController extends Controller
{
    public function index(Request $request)
    {
        $activeYear = AcademicYear::active();
        $academicYears = AcademicYear::orderByDesc('start_year')->get();
        $selectedYearId = $request->academic_year_id ?? ($activeYear ? $activeYear->id : null);
        
        $classes = SchoolClass::where('is_active', true)->orderBy('sort_order')->get();

        if (!$selectedYearId) {
            $monthSequence = [];
            $monthNames = [1=>'Jan',2=>'Feb',3=>'Mar',4=>'Apr',5=>'Mei',6=>'Jun',
                7=>'Jul',8=>'Ags',9=>'Sep',10=>'Okt',11=>'Nov',12=>'Des'];
            return view('admin.sibling-check.index', compact(
                'academicYears', 'activeYear', 'selectedYearId', 'classes', 'monthSequence', 'monthNames'
            ))
                ->with('paginatedGroups', new LengthAwarePaginator([], 0, 10))
                ->with('summary', []);
        }

        $selectedYear = $academicYears->firstWhere('id', $selectedYearId);
        $monthSequence = $selectedYear ? $selectedYear->getMonthSequence() : [];
        $validSequence = [];
        $now = Carbon::now();
        foreach ($monthSequence as $s) {
            $seqDate = Carbon::create($s['year'], $s['month'], 1);
            if ($seqDate->lte($now->copy()->startOfMonth())) {
                $validSequence[] = $s;
            }
        }

        // Base Query for students matching filters
        $baseQuery = Student::where('status', 'active')
            ->where('academic_year_id', $selectedYearId);

        if ($request->search) {
            $search = $request->search;
            $baseQuery->where(function($q) use ($search) {
                $q->where('full_name', 'like', "%{$search}%")
                  ->orWhere('nisn', 'like', "%{$search}%")
                  ->orWhere('parent_name', 'like', "%{$search}%");
            });
        }
        
        if ($request->class_id) {
            $baseQuery->where('class_id', $request->class_id);
        }

        $matchingStudentIds = $baseQuery->pluck('id')->toArray();

        // Get sibling IDs of matching students
        $siblingIds = DB::table('student_siblings')
            ->whereIn('student_id', $matchingStudentIds)
            ->pluck('sibling_id')
            ->toArray();

        $allTargetIds = array_unique(array_merge($matchingStudentIds, $siblingIds));

        // Fetch all targets and their relations
        $students = Student::with([
            'schoolClass',
            'academicYear',
            'siblings' => fn($q) => $q->where('status', 'active'),
            'transactions' => fn($q) => $q->where('academic_year_id', $selectedYearId)->with('details'),
            'discounts' => fn($q) => $q->wherePivot('academic_year_id', $selectedYearId)
        ])
        ->whereIn('id', $allTargetIds)
        ->where('status', 'active')
        ->get();

        // Load SPP settings
        $sppSettings = SppSetting::where('academic_year_id', $selectedYearId)->get()->keyBy('class_id');

        $groups = [];
        $visited = [];
        $totalArrears = 0;
        $totalPaid = 0;
        $totalStudents = 0;

        foreach ($students as $student) {
            if (isset($visited[$student->id])) {
                continue;
            }

            $group = collect();
            $queue = [$student];

            while (!empty($queue)) {
                $curr = array_shift($queue);
                if (isset($visited[$curr->id])) {
                    continue;
                }

                $visited[$curr->id] = true;

                // Process calculations for $curr
                $curr = $this->calculateStudentFinancials($curr, $sppSettings, $selectedYearId, $validSequence, $monthSequence);
                
                $totalArrears += $curr->total_arrear_amount;
                $totalPaid += $curr->total_paid_amount;
                $totalStudents++;

                $group->push($curr);

                foreach ($curr->siblings as $sibling) {
                    if (!isset($visited[$sibling->id])) {
                        $siblingModel = $students->firstWhere('id', $sibling->id);
                        if ($siblingModel) {
                            $queue[] = $siblingModel;
                        } else {
                            // Fetch sibling if not in loaded collection
                            $fetched = Student::with([
                                'schoolClass',
                                'academicYear',
                                'siblings' => fn($q) => $q->where('status', 'active'),
                                'transactions' => fn($q) => $q->where('academic_year_id', $selectedYearId)->with('details'),
                                'discounts' => fn($q) => $q->wherePivot('academic_year_id', $selectedYearId)
                            ])->find($sibling->id);

                            if ($fetched && $fetched->status === 'active') {
                                $queue[] = $fetched;
                            }
                        }
                    }
                }
            }

            $group = $group->sortBy('full_name');
            $groups[] = $group;
        }

        // Sort groups by the name of the first sibling
        usort($groups, function($a, $b) {
            return strcmp($a->first()->full_name, $b->first()->full_name);
        });

        // Pagination
        $page = $request->input('page', 1);
        $perPage = 10;
        $offset = ($page * $perPage) - $perPage;
        
        $paginatedGroups = new LengthAwarePaginator(
            array_slice($groups, $offset, $perPage, true),
            count($groups),
            $perPage,
            $page,
            ['path' => $request->url(), 'query' => $request->query()]
        );

        $summary = [
            'total_students' => $totalStudents,
            'total_groups' => count($groups),
            'total_paid' => $totalPaid,
            'total_arrears' => $totalArrears
        ];

        $monthNames = [1=>'Jan',2=>'Feb',3=>'Mar',4=>'Apr',5=>'Mei',6=>'Jun',
            7=>'Jul',8=>'Ags',9=>'Sep',10=>'Okt',11=>'Nov',12=>'Des'];

        return view('admin.sibling-check.index', compact(
            'paginatedGroups', 'summary', 'academicYears', 'activeYear', 
            'selectedYearId', 'classes', 'monthSequence', 'monthNames'
        ));
    }

    private function calculateStudentFinancials(Student $student, $sppSettings, $yearId, $validSequence, $fullSequence)
    {
        $setting = $sppSettings->get($student->class_id);
        $baseSpp = $setting ? $setting->amount : 0;

        if ($student->is_free_spp) {
            $baseSpp = 0;
        }

        $discountAmount = 0;
        if (!$student->is_free_spp) {
            foreach ($student->discounts as $discount) {
                $discountAmount += $discount->calculateDiscount($baseSpp);
            }
            $discountAmount = min($discountAmount, $baseSpp);
        }
        $netSpp = max(0, $baseSpp - $discountAmount);

        $paidKeys = [];
        $totalPaid = 0;
        foreach ($student->transactions as $tx) {
            $txPaid = $tx->paid_amount ?? $tx->grand_total;
            $totalPaid += $txPaid;
            foreach ($tx->details as $detail) {
                $paidKeys[] = $detail->month . '-' . $detail->year;
            }
        }

        $student->setAttribute('total_paid_amount', $totalPaid);
        
        $arrearsCount = 0;
        $monthStatuses = [];

        foreach ($fullSequence as $s) {
            $key = $s['month'] . '-' . $s['year'];
            $isPaid = in_array($key, $paidKeys);
            
            $isValid = false;
            foreach ($validSequence as $vs) {
                if ($vs['month'] == $s['month'] && $vs['year'] == $s['year']) {
                    $isValid = true; break;
                }
            }

            if ($isPaid) {
                $monthStatuses[$key] = 'paid';
            } elseif ($isValid) {
                $monthStatuses[$key] = 'arrear';
                $arrearsCount++;
            } else {
                $monthStatuses[$key] = 'future';
            }
        }

        $student->setAttribute('month_statuses', $monthStatuses);
        $student->setAttribute('total_arrear_amount', $arrearsCount * $netSpp);
        
        return $student;
    }
}
