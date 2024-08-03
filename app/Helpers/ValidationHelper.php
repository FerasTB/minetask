<?php

namespace App\Helpers;

class ValidationHelper
{
    public static function validateNonNullKeys(array $data, array $requiredKeys)
    {
        $nullValues = [];

        foreach ($requiredKeys as $key) {
            if (!isset($data[$key]) || $data[$key] === null) {
                $nullValues[] = $key;
            }
        }

        if (!empty($nullValues)) {
            return [
                'success' => false,
                'message' => 'The following keys should not be null: ' . implode(', ', $nullValues)
            ];
        }

        return ['success' => true];
    }
}
