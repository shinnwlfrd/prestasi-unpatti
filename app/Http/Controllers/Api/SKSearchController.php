<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\SKDocument;
use Illuminate\Http\Request;

class SKSearchController extends Controller
{
    public function search(Request $request)
    {
        $query = $request->query('q', '');

        // Minimum 2 characters
        if (strlen($query) < 2) {
            return response()->json([]);
        }

        try {
            $skDocuments = SKDocument::query()
                ->where(function ($q) use ($query) {
                    $q->where('sk_number', 'ILIKE', "%{$query}%")
                        ->orWhere('title', 'ILIKE', "%{$query}%");
                })
                ->select('id', 'sk_number', 'title', 'issued_date')
                ->orderBy('issued_date', 'desc')
                ->limit(50)
                ->get();

            return response()->json($skDocuments);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error searching SK: ' . $e->getMessage(),
                'data' => []
            ], 500);
        }
    }
}
