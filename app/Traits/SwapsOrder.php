<?php

namespace App\Traits;

use Illuminate\Database\Eloquent\Model;

trait SwapsOrder
{
    /**
     * Swap order jika ada item lain dengan order yang sama dalam scope parent.
     *
     * @param Model $model           Item yang sedang diupdate
     * @param int   $newOrder        Order baru yang diinginkan
     * @param int   $oldOrder        Order lama
     * @param array $scopeConditions Kondisi scope parent, e.g. ['feature_id' => 5]
     */
    protected function swapOrder($model, int $newOrder, int $oldOrder, array $scopeConditions): void
    {
        if ($newOrder === $oldOrder) {
            return;
        }

        $query = $model::query();

        // Apply each scope condition, handling NULL values properly
        foreach ($scopeConditions as $column => $value) {
            if ($value === null) {
                $query->whereNull($column);
            } else {
                $query->where($column, $value);
            }
        }

        $conflicting = $query
            ->where('id', '!=', $model->id)
            ->where('order', $newOrder)
            ->first();

        if ($conflicting) {
            $conflicting->update(['order' => $oldOrder]);
        }
    }

    /**
     * Insert a new item at a specific order, shifting existing items down only if needed.
     *
     * If the target order is available (gap), fill it and compact items after it.
     * If the target order already exists, shift items at or after that position up by 1.
     *
     * @param string $modelClass      Fully qualified model class name
     * @param int    $insertOrder     The desired order for the new item
     * @param array  $scopeConditions Scope conditions, e.g. ['feature_page_id' => 5]
     * @param array  $extraAttributes Extra attributes for the new item
     * @return Model                   The newly created model instance
     */
    protected function insertAndShiftOrder(string $modelClass, int $insertOrder, array $scopeConditions, array $extraAttributes = []): Model
    {
        // Check if target order already exists
        $query = $modelClass::query();
        foreach ($scopeConditions as $column => $value) {
            if ($value === null) {
                $query->whereNull($column);
            } else {
                $query->where($column, $value);
            }
        }

        $targetExists = $query->where('order', $insertOrder)->exists();

        // Only shift if target order is already taken
        if ($targetExists) {
            $shiftQuery = $modelClass::query();
            foreach ($scopeConditions as $column => $value) {
                if ($value === null) {
                    $shiftQuery->whereNull($column);
                } else {
                    $shiftQuery->where($column, $value);
                }
            }
            $shiftQuery->where('order', '>=', $insertOrder)->increment('order');
        } else {
            // Target order is a gap - after insert, compact items after this order
            // to fill any gaps (e.g., [1,2,3,6,9] insert at 4 → [1,2,3,4,5,6])

            // Create new item at target order
            $newItem = $modelClass::create(array_merge($scopeConditions, $extraAttributes, ['order' => $insertOrder]));

            // Compact items after insertOrder
            $compactQuery = $modelClass::query();
            foreach ($scopeConditions as $column => $value) {
                if ($value === null) {
                    $compactQuery->whereNull($column);
                } else {
                    $compactQuery->where($column, $value);
                }
            }

            $itemsAfter = $compactQuery->where('order', '>', $insertOrder)->orderBy('order')->get();
            foreach ($itemsAfter as $index => $item) {
                $item->update(['order' => $insertOrder + 1 + $index]);
            }

            return $newItem;
        }

        // Create new item at target order
        return $modelClass::create(array_merge($scopeConditions, $extraAttributes, ['order' => $insertOrder]));
    }

    /**
     * Delete an item and compact remaining items sequentially to fill gaps.
     * After deletion, all items are reordered sequentially starting from 1.
     *
     * @param Model $model           The item to delete
     * @param array $scopeConditions Scope conditions, e.g. ['feature_page_id' => 5]
     * @return bool|null
     */
    protected function deleteAndShiftOrder(Model $model, array $scopeConditions): ?bool
    {
        $modelClass = get_class($model);
        $result = $model->delete();

        // After deletion, compact all remaining items sequentially (removes gaps)
        if ($result) {
            $this->compactOrderInScope($modelClass, $scopeConditions);
        }

        return $result;
    }

    /**
     * Compact orders in a scope to remove gaps (e.g., 1,2,4,5 → 1,2,3,4).
     * Reorders all items sequentially starting from 1.
     *
     * @param string $modelClass      Fully qualified model class name
     * @param array  $scopeConditions Scope conditions, e.g. ['feature_page_id' => 5]
     * @return void
     */
    protected function compactOrderInScope(string $modelClass, array $scopeConditions): void
    {
        $query = $modelClass::query();

        // Apply each scope condition
        foreach ($scopeConditions as $column => $value) {
            if ($value === null) {
                $query->whereNull($column);
            } else {
                $query->where($column, $value);
            }
        }

        // Get all items ordered by current order
        $items = $query->orderBy('order')->get();

        // Reorder sequentially starting from 1
        foreach ($items as $index => $item) {
            $item->update(['order' => $index + 1]);
        }
    }
}
