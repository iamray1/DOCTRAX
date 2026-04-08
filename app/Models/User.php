<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'name',
        'email',
        'mobile',
        'password',
        'account_type',
        'office_id',
        'representative_office_name',
    ];

    /**
     * Attributes guarded from mass assignment.
     * role, status, activated_at, activation_ip are set explicitly in controllers.
     */
    protected $guarded = [
        'id',
        'role',
        'status',
        'activated_at',
        'activation_ip',
    ];

    /**
     * The attributes that should be hidden for serialization.
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'activated_at' => 'datetime',
            'password' => 'hashed',
            'has_reports_access' => 'boolean',
        ];
    }

    /**
     * Always persist email addresses in normalized form.
     */
    public function setEmailAttribute($value): void
    {
        $this->attributes['email'] = strtolower(trim((string) $value));
    }

    /**
     * Check if the user account is active.
     */
    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    /**
     * Check if the user account is pending activation.
     */
    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    /**
     * Check if the user account is suspended/deactivated.
     */
    public function isSuspended(): bool
    {
        return $this->status === 'suspended';
    }

    /**
     * Check if the user is an administrator.
     */
    public function isAdmin(): bool
    {
        return $this->role === 'admin' || $this->role === 'superadmin';
    }

    /**
     * Check if the user is a super administrator.
     */
    public function isSuperAdmin(): bool
    {
        return $this->role === 'superadmin';
    }

    /**
     * Check if the user is a Records Section representative.
     */
    public function isRecords(): bool
    {
        return $this->isRepresentative()
            && $this->office
            && strtoupper($this->office->code) === 'RECORDS';
    }

    /**
     * Activation tokens for this user.
     */
    public function activationTokens()
    {
        return $this->hasMany(ActivationToken::class);
    }

    /**
     * Documents submitted by this user.
     */
    public function documents()
    {
        return $this->hasMany(Document::class);
    }

    /**
     * The office this representative belongs to.
     */
    public function office()
    {
        return $this->belongsTo(Office::class);
    }

    /**
     * Displayable office or school label for representative accounts.
     */
    public function representativeOfficeName(): ?string
    {
        if (!$this->isRepresentative()) {
            return null;
        }

        if ($this->office?->name) {
            return $this->office->name;
        }

        $officeName = trim((string) ($this->representative_office_name ?? ''));
        if ($officeName !== '') {
            return $officeName;
        }

        if (!$this->office_id && str_contains($this->name, ' - ')) {
            [$officeName] = explode(' - ', $this->name, 2);
            return trim($officeName);
        }

        return null;
    }

    /**
     * Displayable staff/contact name for representative accounts.
     */
    public function representativeDisplayName(): string
    {
        $displayName = trim((string) $this->name);

        if (!$this->isRepresentative()) {
            return $displayName;
        }

        $officeName = $this->representativeOfficeName();
        $prefix = $officeName ? ($officeName . ' - ') : null;

        if ($prefix && str_starts_with($displayName, $prefix)) {
            return trim(substr($displayName, strlen($prefix)));
        }

        if (!$this->office_id && str_contains($displayName, ' - ')) {
            [, $displayName] = explode(' - ', $displayName, 2);
        }

        return trim($displayName);
    }

    /**
     * Routing log actions performed by this user.
     */
    public function routingLogs()
    {
        return $this->hasMany(RoutingLog::class, 'performed_by');
    }

    /**
     * Documents currently tagged/assigned to this user.
     */
    public function handledDocuments()
    {
        return $this->hasMany(Document::class, 'current_handler_id');
    }

    /**
     * Check if the user is a representative (office staff).
     */
    public function isRepresentative(): bool
    {
        return $this->account_type === 'representative';
    }

    /**
     * Check if this user is an internal office account.
     */
    public function isOfficeAccount(): bool
    {
        return $this->isRepresentative()
            && !is_null($this->office_id)
            && !$this->isAdmin();
    }

    /**
     * Check if the user has access to the Reports dashboard.
     * SuperAdmins always have access; everyone else is controlled by the toggle.
     */
    public function hasReportsAccess(): bool
    {
        if ($this->isSuperAdmin()) {
            return true;
        }

        return (bool) $this->has_reports_access;
    }
}
