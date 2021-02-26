<?php

namespace App\License\DTO;

class Quota
{
    private int $current;

    private int $limit;

    public function __construct(int $current = 0, int $limit = 0)
    {
        $this->current = $current;
        $this->limit = $limit;
    }

    public function getCurrent(): int
    {
        return $this->current;
    }

    public function setCurrent(int $current): self
    {
        $this->current = $current;

        return $this;
    }

    public function increment(): self
    {
        ++$this->current;

        return $this;
    }

    public function getLimit(): int
    {
        return $this->limit;
    }

    public function setLimit(int $limit): self
    {
        $this->limit = $limit;

        return $this;
    }

    public function getCoef(): float
    {
        if (0 === $this->limit) {
            return 1.0;
        }

        return $this->current / $this->limit;
    }

    public function getPercentage(): float
    {
        return 100 * $this->getCoef();
    }

    public function isOverflow(): bool
    {
        return $this->current > $this->limit;
    }
}
