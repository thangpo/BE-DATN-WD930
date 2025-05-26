<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AppoinmentHistory extends Model
{
    use HasFactory;
    protected $fillable = [
        'user_id',
        'doctor_id',
        'appoinment_id',
        'diagnosis',
        'prescription',
        'follow_up_date',
        'notes',
    ];
    public function user()
    {
        return $this->belongsTo(User::class); // thiết lập mối quan hệ một-nhiều (one-to-many) giữa bảng categories và bảng products.
    }
    public function doctor()
    {
        return $this->belongsTo(Doctor::class); // thiết lập mối quan hệ một-nhiều (one-to-many) giữa bảng categories và bảng products.
    }
    public function appoinment()
    {
        return $this->belongsTo(Appoinment::class); // thiết lập mối quan hệ một-nhiều (one-to-many) giữa bảng categories và bảng products.
    }
}
