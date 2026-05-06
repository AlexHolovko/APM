<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Client extends Model
{
    protected $fillable = [
    'last_name',
    'first_name',
    'middle_name',
    'birth_date',
    'tax_number',
    'country',
    'region',
    'city',
    'street',
    'house',
    'apartment',
    'passport_series',
    'passport_number',
    'passport_issued_by',
    'passport_issued_at',
    'phone',
    'email',
];
}