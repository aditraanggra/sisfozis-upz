<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UnitZisResource extends JsonResource
{
    //define properti
    public $status;
    public $message;
    public $resource;

    /**
     * __construct
     *
     * @param  mixed $status
     * @param  mixed $message
     * @param  mixed $resource
     * @return void
     */
    public function __construct($status, $message, $resource)
    {
        parent::__construct($resource);
        $this->status  = $status;
        $this->message = $message;
    }

    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        // Jika resource adalah collection (untuk index)
        if ($this->resource instanceof \Illuminate\Database\Eloquent\Collection) {
            return [
                'success'   => $this->status,
                'message'   => $this->message,
                'data'      => $this->resource->map(function ($item) {
                    return $this->formatUnitZisData($item);
                })
            ];
        }

        // Jika resource adalah single model (untuk show, store)
        return [
            'success'   => $this->status,
            'message'   => $this->message,
            'data'      => $this->formatUnitZisData($this->resource)
        ];
    }

    /**
     * Format Unit ZIS data to include village and district names
     * 
     * @param  mixed $unitZis
     * @return array
     */
    protected function formatUnitZisData($unitZis)
    {
        $data = $unitZis->toArray();

        // Tambahkan village_name jika relasi village tersedia
        if ($unitZis->village) {
            $data['village_name'] = $unitZis->village->name;
        }

        // Tambahkan district_name jika relasi district tersedia
        if ($unitZis->district) {
            $data['district_name'] = $unitZis->district->name;
        }

        return $data;
    }
}
