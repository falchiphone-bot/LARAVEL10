<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InvestmentDailyBalance extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'snapshot_at',
        'total_amount',
    ];

    protected $casts = [
        'snapshot_at' => 'datetime',
        'total_amount' => 'decimal:6',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Ajuste de soft delete para SQL Server garantindo formato compatível com DATETIME2(7)
     */
    protected function performDeleteOnModel()
    {
        if ($this->forceDeleting) {
            return parent::performDeleteOnModel();
        }
        $connection = $this->getConnection();
        $driver = $connection->getDriverName();
        if ($driver !== 'sqlsrv') {
            return parent::performDeleteOnModel();
        }
        $time = now()->copy()->timezone('UTC');
        $stamp = $time->format('Y-m-d H:i:s.u');
        if (preg_match('/^(.*\.\d{6})$/', $stamp)) { $stamp .= '0'; }
        $this->{$this->getDeletedAtColumn()} = $stamp;
        $this->{$this->getUpdatedAtColumn()} = $stamp;
        $connection->table($this->getTable())
            ->where($this->getKeyName(), $this->getKey())
            ->update([
                $this->getDeletedAtColumn() => $stamp,
                $this->getUpdatedAtColumn() => $stamp,
            ]);
    }

    public function restore()
    {
        $connection = $this->getConnection();
        $driver = $connection->getDriverName();
        if ($driver !== 'sqlsrv') {
            return parent::restore();
        }
        $this->{$this->getDeletedAtColumn()} = null;
        $stamp = now()->copy()->timezone('UTC')->format('Y-m-d H:i:s.u');
        if (preg_match('/^(.*\.\d{6})$/', $stamp)) { $stamp .= '0'; }
        $this->{$this->getUpdatedAtColumn()} = $stamp;
        $connection->table($this->getTable())
            ->where($this->getKeyName(), $this->getKey())
            ->update([
                $this->getDeletedAtColumn() => null,
                $this->getUpdatedAtColumn() => $stamp,
            ]);
        $this->exists = true;
        return $this;
    }
}
