<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TanabiAthletePercentage extends Model
{
    use HasFactory;

    protected $fillable = [
        'athlete_name',
        'tanabi_percentage',
        'other_club_percentage',
        'other_club_id',
        'other_club_name',
        'formando_base_id',
    ];

    protected $casts = [
        'tanabi_percentage' => 'decimal:4',
        'other_club_percentage' => 'decimal:4',
    ];

    public function otherClub()
    {
        return $this->belongsTo(SafClube::class, 'other_club_id');
    }

    public function athlete()
    {
        return $this->belongsTo(FormandoBase::class, 'formando_base_id');
    }

    public function otherClubPercentages()
    {
        return $this->hasMany(TanabiAthleteOtherClubPercentage::class,'tanabi_athlete_percentage_id');
    }
}
