<?php

namespace Anteris\Model;

use Anteris\Model\Concerns\GuardsAttributes;
use Anteris\Model\Concerns\HasAttributes;
use Anteris\Model\Concerns\HidesAttributes;
use Anteris\Model\Exceptions\MassAssignmentException;
use ArrayAccess;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Contracts\Support\Jsonable;
use JsonSerializable;

/**
 * This model supports much of the same functionality as found in Laravel, but
 * is completely detached from the framework. The Illuminate contracts are
 * included so that we can support "Arrayable" and "Jsonable" interfaces.
 *
 * @todo Implement events.
 * @todo Implement casting.
 *
 * @author Aidan Casey <aidan.casey@anteris.com>
 * @see https://github.com/laravel/framework/blob/8.x/src/Illuminate/Database/Eloquent/Model.php
 */
abstract class Model implements Arrayable, ArrayAccess, Jsonable, JsonSerializable
{
    use HasAttributes;
    use HidesAttributes;
    use GuardsAttributes;

    /**
     * Create a new model instance.
     *
     * @param  array  $attributes
     * @return void
     */
    public function __construct(array $attributes = [])
    {
        $this->fill($attributes);
    }

    /**
     * Create a new instance of the current model.
     *
     * @param  array  $attributes
     * @return static
     */
    public function newInstance(array $attributes = []): Model
    {
        return new static($attributes);
    }

    /**
     * Fill the model with an array of attributes while respecting the guarded state.
     *
     * @param  array  $attributes
     * @return self
     */
    public function fill(array $attributes): self
    {
        $isTotallyGuarded = $this->totallyGuarded();

        foreach ($this->fillableFromArray($attributes) as $key => $value) {
            // The developers may choose to place some attributes in the "fillable" array
            // which means only those attributes may be set through mass assignment to
            // the model, and all others will just get ignored for security reasons.
            if ($this->isFillable($key)) {
                $this->setAttribute($key, $value);
            } elseif ($isTotallyGuarded) {
                throw new MassAssignmentException(sprintf(
                    'Add [%s] to the fillable property to allow mass assignment on [%s].',
                    $key,
                    get_class($this)
                ));
            }
        }

        return $this;
    }

    /**
     * Fill the model with an array of attributes while bypassing the guarded state.
     *
     * @param  array  $attributes
     * @return self
     */
    public function forceFill(array $attributes)
    {
        return static::unguarded(fn () => $this->fill($attributes));
    }

    /**
     * Dynamically retrieve attributes from the model.
     *
     * @param  string  $key
     * @return mixed
     */
    public function __get($key)
    {
        return $this->getAttribute($key);
    }

    /**
     * Dynamically set attributes on the model.
     *
     * @param  string  $key
     * @param  mixed  $value
     * @return void
     */
    public function __set($key, $value): void
    {
        $this->setAttribute($key, $value);
    }

    /**
     * Dynamically determines if the referenced attribute exists.
     *
     * @param  string  $key
     * @return bool
     */
    public function __isset(string $key): bool
    {
        return ! is_null($this->getAttribute($key));
    }

    /**
     * Dynamically unsets the specified attribute.
     *
     * @param  string  $key
     * @return void
     */
    public function __unset(string $key): void
    {
        unset($this->attributes[$key]);
    }

    /**
     * Get the value of the given offset.
     *
     * @param  mixed  $offset
     * @return mixed
     */
    public function offsetGet($offset)
    {
        return $this->__get($offset);
    }

    /**
     * Set the value of the given offset.
     *
     * @param  mixed  $offset
     * @param  mixed  $value
     * @return void
     */
    public function offsetSet($offset, $value): void
    {
        $this->__set($offset, $value);
    }

    /**
     * Determines if the given offset exists.
     *
     * @param  mixed  $offset
     * @return mixed
     */
    public function offsetExists($offset)
    {
        return $this->__isset($offset);
    }

    /**
     * Unsets the given offset.
     *
     * @return void
     */
    public function offsetUnset($offset): void
    {
        $this->__unset($offset);
    }

    /**
     * Gets the model's values in array form.
     *
     * @return array
     */
    public function toArray(): array
    {
        return $this->attributesToArray();
    }

    /**
     * Gets the model's values in JSON form.
     *
     * @return string
     */
    public function toJson($options = 0): string
    {
        return json_encode($this->jsonSerialize(), $options);
    }

    /**
     * Gets the model's values in string form.
     *
     * @return string
     */
    public function __toString(): string
    {
        return $this->toJson();
    }

    /**
     * Converts the object into something serializable into json.
     *
     * @return array
     */
    public function jsonSerialize(): array
    {
        return $this->toArray();
    }
}
