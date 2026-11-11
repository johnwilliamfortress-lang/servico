<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Model;

#[Hidden(['management_token'])]
class Announcement extends Model
{
    public const CATEGORIES = [
        'Alimentação',
        'Artesanato',
        'Beleza e bem-estar',
        'Casa e serviços',
        'Educação',
        'Moda',
        'Saúde',
        'Transporte',
        'Outros',
    ];

    protected $fillable = [
        'title',
        'category',
        'description',
        'offer_info',
        'contact',
        'image_path',
        'management_token',
    ];

    public function getContactUrlAttribute(): ?string
    {
        $contact = trim($this->contact);

        if (filter_var($contact, FILTER_VALIDATE_EMAIL)) {
            return 'mailto:'.$contact;
        }

        if (filter_var($contact, FILTER_VALIDATE_URL)) {
            $scheme = parse_url($contact, PHP_URL_SCHEME);

            return in_array(strtolower((string) $scheme), ['http', 'https'], true) ? $contact : null;
        }

        $phone = preg_replace('/\D+/', '', $contact);

        if (strlen($phone) === 10 || strlen($phone) === 11) {
            $phone = '55'.$phone;
        }

        return strlen($phone) >= 8 ? 'https://wa.me/'.$phone : null;
    }
}
