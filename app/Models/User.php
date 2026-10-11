<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\StudentStatusEnum;
use Database\Factories\UserFactory;
use Filament\Models\Contracts\FilamentUser;
use Filament\Models\Contracts\HasAvatar;
use Filament\Panel;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Storage;
use Override;

#[Fillable(['name', 'email', 'password', 'username', 'phone', 'is_staff', 'photo_path'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable implements FilamentUser, HasAvatar
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    #[Override]

    // Autorisasi user agar dapat login ke Filament Panel
    public function canAccessPanel(Panel $panel): bool
    {
        if ($panel->getId() === 'admin') {
            return $this->is_staff;
        }

        if ($panel->getId() === 'test') {
            return ! $this->is_staff
                && $this->student()
                    ->where('status', StudentStatusEnum::Active->value)
                    ->exists();
        }

        return false;
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
            'is_staff' => 'boolean',
        ];
    }

    // protected $appends = ['avatar_url'];

    #[Override]
    public function getFilamentAvatarUrl(): ?string
    {
        //cek apakah user punya foto tersimpan
        if (
            $this->photo_path && Storage::disk('public')->exists($this->photo_path)
        ) {
            return Storage::disk('public')->url($this->photo_path); //return url foto
        }
        return null;
    }

    // relasi ke table students
    public function student(): HasOne
    {
        return $this->hasOne(Student::class);
    }
    /**
     * The "booted" method of the model.
     */
   protected static function booted(): void
{
    static::updating(fn ($user) => $user->isDirty('photo_path') && $user->getOriginal('photo_path')
        ? Storage::disk('public')->delete($user->getOriginal('photo_path'))
        : null);
}
}
