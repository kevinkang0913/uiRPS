<?php 

namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class RpsAssessmentCategory extends Model {
    protected $fillable = ['code','name','default_desc','order_no'];
}