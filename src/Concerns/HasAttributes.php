<?php

namespace Anteris\Model\Concerns;

use Anteris\Model\Support\Str;

/**
 * The "HasAttributes" trait defines how a model manipulates attributes. This
 * includes the getting, setting, casting, and mutating of any attributes that
 * are added to the model.
 *
 * This trait is broken into the following sections:
 *
 * 1. Attribute getting & setting.
 * 2. Attribute mutation.
 * 3. Attribute casting (when implemented).
 * 4. Change tracking.
 * 5. Serialization.
 */
trait HasAttributes
{
    /** @var array The model attributes. */
    protected array $attributes = [];

    /** @var array The model attribute's original state. */
    protected array $original = [];

    /** @var array The changed model attributes. */
    protected array $changes = [];

    /**
     * Gets all attributes set on the model.
     *
     * @return array
     */
    public function getAttributes(): array
    {
        return $this->attributes;
    }

    /**
     * Get an attribute from the model.
     *
     * @param  string  $key
     * @return mixed
     */
    public function getAttribute(string $key)
    {
        if (array_key_exists($key, $this->attributes)) {
            return $this->transformModelValue($key, $this->attributes[$key]);
        }
    }

    /**
     * Get a subset of the model's attributes.
     *
     * @param  array|mixed  $attributes
     * @return array
     */
    public function only($attributes)
    {
        $results = [];

        foreach (is_array($attributes) ? $attributes : func_get_args() as $attribute) {
            $results[$attribute] = $this->getAttribute($attribute);
        }

        return $results;
    }

    /**
     * Set an attribute on the model.
     *
     * @param  string  $key
     * @return mixed
     */
    public function setAttribute(string $key, $value)
    {
        // Sets a value via its mutator if one exists.
        if ($this->hasSetMutator($key)) {
            return $this->setMuatatedAttributeValue($key, $value);
        }

        $this->attributes[$key] = $value;

        return $this;
    }

    /**
     * Set the array of model attributes without any checking for mutations or
     * casts.
     *
     * @param  array  $attributes
     * @param  bool  $sync
     * @return self
     */
    public function setRawAttributes(array $attributes, bool $sync = false): self
    {
        $this->attributes = $attributes;

        if ($sync) {
            $this->syncOriginal();
        }

        return $this;
    }

    /**
     * Determine if a get mutator exists for the attribute.
     *
     * @param  string  $key
     * @return bool
     */
    public function hasGetMutator(string $key): bool
    {
        return method_exists($this, 'get' . Str::studly($key) . 'Attribute');
    }

    /**
     * Checks for an executes any transformations that must be applied to the attribute
     * key. These include mutations and casts.
     *
     * @param  string  $key
     * @param  mixed  $value
     * @return mixed
     */
    protected function transformModelValue(string $key, $value)
    {
        if ($this->hasGetMutator($key)) {
            return $this->{'get' . Str::studly($key) . 'Attribute'}($value);
        }

        return $value;
    }

    /**
     * Determine if a set mutator exists for the attribute.
     *
     * @param  string  $key
     * @return bool
     */
    public function hasSetMutator(string $key): bool
    {
        return method_exists($this, 'set' . Str::studly($key) . 'Attribute');
    }

    /**
     * Set an attribute via its mutator.
     *
     * @param  string  $key
     * @param  mixed  $value
     * @return mixed
     */
    protected function setMuatatedAttributeValue(string $key, $value)
    {
        return $this->{'set' . Str::studly($key) . 'Attribute'}($value);
    }

    /**
     * Sync the original attributes with the current attributes.
     *
     * @return self
     */
    public function syncOriginal(): self
    {
        $this->original = $this->getAttributes();

        return $this;
    }

    /**
     * Sync a single original attribute with its current value.
     *
     * @param  string  $attribute
     * @return $this
     */
    public function syncOriginalAttribute($attribute)
    {
        return $this->syncOriginalAttributes($attribute);
    }

    /**
     * Sync multiple original attribute with their current values.
     *
     * @param  array|string  $attributes
     * @return $this
     */
    public function syncOriginalAttributes($attributes)
    {
        $attributes = is_array($attributes) ? $attributes : func_get_args();

        $modelAttributes = $this->getAttributes();

        foreach ($attributes as $attribute) {
            $this->original[$attribute] = $modelAttributes[$attribute];
        }

        return $this;
    }

    /**
     * Sync the changed attributes with any dirty attributes.
     *
     * @return self
     */
    public function syncChanges(): self
    {
        $this->changes = $this->getDirty();

        return $this;
    }

    /**
     * Get the changed attributes on the model.
     *
     * @return array
     */
    public function getChanges(): array
    {
        return $this->changes;
    }

