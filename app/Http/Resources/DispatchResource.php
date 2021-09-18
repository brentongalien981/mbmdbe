<?php

namespace App\Http\Resources;

use App\Models\DispatchStatus;
use Illuminate\Http\Resources\Json\JsonResource;

class DispatchResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'epBatchId' => $this->ep_batch_id,

            'statusCode' => $this->status_code,
            'statusName' => DispatchStatus::where('code', $this->status_code)->get()[0]->name,

            'notes' => $this->notes,
            'createdAt' => $this->created_at,
            'updatedAt' => $this->updated_at,
        ];
    }
}
