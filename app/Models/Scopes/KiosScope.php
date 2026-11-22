<?php

namespace App\Models\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;
use Illuminate\Support\Facades\Auth;

/**
 * Global Scope KiosScope
 *
 * Scope ini memfilter setiap query Eloquent agar hanya mengambil data yang ber-kios_id
 * sesuai dengan kios milik user yang sedang login (Auth::user()->kios_id).
 *
 * Jika tidak ada user login (seeder, console artisan, job background), query tidak difilter.
 *
 * PERHATIAN:
 * JANGAN pasang scope ini pada model User untuk menghindari rekursi tak berujung (infinite loop),
 * karena Auth::user() memuat data user via query ke model User itu sendiri.
 * Untuk data user per kios, filter manual lewat relasi: auth()->user()->kios->users().
 */
class KiosScope implements Scope
{
    /**
     * Terapkan scope ke query builder Eloquent yang diberikan.
     */
    public function apply(Builder $builder, Model $model): void
    {
        if (Auth::check() && Auth::user()->kios_id) {
            $builder->where($model->qualifyColumn('kios_id'), Auth::user()->kios_id);
        }
    }
}
