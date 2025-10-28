<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RpsSubClo extends Model
{
    // 👉 pastikan pakai tabel rps_sub_clos
    protected $table = 'rps_sub_clos';

    // 👉 kolom FK yang benar adalah outcome_id
    protected $fillable = ['outcome_id', 'description'];

    public function outcome()
    {
        return $this->belongsTo(RpsOutcome::class, 'outcome_id');
    }
}
