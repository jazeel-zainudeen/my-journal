<?php

namespace App\Http\Controllers;

use App\Models\Settlement;
use App\Models\Supplier;
use App\Models\Ticket;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;

class SettlementController extends Controller
{
    public function index()
    {
        return view('pages.settlements');
    }

    function list_settlements(Request $request)
    {
        if ($request->ajax()) {
            $data = Supplier::with('settlements');
            $data->orderBy('total_payable', 'desc');
            $data = $data->get();

            foreach ($data as $supplier) {
                $settledBalance = $supplier->settlements()->sum('amount');
                $totalBalance = Ticket::where('supplier_id', $supplier->id)->sum('cost');
                $pendingBalance = $totalBalance - $settledBalance;

                $supplier->settled_balance = $settledBalance;
                $supplier->total_balance = $totalBalance;
                $supplier->pending_balance = $pendingBalance;

                $supplier->settled_balance_amt = $settledBalance;
                $supplier->total_balance_amt = $totalBalance;
                $supplier->pending_balance_amt = $pendingBalance;
            }

            return DataTables::of($data)
                ->addIndexColumn()
                ->editColumn('pending_balance', function ($row) {
                    return 'SAR ' . number_format($row->pending_balance, 2);
                })
                ->editColumn('settled_balance', function ($row) {
                    return 'SAR ' . number_format($row->settled_balance, 2);
                })
                ->editColumn('total_balance', function ($row) {
                    return 'SAR ' . number_format($row->total_balance, 2);
                })
                ->addColumn('action', function ($row) {
                    $actions = '';
                    if ($row->pending_balance_amt != 0) {
                        $actions .= '<button data-id="' . $row->id . '" data-sbal="' . $row->pending_balance . '" class="text-success settle-btn btn btn-none p-1" title="Settle Balance"><svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-check-circle link-icon"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path><polyline points="22 4 12 14.01 9 11.01"></polyline></svg></button>';
                    }
                    $actions .= '<button data-id="' . $row->id . '" class="text-primary view-history-btn btn btn-none p-1" title="View History"><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-eye"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg></button>';
                    return $actions;
                })
                ->rawColumns(['action'])
                ->make(true);
        }
        return redirect()->route('settlements');
    }

    function settle_balance(Request $request, $id)
    {
        $supplier = Supplier::findOrFail($id);

        $settlement = new Settlement();
        $settlement->supplier_id = $supplier->id;
        $settlement->amount = $request->settlement_amount;
        $settlement->save();

        $supplier->decrement('total_payable', $request->settlement_amount);

        return response()->json($supplier, 200);
    }

    function list_transactions(Request $request, $id)
    {

        if ($request->ajax()) {
            $data = Settlement::where('supplier_id', $id)
                ->orderBy('created_at', 'desc')->get();
            return DataTables::of($data)
                ->addIndexColumn()
                ->editColumn('amount', function ($row) {
                    return 'SAR ' . number_format($row->amount, 2);
                })
                ->editColumn('created_at', function ($row) {
                    if ($row->created_at) {
                        return Carbon::parse($row->created_at)->format('d F, Y h:i A');
                    } else {
                        return '';
                    }
                })
                ->make(true);
        }
        return redirect()->route('settlements');
    }
}
