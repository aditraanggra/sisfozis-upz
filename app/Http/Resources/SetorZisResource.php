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
            'zf_rice_sold_amount' => $this->zf_rice_sold_amount,
            'zf_rice_sold_price' => $this->zf_rice_sold_price,
            'zf_rice_sold_proof' => $this->zf_rice_sold_proof,
            'is_rice_sold' => $this->is_rice_sold,
            'unsold_rice' => $this->unsold_rice,
            'original_rice_qty' => $this->original_rice_qty,
            'zm_amount_deposit' => $this->zm_amount_deposit,
            'ifs_amount_deposit' => $this->ifs_amount_deposit,
            'total_deposit' => $this->total_deposit,
            'status' => $this->status,
            'validation' => $this->validation,
            'upload' => $this->upload,
            'deposit_destination' => $this->deposit_destination,
        ]);
    }
}
