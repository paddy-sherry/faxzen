<?php

namespace App\Http\Controllers;

use App\Models\FaxJob;
use Illuminate\Http\Request;

class AdminController extends Controller
{
    public function faxJobs(Request $request)
    {
        $query = FaxJob::query()->orderBy('created_at', 'desc');
        
        // Optional status filter
        if ($request->has('status') && $request->status !== '') {
            $query->where('status', $request->status);
        }
        
        // Optional date filter
        if ($request->has('date_from') && $request->date_from !== '') {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        
        if ($request->has('date_to') && $request->date_to !== '') {
            $query->whereDate('created_at', '<=', $request->date_to);
        }
        
        $faxJobs = $query->paginate(50);
        
        return view('admin.fax-jobs', compact('faxJobs'));
    }
} 