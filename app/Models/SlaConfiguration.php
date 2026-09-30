<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SlaConfiguration extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'unit',
        'value',
        'applies_to',
        'request_type_id',
        'priority_id',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'value' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    public function requestType()
    {
        return $this->belongsTo(RequestType::class, 'request_type_id');
    }

    public function priority()
    {
        return $this->belongsTo(Priority::class, 'priority_id');
    }

    /**
     * Resuelve la configuración SLA aplicable a un caso según su tipo/prioridad,
     * priorizando la más específica y cayendo a la global.
     */
    public static function resolveFor(?int $requestTypeId, ?int $priorityId): ?self
    {
        $query = self::query()->where('is_active', true);

        if ($requestTypeId) {
            $specific = (clone $query)->where('applies_to', 'request_type')
                ->where('request_type_id', $requestTypeId)
                ->first();
            if ($specific) {
                return $specific;
            }
        }

        if ($priorityId) {
            $specific = (clone $query)->where('applies_to', 'priority')
                ->where('priority_id', $priorityId)
                ->first();
            if ($specific) {
                return $specific;
            }
        }

        return (clone $query)->where('applies_to', 'global')->first();
    }
}