<?php

namespace App\Http\Controllers;

use App\Models\Reference;
use App\Models\Supplier;
use App\Models\Ticket;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Storage;

class TicketController extends Controller
{
    public function index()
    {
        $references = Reference::all();
        $suppliers = Supplier::all();
        return view('pages.tickets', compact('references', 'suppliers'));
    }

    public function add_ticket(Request $request)
    {
        $supplier = Supplier::where('id', $request->filter_suppliers)
            ->orWhere('name', $request->filter_suppliers)
            ->first();

        if ($supplier) {
            $supplierId = $supplier->id;
        } else {
            $newSupplier = Supplier::create(['name' => $request->filter_suppliers]);
            $supplierId = $newSupplier->id;
        }

        $reference = Reference::where('id', $request->filter_references)
            ->orWhere('name', $request->filter_references)
            ->first();

        if ($reference) {
            $referenceId = $reference->id;
        } else {
            $newReference = Reference::create(['name' => $request->filter_references]);
            $referenceId = $newReference->id;
        }

        if ($request->date) {
            $formattedDate = Carbon::createFromFormat('F d, Y', $request->date);
        }



        $ticket = new Ticket();
        $ticket->customer_name = $request->customer_name;
        if ($request->return_date) {
            $ticket->return_date = Carbon::createFromFormat('F d, Y', $request->return_date);
        }
        if ($request->departure_date) {
            $ticket->departure_date = Carbon::createFromFormat('F d, Y', $request->departure_date);
        }
        $ticket->reference_id = $referenceId;
        $ticket->supplier_id = $supplierId;
        $ticket->ticket_number = $request->ticket_no;
        $ticket->cost = str_replace('SAR ', '', str_replace(',', '', $request->cost));
        $ticket->profit = str_replace('SAR ', '', str_replace(',', '', $request->profit));
        $ticket->total = str_replace('SAR ', '', str_replace(',', '', $request->total));
        if ($request->collection_amount) {
            $ticket->collection_amount = str_replace('SAR ', '', str_replace(',', '', $request->collection_amount));
        }
        $ticket->created_at = $formattedDate;
        $ticket->updated_at = NULL;
        $ticket->save();

        $supplier = Supplier::find($supplierId);
        $supplier->increment('total_payable', str_replace('SAR ', '', str_replace(',', '', $request->cost)));

        return redirect()->back();
    }

