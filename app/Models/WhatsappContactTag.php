<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WhatsappContactTag extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    public function tag()
    {
        return $this->belongsTo(WhatsappTag::class, 'whatsapp_tag_id');
    }
}
