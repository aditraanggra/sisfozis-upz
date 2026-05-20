<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\DokumentasiKurbanResource;
use App\Models\DokumentasiKurban;
use App\Models\LaporanKurban;
use App\Models\UnitZis;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class DokumentasiKurbanController extends Controller
{
    public function store(Request $request, string $laporanKurbanId)
    {
        $validator = Validator::make($request->all(), [
            'file' => 'required|file|max:5120|mimes:jpg,png,pdf',
            'jenis' => 'required|in:foto_kegiatan,berita_acara,lainnya',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Data tidak valid',
                'errors' => $validator->errors()->toArray(),
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $user = Auth::user();
        $unit = UnitZis::where('user_id', $user->id)->first();

        if (! $unit) {
            return response()->json([
                'success' => false,
                'message' => 'Unit tidak ditemukan',
            ], Response::HTTP_NOT_FOUND);
        }

        $laporan = LaporanKurban::findOrFail($laporanKurbanId);

        if ($laporan->unit_id !== $unit->id) {
            abort(Response::HTTP_FORBIDDEN, 'Anda tidak memiliki akses ke laporan ini');
        }
        $file = $request->file('file');
        $path = sprintf('dokumentasi/%d/%d', $laporan->unit_id, $laporan->id);

        $storedPath = Storage::disk('cloudinary')->putFile($path, $file);

        $dokumentasi = DokumentasiKurban::create([
            'laporan_kurban_id' => $laporan->id,
            'jenis' => $request->jenis,
            'file_path' => $storedPath,
            'file_size' => $file->getSize(),
            'mime_type' => $file->getMimeType(),
            'uploaded_at' => now(),
        ]);

        return (new DokumentasiKurbanResource($dokumentasi))
            ->additional(['success' => true, 'message' => 'Dokumentasi berhasil diunggah'])
            ->response()
            ->setStatusCode(Response::HTTP_CREATED);
    }

    public function destroy(string $laporanKurbanId, string $dokId)
    {
        $user = Auth::user();
        $unit = UnitZis::where('user_id', $user->id)->first();

        if (! $unit) {
            return response()->json([
                'success' => false,
                'message' => 'Unit tidak ditemukan',
            ], Response::HTTP_NOT_FOUND);
        }

        $laporan = LaporanKurban::findOrFail($laporanKurbanId);

        if ($laporan->unit_id !== $unit->id) {
            abort(Response::HTTP_FORBIDDEN, 'Anda tidak memiliki akses ke laporan ini');
        }

        $dokumentasi = DokumentasiKurban::where('laporan_kurban_id', $laporanKurbanId)
            ->findOrFail($dokId);

        if (Storage::disk('cloudinary')->exists($dokumentasi->file_path)) {
            Storage::disk('cloudinary')->delete($dokumentasi->file_path);
        }

        $dokumentasi->delete();

        return response()->json([
            'success' => true,
            'data' => null,
            'message' => 'Dokumentasi berhasil dihapus',
        ]);
    }
}
