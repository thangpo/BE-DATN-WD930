<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Package extends Model
{
    use HasFactory;
    protected $fillable = [
        'specialty_id ',
        'hospital_name',
        'description',
        'image',
        'address',
        'price',
    ];

    // Khai báo quan hệ với bảng specialties
    public function specialty()
    {
        return $this->belongsTo(Specialty::class);
    }
    public function timeSlot()
    {
        return $this->hasMany(AvailableTimeslot::class);
    }
    public function appoinment()
    {
        return $this->hasMany(Appoinment::class);
    }
    public function review()
    {
        return $this->hasMany(Review::class);
    }
}