    public function list_ticket(Request $request)
    {
        if ($request->ajax()) {
            $data = Ticket::with('supplier', 'reference');

            if ($request->referred_by) {
                $data->whereIn('reference_id', $request->referred_by);
            }

            if ($request->suppliers) {
                $data->whereIn('supplier_id', $request->suppliers);
            }

            if ($request->created_at) {
                if ($request->created_at == '1') {
                    $data->whereDate('created_at', Carbon::today());
                } else if ($request->created_at == '2') {
                    $data->whereBetween('created_at', [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()]);
                } else if ($request->created_at == '3') {
                    $data->whereBetween('created_at', [Carbon::now()->startOfMonth(), Carbon::now()->endOfMonth()]);
                } else if ($request->created_at == '4') {
                    if ($request->created_custom_date) {
                        $created_custom_date = explode(' to ', $request->created_custom_date);
                        if (count($created_custom_date) == 2) {
                            $startDate = Carbon::createFromFormat('F j, Y', $created_custom_date[0])->startOfDay();
                            $endDate = Carbon::createFromFormat('F j, Y', $created_custom_date[1])->endOfDay();

                            $data->whereBetween('created_at', [$startDate, $endDate]);
                        }
                    }
                }
            }

            if ($request->departure_at) {
                if ($request->departure_at == '1') {
                    $data->whereDate('departure_date', Carbon::today());
                } else if ($request->departure_at == '2') {
                    $data->whereBetween('departure_date', [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()]);
                } else if ($request->departure_at == '3') {
                    $data->whereBetween('departure_date', [Carbon::now()->startOfMonth(), Carbon::now()->endOfMonth()]);
                } else if ($request->departure_at == '4') {
                    if ($request->departure_custom_date) {
                        $departure_custom_date = explode(' to ', $request->departure_custom_date);
                        if (count($departure_custom_date) == 2) {
                            $startDate = Carbon::createFromFormat('F j, Y', $departure_custom_date[0])->startOfDay();
                            $endDate = Carbon::createFromFormat('F j, Y', $departure_custom_date[1])->endOfDay();

                            $data->whereBetween('departure_date', [$startDate, $endDate]);
                        }
                    }
                }
            }

            $data->orderBy('created_at', 'desc');

            $data = $data->get();

            return DataTables::of($data)
                ->addIndexColumn()
                ->editColumn('created_at', function ($row) {
                    if ($row->created_at) {
                        return Carbon::parse($row->created_at)->format('d F, Y');
                    } else {
                        return '';
                    }
                })
                ->editColumn('return_date', function ($row) {
                    if ($row->return_date) {
                        return Carbon::parse($row->return_date)->format('d F, Y');
                    } else {
                        return '';
                    }
                })
                ->editColumn('departure_date', function ($row) {
                    if ($row->departure_date) {
                        return Carbon::parse($row->departure_date)->format('d F, Y');
                    } else {
                        return '';
                    }
                })
                ->editColumn('refunded_at', function ($row) {
                    if ($row->refunded_at) {
                        return Carbon::parse($row->refunded_at)->format('d F, Y h:i A');
                    } else {
                        return '';
                    }
                })
                ->editColumn('cost', function ($row) {
                    return 'SAR ' . number_format($row->cost, 2);
                })
                ->editColumn('profit', function ($row) {
                    return 'SAR ' . number_format($row->profit, 2);
                })
                ->editColumn('total', function ($row) {
                    if ($row->refunded_at != '') {
                        return 'SAR ' . number_format($row->total - $row->extra_charges, 2);
                    }
                    return 'SAR ' . number_format($row->total, 2);
                })
                ->editColumn('collection_amount', function ($row) {
                    if ($row->refunded_at != '') {
                        return 'SAR ' . number_format(- ($row->collection_amount + $row->extra_charges), 2);
                    }
                    return 'SAR ' . number_format($row->collection_amount, 2);
                })
                ->addColumn('cost_amount', function ($row) {
                    return $row->cost;
                })
                ->addColumn('profit_amount', function ($row) {
                    return $row->profit;
                })
                ->addColumn('total_amount', function ($row) {
                    if ($row->refunded_at != '') {
                        return $row->total - $row->extra_charges;
                    }
                    return $row->total;
                })
                ->addColumn('collect_amount', function ($row) {
                    if ($row->refunded_at != '') {
                        return - ($row->collection_amount + $row->extra_charges);
                    }
                    return $row->collection_amount;
                })
                ->addColumn('action', function ($row) {
                    $data = '';

                    $balance_amt = $row->refunded_at != '' ? $row->collection_amount + $row->extra_charges : $row->total - $row->collection_amount;

                    $data = '<div class="d-flex align-items-baseline justify-content-center">';
                    if ($balance_amt != 0) {
                        $data .= '<button type="button" title="Mark as Collected" class="btn btn-none text-success p-1 mark-collected-action" data-ticket-id="' . $row->id . '" data-balance="' . $balance_amt . '"><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-check-square"><polyline points="9 11 12 14 22 4"></polyline><path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"></path></svg></button>';
                    }
                    $data .= '<a href="' . route('ticket.delete', $row->id) . '" title="Delete" class="text-danger p-1 confirm-link"><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-trash-2"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path><line x1="10" y1="11" x2="10" y2="17"></line><line x1="14" y1="11" x2="14" y2="17"></line></svg></a>';
                    $data .= '<button title="Edit" data-ticket-id="' . $row->id . '" class="btn btn-none text-primary p-1 edit-ticket-link"><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-edit"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path></svg></button>';
                    if ($row->refunded_at == '') {
                        $data .= '<button type="button" data-ticket-id="' . $row->id . '" title="Mark as Refunded" class="btn btn-none text-warning p-1 refund-link"><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-refresh-ccw"><polyline points="1 4 1 10 7 10"></polyline><polyline points="23 20 23 14 17 14"></polyline><path d="M20.49 9A9 9 0 0 0 5.64 5.64L1 10m22 4l-4.64 4.36A9 9 0 0 1 3.51 15"></path></svg></button>';
                    }
                    $data .= '</div>';
                    return $data;
                })
                ->addColumn('balance_amount', function ($row) {
                    if ($row->refunded_at != '') {
                        return 'SAR ' . number_format($row->collection_amount + $row->extra_charges, 2);
                    }
                    if ($row->collection_amount != $row->total) {
                        return '<div class="text-danger">SAR ' . number_format($row->total - $row->collection_amount, 2) . '</div>';
                    } else {
                        return '<div class="text-success">SAR ' . number_format($row->total - $row->collection_amount, 2) . '</div>';
                    }
                })
                ->addColumn('balance_amt', function ($row) {
                    if ($row->refunded_at != '') {
                        return $row->collection_amount + $row->extra_charges;
                    } else {
                        return $row->total - $row->collection_amount;
                    }
                })
                ->editColumn('extra_charges', function ($row) {
                    if ($row->refunded_at != '') {
                        return 'SAR ' . number_format($row->extra_charges, 2);
                    } else {
                        return '';
                    }
                })
                ->editColumn('extra_charge_amt', function ($row) {
                    if ($row->refunded_at != '') {
                        return $row->extra_charges;
                    } else {
                        return 0;
                    }
                })
                ->rawColumns(['action', 'balance_amount'])
                ->make(true);
        }
        return redirect()->route('/');
    }

