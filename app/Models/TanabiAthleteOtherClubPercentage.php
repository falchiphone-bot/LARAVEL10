<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TanabiAthleteOtherClubPercentage extends Model
{
    protected $fillable = [
        'tanabi_athlete_percentage_id',
        'other_club_id',
        'other_club_name',
        'percentage',
    ];

    protected $casts = [
        'percentage' => 'decimal:4',
    ];

    public function main()
    {
        return $this->belongsTo(TanabiAthletePercentage::class,'tanabi_athlete_percentage_id');
    }

    public function otherClub()
    {
        return $this->belongsTo(SafClube::class,'other_club_id');
    }
}
