<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

#[Fillable(['name', 'email', 'password', 'role', 'department_id', 'phone'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * Relasi ke Department.
     */
    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    /**
     * Relasi ke Loans.
     */
    public function loans(): HasMany
    {
        return $this->hasMany(Loan::class);
    }

    public function activeLoans()
    {
        return $this->hasMany(Loan::class)->whereNull('returned_at');
    }

    public function getWhatsappNumberAttribute(): ?string
    {
        $digits = preg_replace('/\D+/', '', (string) $this->phone);

        if ($digits === '') {
            return null;
        }

        return match (true) {
            str_starts_with($digits, '0')  => '62' . substr($digits, 1),
            str_starts_with($digits, '62') => $digits,
            str_starts_with($digits, '8')  => '62' . $digits,
            default                        => $digits,
        };
    }

    public function whatsappUrl(?string $message = null): ?string
    {
        if (! $this->whatsapp_number) {
            return null;
        }

        $url = 'https://wa.me/' . $this->whatsapp_number;

        return $message ? $url . '?text=' . rawurlencode($message) : $url;
    }

    public function emailUrl(string $message, string $subject = 'Pengingat Pengembalian Aset'): string
    {
        return 'mailto:' . $this->email
            . '?subject=' . rawurlencode($subject)
            . '&body=' . rawurlencode($message);
    }

    // Pesan pengingat berisi semua aset yang terlambat; null kalau tidak ada yang terlambat
    public function reminderMessage(string $sender): ?string
    {
        $overdue = $this->activeLoans
            ->filter(fn (Loan $loan) => $loan->status === 'terlambat')
            ->sortBy('due_date');

        if ($overdue->isEmpty()) {
            return null;
        }

        $list = $overdue
            ->map(fn (Loan $loan) => '- ' . $loan->asset->name . ' (' . $loan->asset->asset_code
                . '), jatuh tempo ' . $loan->due_date->format('d/m/Y'))
            ->implode("\n");

        return "Halo {$this->name},\n\n"
            . "Kami mengingatkan bahwa aset berikut sudah melewati batas pengembalian:\n{$list}\n\n"
            . "Mohon segera dikembalikan. Terima kasih.\n\nSalam,\n{$sender}";
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }
}
