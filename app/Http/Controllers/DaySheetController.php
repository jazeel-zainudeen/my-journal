<?php

namespace App\Http\Controllers;

use App\Models\DaySheet;
use Illuminate\Http\Request;

class DaySheetController extends Controller
{
    function index()
    {
        $daysheets = DaySheet::orderBy('created_at', 'desc')->get();
        return view('pages.day-sheet', compact('daysheets'));
    }

    function save(Request $request)
    {
        if ($request->id) {
            $day_sheet = DaySheet::findOrFail($request->id);
            $day_sheet->updated_at = now();
        } else {
            $day_sheet = new DaySheet();
            $day_sheet->updated_at = NULL;
            $day_sheet->created_at = now();
        }
        $day_sheet->title = $request->title;
        $day_sheet->data = $request->data;

        $day_sheet->save();

        return redirect()->back()->with('message', 'Day sheet ' . ($request->id ? 'edited' : 'added') . ' successfully.');
    }

    function details($id)
    {
        $daysheet = DaySheet::find($id);
        return response()->json($daysheet);
    }

    function delete($id)
    {
        $daysheet = DaySheet::findOrFail($id);
        $daysheet->delete();

        return redirect()->back()->with('message', 'Day Sheet deleted successfully.');
    }
}
