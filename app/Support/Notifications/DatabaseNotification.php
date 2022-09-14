<?php

namespace App\Support\Notifications;

use App\Support\Models\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Notifications\DatabaseNotificationCollection;

class DatabaseNotification extends Model
{
    protected $keyType = 'string';

    public $incrementing = false;

    protected $table = 'notifications';

    protected $guarded = [];

    protected $casts = [
        'data' => 'array',
        'read_at' => 'datetime',
    ];

    public function notifiable(): MorphTo
    {
        return $this->morphTo();
    }

    public function markAsRead()
    {
        if (is_null($this->attributes['read_at'])) {
            $this->forceFill(['read_at' => $this->freshTimestamp()])->save();
        }
    }

    public function markAsUnread()
    {
        if (!is_null($this->attributes['read_at'])) {
            $this->forceFill(['read_at' => null])->save();
        }
    }

    public function read(): bool
    {
        return !$this->unread();
    }

    public function unread(): bool
    {
        return is_null($this->attributes['read_at'] ?? null);
    }

    public function scopeRead(Builder $query): Builder
    {
        return $query->whereNotNull('read_at');
    }

    public function scopeUnread(Builder $query): Builder
    {
        return $query->whereNull('read_at');
    }

    public function newCollection(array $models = []): DatabaseNotificationCollection
    {
        return new DatabaseNotificationCollection($models);
    }
}
