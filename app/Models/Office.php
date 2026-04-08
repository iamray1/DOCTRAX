<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Office extends Model
{
    use HasFactory;

    protected $fillable = ['code', 'name', 'head', 'description', 'is_active'];

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
