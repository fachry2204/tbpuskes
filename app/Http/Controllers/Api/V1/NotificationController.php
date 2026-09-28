<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class NotificationController extends Controller
{
    public function index(Request $request): JsonResponse { $items = DB::table('user_notifications')->where('user_id', $request->user()->id)->latest()->paginate(min((int) $request->input('per_page', 20), 100)); return response()->json(['success' => true, 'data' => $items->items(), 'meta' => ['current_page' => $items->currentPage(), 'total' => $items->total()]]); }
    public function read(Request $request, int $notification): JsonResponse { $changed = DB::table('user_notifications')->where('id', $notification)->where('user_id', $request->user()->id)->where('is_read', false)->update(['is_read' => true, 'read_at' => now(), 'updated_at' => now()]); abort_unless($changed, 404); return response()->json(['success' => true, 'message' => 'Notifikasi ditandai telah dibaca.']); }
}
