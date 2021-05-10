<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class HitsResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request): array
    {
        //return parent::toArray($request);
        return [
            'year'       => $this->year,
            'month'      => $this->month,
            'counts'     => $this->counts
        ];
    }
}