    public function mark_refunded(Request $request, $id)
    {
        $ticket = Ticket::findOrFail($id);
        $ticket->collection_amount = - ($ticket->collection_amount - $request->amount);
        $ticket->extra_charges = $request->amount;
        $ticket->refunded_at = now();
        $ticket->save();

        // $ticket->decrement('collection_amount', $ticket->total);

        $supplier = Supplier::find($ticket->supplier_id);
        $supplier->decrement('total_payable', $ticket->cost);

        Session::flash('message', 'Ticket refunded successfully.');

        return response()->json([]);
    }

    public function delete($id)
    {
        $ticket = Ticket::find($id);
        $ticket->delete();

        return redirect()->back()->with('message', 'Ticket deleted successfully.');
    }

    public function get_ticket($id)
    {
        $ticket = Ticket::find($id);
        $data = $ticket->toArray();
        if ($data['created_at']) {
            $data['created_at'] = Carbon::parse($ticket->created_at)->format('F d, Y h:i A');
        }
        if ($data['departure_date']) {
            $data['departure_date'] = Carbon::parse($ticket->departure_date)->format('F d, Y h:i A');
        }
        if ($data['return_date']) {
            $data['return_date'] = Carbon::parse($ticket->return_date)->format('F d, Y h:i A');
        }
        return response()->json($data);
    }

    public function edit(Request $request)
    {
        $ticket = Ticket::findOrFail($request->ticket_id);

        $supplier = Supplier::where('id', $request->filter_suppliers)
            ->orWhere('name', $request->filter_suppliers)
            ->first();

        if ($supplier) {
            $supplier->decrement('total_payable', $ticket->cost);
            $supplierId = $supplier->id;
        } else {
            $newSupplier = Supplier::create(['name' => $request->filter_suppliers]);
            $supplierId = $newSupplier->id;
        }

        $reference = Reference::where('id', $request->filter_references)
            ->orWhere('name', $request->filter_references)
            ->first();

        if ($reference) {
            $referenceId = $reference->id;
        } else {
            $newReference = Reference::create(['name' => $request->filter_references]);
            $referenceId = $newReference->id;
        }

        if ($request->date) {
            $formattedDate = Carbon::createFromFormat('F d, Y', $request->date);
        }

        $ticket->customer_name = $request->customer_name;
        if ($request->return_date) {
            $ticket->return_date = Carbon::createFromFormat('F d, Y', $request->return_date);
        }
        if ($request->departure_date) {
            $ticket->departure_date = Carbon::createFromFormat('F d, Y', $request->departure_date);
        }
        $ticket->reference_id = $referenceId;
        $ticket->supplier_id = $supplierId;
        $ticket->cost = str_replace('SAR ', '', str_replace(',', '', $request->cost));
        $ticket->profit = str_replace('SAR ', '', str_replace(',', '', $request->profit));
        $ticket->total = str_replace('SAR ', '', str_replace(',', '', $request->total));
        if ($request->collection_amount) {
            $ticket->collection_amount = str_replace('SAR ', '', str_replace(',', '', $request->collection_amount));
        }
        $ticket->ticket_number = $request->ticket_no;
        $ticket->created_at = $formattedDate;
        $ticket->updated_at = now();
        $ticket->save();

        $supplier = Supplier::find($supplierId);
        $supplier->increment('total_payable', str_replace('SAR ', '', str_replace(',', '', $request->cost)));

        return redirect()->back()->with('message', 'Ticket edited successfully.');
    }

