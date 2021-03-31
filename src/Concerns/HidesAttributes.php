<?php

namespace Anteris\Model\Concerns;

use Closure;

trait HidesAttributes
{
    /** @var array The attributes that should be hidden in serialization. */
    protected array $hidden = [];

    /** @var array The attributes that should be visible in serialization. */
    protected array $visible = [];

    /**
     * Get the hidden attributes for the model.
     *
     * @return array
     */
    public function getHidden(): array
    {
        return $this->hidden;
    }

    /**
     * Set the hidden attributes for the model.
     *
     * @param  array  $hidden
     * @return self
     */
    public function setHidden(array $hidden): self
    {
        $this->hidden = $hidden;

        return $this;
    }

    /**
     * Get the visible attributes for the model.
     *
     * @return array
     */
    public function getVisible(): array
    {
        return $this->visible;
    }

    /**
     * Set the visible attributes for the model.
     *
     * @param  array  $visible
     * @return self
     */
    public function setVisible(array $visible): self
    {
        $this->visible = $visible;

        return $this;
    }

    /**
     * Make the given, typically hidden, attributes visible.
     *
     * @param  array|string|null  $attributes
     * @return self
     */
    public function makeVisible($attributes): self
    {
        $attributes = is_array($attributes) ? $attributes : func_get_args();

        $this->hidden = array_diff($this->hidden, $attributes);

        if (! empty($this->visible)) {
            $this->visible = array_merge($this->visible, $attributes);
        }

        return $this;
    }

    /**
     * Make the given, typically hidden, attributes visible if the given condition is true.
     *
     * @param  bool|Closure  $condition
     * @param  array|string|null  $attributes
     * @return self
     */
    public function makeVisibleIf($condition, $attributes): self
    {
        $condition = $condition instanceof Closure ? $condition($this) : $condition;

        return $condition ? $this->makeVisible($attributes) : $this;
    }

    /**
     * Make the given, typically visible, attributes hidden.
     *
     * @param  array|string|null  $attributes
     * @return self
     */
    public function makeHidden($attributes): self
    {
        $this->hidden = array_merge(
            $this->hidden,
            is_array($attributes) ? $attributes : func_get_args()
        );

        return $this;
    }

    /**
     * Make the given, typically visible, attributes hidden if the given condition is true.
     *
     * @param  bool|Closure  $condition
     * @param  array|string|null  $attributes
     * @return self
     */
    public function makeHiddenIf($condition, $attributes): self
    {
        $condition = $condition instanceof Closure ? $condition($this) : $condition;

        return $condition ? $this->makeHidden($attributes) : $this;
    }
}
