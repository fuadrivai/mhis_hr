<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WhatsappTag extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    public function chatters()
    {
        return $this->belongsToMany(WhatsappChatter::class, 'chatter_whatsapp_tag');
    }
}
