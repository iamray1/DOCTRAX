<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Office extends Model
{
    use HasFactory;

    protected $fillable = ['code', 'name', 'head', 'description', 'is_active', 'is_school'];

    protected $casts = [
        'is_active' => 'boolean',
        'is_school' => 'boolean',
    ];

    public function scopeSchools($query)
    {
        return $query->where('is_school', true);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function users()
    {
        return $this->hasMany(User::class);
    }

    public function documentsHeld()
    {
        return $this->hasMany(Document::class, 'current_office_id');
    }

    public function documentsSubmittedTo()
    {
        return $this->hasMany(Document::class, 'submitted_to_office_id');
    }
}
