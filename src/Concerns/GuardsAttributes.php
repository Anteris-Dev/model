<?php

namespace Anteris\Model\Concerns;

trait GuardsAttributes
{
    /** @var string[] The attributes that are mass-assignable. */
    protected array $fillable = [];

    /** @var string[] The attributes that are not mass-assignable. */
    protected array $guarded = [];

    /** @var bool Indicates whether or not the mass-assignment guard is enabled. */
    protected static bool $unguarded = false;

    /**
     * Get the fillable attributes for the model.
     *
     * @return array
     */
    public function getFillable(): array
    {
        return $this->fillable;
    }

    /**
     * Set the fillable attributes for the model.
     *
     * @param  array  $fillable
     * @return self
     */
    public function fillable(array $fillable): self
    {
        $this->fillable = $fillable;

        return $this;
    }

    /**
     * Merge new fillable attributes with the existing fillable attributes on the model.
     *
     * @param  array  $fillable
     * @return self
     */
    public function mergeFillable(array $fillable): self
    {
        $this->fillable = array_merge($this->fillable, $fillable);

        return $this;
    }

    /**
     * Get the guarded attributes for the model.
     *
     * @return array
     */
    public function getGuarded(): array
    {
        return $this->guarded;
    }

    /**
     * Set the guarded attributes for the model.
     *
     * @param  array  $guard
     * @return self
     */
    public function guard(array $guard): self
    {
        $this->guarded = $guard;

        return $this;
    }

    /**
     * Merge new guarded attributes with the existing guarded attributes on the model.
     *
     * @param  array  $guarded
     * @return self
     */
    public function mergeGuarded(array $guarded): self
    {
        $this->guarded = array_merge($this->guarded, $guarded);

        return $this;
    }

    /**
     * Disable all mass-assignment restrictions.
     *
     * @param  bool  $state
     * @return void
     */
    public static function unguard(bool $state = true): void
    {
        static::$unguarded = $state;
    }

    /**
     * Enable all mass-assignment restrictions.
     *
     * @return void
     */
    public static function reguard()
    {
        static::$unguarded = false;
    }

    /**
     * Determines if mass-assignment is "unguarded."
     *
     * @return bool
     */
    public static function isUnguarded(): bool
    {
        return static::$unguarded;
    }

    /**
     * Run the given callable while being unguarded.
     *
     * @param  callable  $callback
     * @return mixed
     */
    public static function unguarded(callable $callback)
    {
        if (static::$unguarded) {
            return $callback();
        }

        static::unguard();

        try {
            return $callback();
        } finally {
            static::reguard();
        }
    }

    /**
     * Determines whether or not the attribute is fillable.
     *
     * @param  string  $key
     * @return bool
     */
    public function isFillable(string $key): bool
    {
        if (static::$unguarded) {
            return true;
        }

        if (in_array($key, $this->getFillable())) {
            return true;
        }

        if ($this->isGuarded($key)) {
            return false;
        }

        return empty($this->getFillable()) &&
            strpos($key, '.') === false &&
            strpos($key, '_') !== 0;
    }

    /**
     * Determine if the given key is guarded.
     *
     * @param  string  $key
     * @return bool
     */
    public function isGuarded(string $key): bool
    {
        if (empty($this->getGuarded())) {
            return false;
        }

        return $this->getGuarded() == ['*'] ||
            ! empty(preg_grep('/^' . preg_quote($key) . '$/i', $this->getGuarded()));
    }

    /**
     * Determine if the model is totally guarded. This just means that all attributes
     * are guarded.
     *
     * @return bool
     */
    public function totallyGuarded(): bool
    {
        return count($this->getFillable()) === 0 && $this->getGuarded() == ['*'];
    }

    /**
     * Get fillable attributes from an array.
     *
     * @param  array  $attributes
     * @return array
     */
    protected function fillableFromArray(array $attributes)
    {
        if (count($this->getFillable()) > 0 && ! static::$unguarded) {
            return array_intersect_key($attributes, array_flip($this->getFillable()));
        }

        return $attributes;
    }
}