    public function collect_balance(Request $request, $id)
    {
        $ticket = Ticket::find($id);
        if ($ticket->refunded_at == '') {
            $collection_amount = $ticket->collection_amount + $request->amount;
        } else {
            $collection_amount = $ticket->collection_amount - $request->amount;
        }
        $ticket->collection_amount = $collection_amount;
        $ticket->save();

        Session::flash('message', 'Ticket amount collected successfully.');
        return response()->json([], 200);
    }

    public function get_collection_balance($id)
    {
        $totalPendingAmount = Ticket::where('reference_id', $id)->sum('total') - Ticket::where('reference_id', $id)->sum('collection_amount');

        if ($totalPendingAmount == 0) {
            return response()->json(['message' => '']);
        }
        return response()->json(['message' => 'Pending Collection Balance is SAR ' . number_format($totalPendingAmount, 2)]);
    }

    public function generate_report(Request $request)
    {
        $data = Ticket::with('supplier', 'reference');

        if ($request->referred_by) {
            $data->whereIn('reference_id', $request->referred_by);
        }

        if ($request->suppliers) {
            $data->whereIn('supplier_id', $request->suppliers);
        }

        if ($request->created_at) {
            if ($request->created_at == '1') {
                $data->whereDate('created_at', Carbon::today());
            } else if ($request->created_at == '2') {
                $data->whereBetween('created_at', [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()]);
            } else if ($request->created_at == '3') {
                $data->whereBetween('created_at', [Carbon::now()->startOfMonth(), Carbon::now()->endOfMonth()]);
            } else if ($request->created_at == '4') {
                if ($request->created_custom_date) {
                    $created_custom_date = explode(' to ', $request->created_custom_date);
                    if (count($created_custom_date) == 2) {
                        $startDate = Carbon::createFromFormat('F j, Y', $created_custom_date[0])->startOfDay();
                        $endDate = Carbon::createFromFormat('F j, Y', $created_custom_date[1])->endOfDay();

                        $data->whereBetween('created_at', [$startDate, $endDate]);
                    }
                }
            }
        }

        if ($request->departure_at) {
            if ($request->departure_at == '1') {
                $data->whereDate('departure_date', Carbon::today());
            } else if ($request->departure_at == '2') {
                $data->whereBetween('departure_date', [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()]);
            } else if ($request->departure_at == '3') {
                $data->whereBetween('departure_date', [Carbon::now()->startOfMonth(), Carbon::now()->endOfMonth()]);
            } else if ($request->departure_at == '4') {
                if ($request->departure_custom_date) {
                    $departure_custom_date = explode(' to ', $request->departure_custom_date);
                    if (count($departure_custom_date) == 2) {
                        $startDate = Carbon::createFromFormat('F j, Y', $departure_custom_date[0])->startOfDay();
                        $endDate = Carbon::createFromFormat('F j, Y', $departure_custom_date[1])->endOfDay();

                        $data->whereBetween('departure_date', [$startDate, $endDate]);
                    }
                }
            }
        }

        $data->orderBy('created_at', 'desc');
        $data = $data->get();

        $return_data = [
            'data' => $data,
            'totals' => [
                'cost' => $data->sum('cost'),
                'profit' => $data->sum('profit'),
                'total' => $data->sum('total'),
                'collection_amount' => $data->sum('collection_amount'),
            ]
        ];

        $pdf = Pdf::loadView('layout.pdf.export', $return_data)->setOptions(['title' => 'Ticket Report'])->setPaper('a4', 'landscape');
        $filename = 'ticket_report_' . time() . '.pdf';

        $year = date('Y');
        $month = date('m');
        $day = date('d');
        $folderPath = "pdf_reports/$year/$month/$day/";
        Storage::disk('public')->put($folderPath . $filename, $pdf->output());

        return response()->json([
            'success' => true,
            'url' => Storage::disk('public')->url($folderPath . $filename)
        ]);
    }

    public function list_ticket_by_reference($reference_id)
    {
        $tickets = Ticket::whereRaw('collection_amount != total')->where('reference_id', $reference_id)->get();

        return response()->json($tickets);
    }

    public function bulk_collect_balance(Request $request, $reference_id)
    {
        foreach ($request->collections as $ticket_id => $amount) {
            $ticket = Ticket::findOrFail($ticket_id);
            if ($ticket->refunded_at == '') {
                $collection_amount = $ticket->collection_amount + $amount;
            } else {
                $collection_amount = -$ticket->collection_amount + $amount;
            }
            $ticket->collection_amount = $collection_amount;
            $ticket->save();
        }

        Session::flash('message', 'Ticket amount collected successfully.');
        return response()->json($request, 200);
    }
}
