<?php

namespace App\Http\Controllers;

use App\Models\Patient;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\PatientsExport;
use PDF;  // DomPDF facade




class PatientController extends Controller
{
    // Listimi i pacientëve (me pagination, 10 për faqe)
    public function index(Request $request)
    {
        $query = Patient::query();

        // Kërko sipas emrit ose mbiemrit
        if ($request->has('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('first_name', 'like', "%$search%")
                    ->orWhere('last_name', 'like', "%$search%");
            });
        }

        // Filtrim sipas datës së vizitës
        if ($request->has('visit_date')) {
            $query->whereDate('visit_date', $request->input('visit_date'));
        }

        // Filtrim sipas simptomës
        if ($request->has('symptom')) {
            $query->where('symptom', 'like', '%' . $request->input('symptom') . '%');
        }

        // Filtrim sipas periudhës: sot, java, muaji
        if ($request->has('period')) {
            $period = $request->input('period');
            if ($period === 'today') {
                $query->whereDate('visit_date', today());
            } elseif ($period === 'week') {
                $query->whereBetween('visit_date', [now()->startOfWeek(), now()->endOfWeek()]);
            } elseif ($period === 'month') {
                $query->whereMonth('visit_date', now()->month)
                    ->whereYear('visit_date', now()->year);
            }
        }

        // Pagination
        $patients = $query->orderBy('visit_date', 'desc')->paginate(10);

        return response()->json($patients);
    }

    // Krijo pacient të ri
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'date_of_birth' => 'required|date',
            'symptom' => 'nullable|string|max:255',
            'visit_date' => 'nullable|date',
            'estimated_recovery_days' => 'nullable|integer|min:0',
            'urgent' => 'nullable|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $patient = Patient::create($request->all());

        return response()->json($patient, 201);
    }

    // Merr një pacient specifik
    public function show($id)
    {
        $patient = Patient::find($id);

        if (!$patient) {
            return response()->json(['message' => 'Patient not found'], 404);
        }

        return response()->json($patient);
    }

    // Përditëso pacientin
    public function update(Request $request, $id)
    {
        $patient = Patient::find($id);

        if (!$patient) {
            return response()->json(['message' => 'Patient not found'], 404);
        }

        $validator = Validator::make($request->all(), [
            'first_name' => 'sometimes|required|string|max:255',
            'last_name' => 'sometimes|required|string|max:255',
            'date_of_birth' => 'sometimes|required|date',
            'symptom' => 'nullable|string|max:255',
            'visit_date' => 'nullable|date',
            'estimated_recovery_days' => 'nullable|integer|min:0',
            'urgent' => 'nullable|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $patient->update($request->all());

        return response()->json($patient);
    }

    // Fshi pacientin
    public function destroy($id)
    {
        $patient = Patient::find($id);

        if (!$patient) {
            return response()->json(['message' => 'Patient not found'], 404);
        }

        $patient->delete();

        return response()->json(['message' => 'Patient deleted successfully']);
    }

    // Statistikat për simptomat e muajit aktual
    public function statsSymptoms()
    {
        $symptoms = Patient::whereMonth('visit_date', now()->month)
            ->whereYear('visit_date', now()->year)
            ->select('symptom', \DB::raw('count(*) as total'))
            ->groupBy('symptom')
            ->orderByDesc('total')
            ->get();

        return response()->json($symptoms);
    }

    // Statistikat për vizitat në javë
    public function statsVisits()
    {
        $visits = Patient::whereBetween('visit_date', [now()->startOfWeek(), now()->endOfWeek()])
            ->select(\DB::raw('DAYNAME(visit_date) as day'), \DB::raw('count(*) as total'))
            ->groupBy('day')
            ->orderByRaw("FIELD(day, 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday')")
            ->get();

        return response()->json($visits);
    }

    // Mesatarja e ditëve të rikuperimit
    public function statsRecovery()
    {
        $average = Patient::whereNotNull('estimated_recovery_days')
            ->avg('estimated_recovery_days');

        return response()->json([
            'average_recovery_days' => round($average, 2)
        ]);
    }
    public function exportExcel()
    {
        return Excel::download(new PatientsExport, 'patients.xlsx');
    }
    public function exportPDF()
    {
        $patients = Patient::all();

        $pdf = PDF::loadView('patients_pdf', compact('patients'));

        return $pdf->download('patients.pdf');
    }
}