    /**
     * Get the model's original attribute values (before changes were made).
     *
     * @param  string|null  $key
     * @param  mixed  $default
     * @return mixed
     */
    public function getOriginal(?string $key = null, $default = null)
    {
        return (new static)->setRawAttributes(
            $this->original,
            $sync = true
        )->getOriginalWithoutRewindingModel($key, $default);
    }

    /**
     * Get the model's original attribute values while respecting mutations and casts.
     *
     * @param  string|null  $key
     * @param  mixed  $default
     * @return mixed
     */
    protected function getOriginalWithoutRewindingModel(?string $key = null, $default = null)
    {
        if ($key) {
            return $this->transformModelValue(
                $key,
                $this->original[$key] ?? $default
            );
        }

        // We should probably clean this up...
        // Basically it just does what Arr::mapWithKeys() does in Laravel.
        // We are going through the original attributes and transforming the value.
        $result = [];

        foreach ($this->original as $key => $value) {
            $assoc = fn ($value, $key) => [ $key => $this->transformModelValue($key, $value) ];

            foreach ($assoc as $mapKey => $mapValue) {
                $result[$mapKey] = $mapValue;
            }
        }

        return $result;
    }

    /**
     * Get the model's raw original attribute values.
     *
     * @param  string|null  $key
     * @param  string|null  $default
     * @return mixed
     */
    public function getRawOriginal($key = null, $default = null)
    {
        return $this->original[$key] ?? $default;
    }

    /**
     * Determine if the new and old values for a given key are equivalent.
     *
     * @param  string  $key
     * @return bool
     */
    public function originalIsEquivalent(string $key): bool
    {
        if (! array_key_exists($key, $this->original)) {
            return false;
        }

        $attribute = $this->attributes[$key] ?? null;
        $original  = $this->original[$key] ?? null;

        if ($attribute === $original) {
            return true;
        }

        return is_numeric($attribute)
            && is_numeric($original)
            && strcmp((string) $attribute, (string) $original) === 0;
    }

    /**
     * Gets any attributes that have been changed sync the last sync.
     *
     * @return array
     */
    public function getDirty(): array
    {
        $dirty = [];

        foreach ($this->getAttributes() as $key => $value) {
            if (! $this->originalIsEquivalent($key)) {
                $dirty[$key] = $value;
            }
        }

        return $dirty;
    }

    /**
     * Determine if the model or any of the given attribute(s) have remained the same.
     *
     * @param  array|string|null  $attributes
     * @return bool
     */
    public function isClean($attributes = null): bool
    {
        return ! $this->isDirty(...func_get_args());
    }

    /**
     * Determine if the model or any of the given attribute(s) have been modified.
     *
     * @param  array|string|null  $attributes
     * @return bool
     */
    public function isDirty($attributes = null)
    {
        return $this->hasChanges(
            $this->getDirty(),
            is_array($attributes) ? $attributes : func_get_args()
        );
    }

    /**
     * Determine if the model or any of the given attribute(s) have been modified.
     *
     * @param  array|string|null  $attributes
     * @return bool
     */
    public function wasChanged($attributes = null): bool
    {
        return $this->hasChanges(
            $this->getChanges(),
            is_array($attributes) ? $attributes : func_get_args()
        );
    }

    /**
     * Determines if any of the given attributes were changed.
     *
     * @param  array  $changes
     * @param  array|string|null  $attributes
     * @return bool
     */
    public function hasChanges(array $changes, $attributes = null): bool
    {
        // If there are no attributes specified, just check if there were any
        // changes at all.
        if (empty($attributes) || is_null($attributes)) {
            return count($changes) > 0;
        }

        $attributes = is_array($attributes) ? $attributes : [$attributes];

        // Iterate over the attributes and see if any of them were changed.
        foreach ($attributes as $attribute) {
            if (array_key_exists($attribute, $changes)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Convert the model's attributes to an array.
     *
     * @return array
     */
    public function attributesToArray(): array
    {
        return $this->getArrayableAttributes();
    }

    /**
     * Get an array of attributes that can be serialized.
     *
     * @return array
     */
    protected function getArrayableAttributes(): array
    {
        return $this->getArrayableItems($this->getAttributes());
    }

    /**
     * Taking into consideration our visible and hidden serialization preferences,
     * builds an array of visible values.
     *
     * @param  array  $values
     * @return array
     */
    public function getArrayableItems(array $values): array
    {
        if (count($this->getVisible()) > 0) {
            $values = array_intersect_key($values, array_flip($this->getVisible()));
        }

        if (count($this->getHidden()) > 0) {
            $values = array_diff_key($values, array_flip($this->getHidden()));
        }

        return $values;
    }
}
