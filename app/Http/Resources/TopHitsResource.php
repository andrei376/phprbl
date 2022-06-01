<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\URL;

class TopHitsResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request): array
    {
        $ipv6 = false;
        if (stripos($this->list, '6')) {
            $ipv6 = true;
        }

        $iplong = $ipv6 ? inet_ntop($this->iplong) : long2ip($this->iplong);

        $format_cidr = $iplong . '/' . $this->mask;

        $format_cidr = '<a href="'.
            URL::route($ipv6 ? 'rbl.show6' : 'rbl.show4', ['id' => $this->list_id, 'list' => $this->show_list]).
            '">'.
            $format_cidr.'</a>';

        return [
            'index' => $this->index,
            'hit_date' => $this->hit_date,
            'list' => $this->list,
            'list_id' => $this->list_id,
            'count' => $this->count,
            'iplong' => $iplong,
            'mask' => $this->mask,
            'format_cidr' => $format_cidr,
        ];
    }
}
