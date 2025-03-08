<?php

namespace App\Http\Resources;


class SetorZisResource extends BaseTransactionResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray($request)
    {
        return array_merge(parent::getBaseArray(), [
            'zf_amount_deposit' => $this->zf_amount_deposit,
            'zf_rice_deposit' => $this->zf_rice_deposit,
            'zm_amount_deposit' => $this->zm_amount_deposit,
            'ifs_amount_deposit' => $this->ifs_amount_deposit,
            'total_deposit' => $this->total_deposit,
            'status' => $this->status,
            'validation' => $this->validation,
            'upload' => $this->upload,
        ]);
    }
}
