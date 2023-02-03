<?php

namespace App\Models;

use App\Models\File;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Tool extends Model
{
    use SoftDeletes;

    protected $guarded = ['id', 'created_at', 'updated_at'];
    protected $casts = [
        'has_validation' => 'boolean',
        'dispatchable' => 'boolean'
    ];

    public function set(): BelongsTo {
        return $this->belongsTo(Set::class);
    }

    public function des(): BelongsTo {
        return $this->belongsTo(Des::class);
    }

    public function brand(): BelongsTo {
        return $this->belongsTo(Brand::class);
    }

    public function calibration(): BelongsTo {
        return $this->belongsTo(Calibration::class);
    }

    public function location(): BelongsTo {
        return $this->belongsTo(Location::class);
    }

    public function files(): MorphMany {
        return $this->morphMany(File::class, 'fileable');
    }

    public function user(): BelongsTo {
        return $this->belongsTo(User::class);
    }

    public function logs(): HasMany {
        return $this->hasMany(Log::class);
    }
}
