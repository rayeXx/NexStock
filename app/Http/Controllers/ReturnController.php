<?php

namespace App\Http\Controllers;

use App\Models\PoReceivingHistory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ReturnController extends Controller
{
    // List returns for Owner & Admin
    public function index(Request $request)
    {
        $query = PoReceivingHistory::with(['purchaseOrder', 'product', 'receiver'])
            ->whereIn('status_retur', ['Menunggu Retur', 'Sudah Diretur']);

        if ($request->filled('status')) {
            $status = $request->status;
            if ($status === 'pending') {
                $query->where('status_retur', 'Menunggu Retur');
            } elseif ($status === 'completed') {
                $query->where('status_retur', 'Sudah Diretur');
            }
        }

        if ($request->filled('period')) {
            switch ($request->period) {
                case 'today':
                    $query->whereDate('received_at', Carbon::today());
                    break;
                case 'week':
                    $query->whereBetween('received_at', [
                        Carbon::now()->startOfWeek(), 
                        Carbon::now()->endOfWeek()
                    ]);
                    break;
                case 'month':
                    $query->whereMonth('received_at', Carbon::now()->month)
                          ->whereYear('received_at', Carbon::now()->year);
                    break;
            }
        }

        if ($request->filled('date')) {
            $dateInput = $request->date;
            if (str_contains($dateInput, ':')) {
                // Hourly filter for today (e.g. "08:00")
                $hour = substr($dateInput, 0, 2);
                $query->whereDate('received_at', Carbon::today())
                      ->whereRaw(DB::getDriverName() === 'mysql' 
                          ? 'DATE_FORMAT(received_at, "%H") = ?' 
                          : 'STRFTIME(\'%H\', received_at) = ?', 
                          [$hour]);
            } else {
                // Day filter (e.g. "Mon, 06 Jul" or "06 Jul")
                try {
                    $date = Carbon::parse($dateInput)->startOfDay();
                    $query->whereDate('received_at', $date);
                } catch (\Exception $e) {
                    // Ignore parsing error
                }
            }
        }

        $returns = $query->orderBy('received_at', 'desc')->get();

        return view('returns.index', compact('returns'));
    }
}
