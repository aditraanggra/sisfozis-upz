<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreLaporanKurbanRequest;
use App\Http\Requests\SubmitLaporanKurbanRequest;
use App\Http\Resources\LaporanKurbanResource;
use App\Models\LaporanKurban;
use App\Models\UnitZis;
use App\Models\User;
use App\Services\LaporanKurbanService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;

class LaporanKurbanController extends Controller
{
    protected LaporanKurbanService $service;

    public function __construct(LaporanKurbanService $service)
    {
        $this->service = $service;
    }

    public function index(Request $request)
    {
        $user = Auth::user();
        $unit = $this->getUserUnit($user);

        if (! $unit) {
            return $this->error('Unit tidak ditemukan', Response::HTTP_NOT_FOUND);
        }

        $query = LaporanKurban::with('unit')
            ->where('unit_id', $unit->id);

        if ($request->has('tahun')) {
            $query->where('tahun_masehi', (int) $request->tahun);
        }

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        $data = $query->latest()->paginate(15);

        return LaporanKurbanResource::collection($data)
            ->additional(['success' => true, 'message' => 'Riwayat laporan kurban'])
            ->response();
    }

    public function store(StoreLaporanKurbanRequest $request)
    {
        try {
            $user = Auth::user();
            $unit = $this->getUserUnit($user);

            if (! $unit) {
                return $this->error('Unit tidak ditemukan', Response::HTTP_NOT_FOUND);
            }

            $laporan = $this->service->createOrUpdate($unit->id, $user->id, $request->validated());

            return (new LaporanKurbanResource($laporan->load(['unit', 'dokumentasi'])))
                ->additional(['success' => true, 'message' => 'Laporan berhasil disimpan'])
                ->response();
        } catch (\RuntimeException $e) {
            return $this->error($e->getMessage(), Response::HTTP_UNPROCESSABLE_ENTITY);
        }
    }

    public function show(string $id)
    {
        $user = Auth::user();
        $unit = $this->getUserUnit($user);

        if (! $unit) {
            return $this->error('Unit tidak ditemukan', Response::HTTP_NOT_FOUND);
        }

        $laporan = LaporanKurban::with(['unit', 'dokumentasi'])->findOrFail($id);

        if ($laporan->unit_id !== $unit->id) {
            abort(Response::HTTP_FORBIDDEN, 'Anda tidak memiliki akses ke laporan ini');
        }

        return (new LaporanKurbanResource($laporan))
            ->additional(['success' => true, 'message' => 'Detail laporan kurban'])
            ->response();
    }

    public function submit(SubmitLaporanKurbanRequest $request, string $id)
    {
        try {
            $user = Auth::user();
            $unit = $this->getUserUnit($user);

            if (! $unit) {
                return $this->error('Unit tidak ditemukan', Response::HTTP_NOT_FOUND);
            }

            $laporan = LaporanKurban::findOrFail($id);

            if ($laporan->unit_id !== $unit->id) {
                abort(Response::HTTP_FORBIDDEN, 'Anda tidak memiliki akses ke laporan ini');
            }

            $laporan = $this->service->submit($laporan);

            return (new LaporanKurbanResource($laporan->load(['unit', 'dokumentasi'])))
                ->additional(['success' => true, 'message' => 'Laporan berhasil dikirim'])
                ->response();
        } catch (\Symfony\Component\HttpKernel\Exception\HttpException $e) {
            throw $e;
        } catch (\RuntimeException $e) {
            return $this->error($e->getMessage(), Response::HTTP_UNPROCESSABLE_ENTITY);
        }
    }

    public function destroy(string $id)
    {
        try {
            $user = Auth::user();
            $unit = $this->getUserUnit($user);

            if (! $unit) {
                return $this->error('Unit tidak ditemukan', Response::HTTP_NOT_FOUND);
            }

            $laporan = LaporanKurban::findOrFail($id);

            if ($laporan->unit_id !== $unit->id) {
                abort(Response::HTTP_FORBIDDEN, 'Anda tidak memiliki akses ke laporan ini');
            }

            $this->service->destroy($laporan);

            return response()->json([
                'success' => true,
                'data' => null,
                'message' => 'Laporan berhasil dihapus',
            ]);
        } catch (\Symfony\Component\HttpKernel\Exception\HttpException $e) {
            throw $e;
        } catch (\RuntimeException $e) {
            return $this->error($e->getMessage(), Response::HTTP_UNPROCESSABLE_ENTITY);
        }
    }

    private function getUserUnit(User $user): ?UnitZis
    {
        return UnitZis::where('user_id', $user->id)->first();
    }

    private function error(string $message, int $code = Response::HTTP_BAD_REQUEST)
    {
        return response()->json([
            'success' => false,
            'message' => $message,
        ], $code);
    }
}
